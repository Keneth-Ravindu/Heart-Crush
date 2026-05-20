<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Model/User.php';

// Ensure user is logged in
$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    Database::redirect('login.php');
}

$db        = Database::getInstance()->getConnection();
$userModel = new User($db);

// We’ll collect messages and show them in settings.php
$settingsSuccess = [];
$settingsErrors  = [];

// Get current user id + email/name from session
$userId       = $_SESSION['user']['id'] ?? null;
$currentEmail = $_SESSION['email'] ?? null;
$currentName  = $_SESSION['user_name'] ?? ($_SESSION['fullName'] ?? null);

if (!$userId || !$currentEmail) {
    $settingsErrors[] = 'Could not determine current user.';
    return;
}

// 1) Update name + email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    $newName  = trim($_POST['fullName'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    if ($newName === '' || $newEmail === '') {
        $settingsErrors[] = 'Name and email cannot be empty.';
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $settingsErrors[] = 'Please enter a valid email address.';
    } else {
        // If email changed, makes sure it’s not taken by someone else
        if ($newEmail !== $currentEmail && $userModel->emailExists($newEmail)) {
            $settingsErrors[] = 'That email is already in use.';
        }

        if (empty($settingsErrors)) {
            // User model to update name + email
            $ok = $userModel->updateNameAndEmail($userId, $newName, $newEmail);

            if ($ok) {
                $settingsSuccess[] = 'Profile updated successfully.';

                // Update session
                $_SESSION['email']     = $newEmail;
                $_SESSION['fullName']  = $newName;
                $_SESSION['user_name'] = $newName ?: $newEmail;

                if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
                    $_SESSION['user'] = [];
                }
                $_SESSION['user']['display_name'] = $_SESSION['user_name'];

                // Update currentEmail/currName for later use in this request
                $currentEmail = $newEmail;
                $currentName  = $_SESSION['user_name'];
            } else {
                $settingsErrors[] = 'Error updating profile.';
            }
        }
    }
}

// 2) Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $settingsErrors[] = 'Please fill in all password fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $settingsErrors[] = 'New passwords do not match.';
    } else {
        // Get current hash using User model
        $userRow = $userModel->findById((int)$userId);

        if (!$userRow) {
            $settingsErrors[] = 'User not found.';
        } else {
            $hash = $userRow['password'];

            if (!password_verify($currentPassword, $hash)) {
                $settingsErrors[] = 'Current password is incorrect.';
            } else {
                // User model to update password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $ok      = $userModel->updatePassword($userId, $newHash);

                if ($ok) {
                    $settingsSuccess[] = 'Password updated successfully.';
                } else {
                    $settingsErrors[] = 'Error updating password.';
                }
            }
        }
    }
}


// 3) Update avatar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {

    $avatarStyle = $_POST['avatar_style'] ?? 'lorelei';
    $avatarSeed  = trim($_POST['avatar_seed'] ?? '');

    if ($avatarSeed === '') {
        $avatarSeed = $currentName ?: $currentEmail ?: 'HeartCrushPlayer';
    }

    // User model to update avatar
    $ok = $userModel->updateAvatar($userId, $avatarStyle, $avatarSeed);

    if ($ok) {
        $settingsSuccess[] = 'Avatar updated successfully.';

        // Update session
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            $_SESSION['user'] = [];
        }
        $_SESSION['user']['avatar_style'] = $avatarStyle;
        $_SESSION['user']['avatar_seed']  = $avatarSeed;
    } else {
        $settingsErrors[] = 'Error updating avatar.';
    }
}
