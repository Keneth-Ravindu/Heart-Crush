<?php
session_start();

class Database
{
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "heartCrush";

    private static $instance = null;
    private $conn;

    // Private constructor to prevent direct creation
    private function __construct()
    {
        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname
        );

        if ($this->conn->connect_error) {
            die("Connection Failed: " . $this->conn->connect_error);
        }
    }

    // Get the singleton instance
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get the mysqli connection
    public function getConnection()
    {
        return $this->conn;
    }

    // Centralized redirect helper
    public static function redirect($url)
    {
        echo "<script>window.location.href = '$url';</script>";
        exit;
    }
}


function hc_auto_login_from_cookie(mysqli $db): void
{
    // Already logged in? Nothing to do.
    if (isset($_SESSION['user']) && !empty($_SESSION['user']['logged_in'])) {
        return;
    }

    // No remember-me cookie present
    if (empty($_COOKIE['hc_remember'])) {
        return;
    }

    $cookie = $_COOKIE['hc_remember'];
    if (strpos($cookie, ':') === false) {
        return; // malformed
    }

    list($selector, $validator) = explode(':', $cookie, 2);
    if (!$selector || !$validator) {
        return;
    }

    // Look up remember token
    $sql = "
        SELECT rt.user_id,
            rt.hashed_validator,
            rt.expires_at,
            u.fullName,
            u.email,
            u.avatar_style,
            u.avatar_seed
        FROM remember_tokens rt
        JOIN users u ON u.id = rt.user_id
        WHERE rt.selector = ?
        LIMIT 1
    ";

    if (!$stmt = $db->prepare($sql)) {
        return;
    }

    $stmt->bind_param('s', $selector);

    if (!$stmt->execute()) {
        $stmt->close();
        return;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return; // no matching token
    }

    // Check expiry
    $expiresAtTs = strtotime($row['expires_at']);
    if ($expiresAtTs !== false && $expiresAtTs < time()) {
        // Expired → delete token & clear cookie
        if ($del = $db->prepare("DELETE FROM remember_tokens WHERE selector = ?")) {
            $del->bind_param('s', $selector);
            $del->execute();
            $del->close();
        }
        setcookie('hc_remember', '', time() - 3600, '/');
        return;
    }

    // Validate validator using constant-time comparison
    $calcHash = hash('sha256', $validator);
    if (!hash_equals($row['hashed_validator'], $calcHash)) {
        // Possible tampering → delete token & cookie
        if ($del = $db->prepare("DELETE FROM remember_tokens WHERE selector = ?")) {
            $del->bind_param('s', $selector);
            $del->execute();
            $del->close();
        }
        setcookie('hc_remember', '', time() - 3600, '/');
        return;
    }

    // Token is valid → log user in
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    $displayName = $row['fullName'] ?: $row['email'];
    $avatarStyle = $row['avatar_style'] ?: 'lorelei';
    $avatarSeed  = $row['avatar_seed']
        ?: ($displayName ?: $row['email'] ?: 'HeartCrushPlayer');

    $_SESSION['user'] = [
        'id'           => (int)$row['user_id'],
        'email'        => $row['email'],
        'display_name' => $displayName,
        'avatar_style' => $avatarStyle,
        'avatar_seed'  => $avatarSeed,
        'logged_in'    => true,
    ];

    // Legacy keys used elsewhere in the game
    $_SESSION['email']     = $row['email'];
    $_SESSION['user_name'] = $displayName;
    $_SESSION['loggedIn']  = true;

    // Rotate token (one-time use) for extra safety
    $newSelector  = bin2hex(random_bytes(9));   // 18 chars
    $newValidator = bin2hex(random_bytes(32));  // 64 chars
    $newHash      = hash('sha256', $newValidator);

    $expiresDateTime = new DateTime('+30 days');
    $newExpiresAt    = $expiresDateTime->format('Y-m-d H:i:s');
    $cookieExpireTs  = $expiresDateTime->getTimestamp();

    // Remove old token
    if ($del = $db->prepare("DELETE FROM remember_tokens WHERE selector = ?")) {
        $del->bind_param('s', $selector);
        $del->execute();
        $del->close();
    }

    // Insert new token
    $userId = (int)$row['user_id'];
    if ($ins = $db->prepare("
        INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at)
        VALUES (?, ?, ?, ?)
    ")) {
        $ins->bind_param('isss', $userId, $newSelector, $newHash, $newExpiresAt);
        $ins->execute();
        $ins->close();
    }

    // Set new cookie
    $newCookieVal = $newSelector . ':' . $newValidator;

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    $cookieOptions = [
        'expires'  => $cookieExpireTs,
        'path'     => '/',
        'domain'   => '', // set domain here if needed
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    setcookie('hc_remember', $newCookieVal, $cookieOptions);
}

//
// Initialize DB and run auto-login hook

// Get shared mysqli connection
$db = Database::getInstance()->getConnection();

// Try to auto-login from remember cookie (if no active session user yet)
hc_auto_login_from_cookie($db);
