<?php
require_once __DIR__ . '/../Controller/config.php';
require_once __DIR__ . '/../Model/User.php';

$db        = Database::getInstance()->getConnection(); // mysqli
$userModel = new User($db); // or new User()

$alertType     = null;   // 'success' or 'error'
$alertMessages = [];

// Run whenever the form is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $alertType = 'error';
        $alertMessages[] = 'Please enter both email and password.';
    } else {

        // Use the User model instead of manual SELECT
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Success: build session

            // Make sure session is active (config.php should already do this)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Prevent session fixation
            session_regenerate_id(true);

            $displayName = $user['fullName'] ?: $user['email'];

            // Fallbacks if avatar not set
            $avatarStyle = $user['avatar_style'] ?: 'lorelei';
            $avatarSeed  = $user['avatar_seed']
                ?: ($displayName ?: $user['email'] ?: 'HeartCrushPlayer');

            $_SESSION['user'] = [
                'id'           => (int) $user['id'],
                'email'        => $user['email'],
                'display_name' => $displayName,
                'avatar_style' => $avatarStyle,
                'avatar_seed'  => $avatarSeed,
                'logged_in'    => true,
            ];

            // Legacy keys used elsewhere in your app
            $_SESSION['email']     = $user['email'];
            $_SESSION['user_name'] = $displayName;
            $_SESSION['loggedIn']  = true;

            // "Remember Me" persistent login
            $rememberRequested = !empty($_POST['remember_me']);

            if ($rememberRequested) {
                $userId         = (int)$user['id'];
                $selector       = bin2hex(random_bytes(9));   // 18 chars
                $validator      = bin2hex(random_bytes(32));  // 64 chars
                $hashedValidator = hash('sha256', $validator);

                // 30-day expiry
                $expiresDateTime = new DateTime('+30 days');
                $expiresAt       = $expiresDateTime->format('Y-m-d H:i:s');
                $cookieExpireTs  = $expiresDateTime->getTimestamp();

                // clear old tokens for this user
                if ($del = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?")) {
                    $del->bind_param('i', $userId);
                    $del->execute();
                    $del->close();
                }

                // Insert new remember token
                if ($ins = $db->prepare("
                    INSERT INTO remember_tokens (user_id, selector, hashed_validator, expires_at)
                    VALUES (?, ?, ?, ?)
                ")) {
                    $ins->bind_param('isss', $userId, $selector, $hashedValidator, $expiresAt);
                    $ins->execute();
                    $ins->close();
                }

                // Cookie value: selector:validator
                $cookieValue = $selector . ':' . $validator;

                // Secure cookie flags
                $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

                // Use array-style options
                $cookieOptions = [
                    'expires'  => $cookieExpireTs,
                    'path'     => '/',
                    'domain'   => '', // set your domain if needed
                    'secure'   => $secure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ];

                setcookie('hc_remember', $cookieValue, $cookieOptions);

            } else {
                // If "Remember me" not checked, ensure cookie is cleared
                if (isset($_COOKIE['hc_remember'])) {
                    // Clear cookie (basic path-wide clear)
                    setcookie('hc_remember', '', time() - 3600, '/');
                }
            }

            // Redirect to loading page after successful login
            Database::redirect('loading.php');
            exit;

        } else {
            // Wrong email or password
            $alertType = 'error';
            $alertMessages[] = 'Incorrect email or password.';
        }
    }
}

