<?php
require_once __DIR__ . '/../Controller/config.php';
require_once __DIR__ . '/../Model/User.php';

$db        = Database::getInstance()->getConnection();
$userModel = new User($db);

$alertType     = null;   // 'success' or 'error'
$alertMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    $fullName        = trim($_POST['fullName']        ?? '');
    $email           = trim($_POST['email']           ?? '');
    $password        = $_POST['password']            ?? '';
    $confirmPassword = $_POST['confirmPassword']     ?? '';

    // Basic validation
    if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $alertType = 'error';
        $alertMessages[] = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertType = 'error';
        $alertMessages[] = 'Please enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $alertType = 'error';
        $alertMessages[] = 'Passwords do not match.';
    } else {

        // Use User model to check if email already exists
        if ($userModel->emailExists($email)) {
            $alertType = 'error';
            $alertMessages[] = 'User already exists with this email.';
        } else {
            // Create new user
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // choose sensible defaults for avatar fields
            $avatarSeed  = $fullName !== '' ? $fullName : $email;
            $avatarStyle = 'lorelei';

            // Use User model to insert
            $newUserId = $userModel->create($fullName, $email, $hash, $avatarSeed, $avatarStyle);

            if ($newUserId > 0) {
                // SUCCESS: user is now in the DB
                $alertType = 'success';
                $alertMessages[] = 'Registration successful! You can now log in.';
            } else {
                $alertType = 'error';
                $alertMessages[] = 'Error registering user. Please try again.';
            }
        }
    }
}

