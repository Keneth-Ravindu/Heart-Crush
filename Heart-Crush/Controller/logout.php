<?php
require_once __DIR__ . '/config.php';

// DB connection from config
$db = Database::getInstance()->getConnection();


// 1) Clear remember-me cookie + delete DB token
if (!empty($_COOKIE['hc_remember'])) {

    $cookie = $_COOKIE['hc_remember'];

    if (strpos($cookie, ':') !== false) {

        list($selector, $validator) = explode(':', $cookie, 2);

        if (!empty($selector)) {
            if ($stmt = $db->prepare("DELETE FROM remember_tokens WHERE selector = ?")) {
                $stmt->bind_param('s', $selector);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Wipe cookie
    setcookie('hc_remember', '', time() - 3600, '/', '', false, true);
}

// 2) Fully destroy session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Clear session array
$_SESSION = [];

// Delete PHP session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy session storage
session_destroy();

// 3) Redirect to login page
Database::redirect('../View/login.php');
exit;
