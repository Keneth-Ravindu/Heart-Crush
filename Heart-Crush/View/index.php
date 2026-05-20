<?php
require_once __DIR__ . '/../Controller/config.php';

// Unified login check (same as profile, scores, game, settings)
$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    Database::redirect('login.php');
}

// Basic user info from session
$displayName = $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player');
$email = $_SESSION['email'] ?? $displayName;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Heart Crush — Home</title>

    <!-- Bootstrap & Icons (same stack as login/register) -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App styles & audio JS -->
    <link rel="stylesheet" href="../Assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/navbar.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/cursor.css">

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="../Assets/JS/bgaudio.js"></script>
    <script defer src="../Assets/JS/main.js"></script>
</head>
<body>
    <div class="image-container index-v2">
        <!-- Glowing Heart Particles -->
        <div class="hearts">
            <?php for ($i = 1; $i <= 15; $i++): ?>
                <span style="--i:<?= $i ?>"></span>
            <?php endfor; ?>
        </div>

        <?php include __DIR__ . '/navbar.php'; ?>

        <!-- Main content block -->
        <main class="container">
            <section class="form-wrapper" style="text-align:center;">
                <h1 class="text-center" style="margin-bottom:6px;">Let’s Play And Win!</h1>
                <p class="text-center" style="color:#ddd; margin-bottom:20px;">
                    Welcome, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?> 💞
                </p>

                <a href="game.php" style="text-decoration:none; display:inline-block; width:100%; max-width:260px;">
                    <button class="loginbtn" style="width:100%;">Start Playing</button>
                </a>
            </section>
        </main>

        <!-- Floating Bottom-Right Sound Button + Hover Volume -->
        <div class="links audio-controls">
            <input
                id="musicVolume"
                type="range"
                min="0"
                max="1"
                step="0.05"
                value="1"
                aria-label="Music volume"
            >
            <button id="mutebtn" aria-label="Toggle sound">
                <i class="bi bi-volume-up-fill"></i>
            </button>
        </div>
    </div>

    <audio id="music" loop>
        <source type="audio/mp3" src="../Assets/Audio/backgroundMusic.mp3">
    </audio>

    <div class="hc-cursor"></div>
    <div class="hc-cursor-outline"></div>
    <script src="../Assets/js/cursor.js" defer></script>
</body>
</html>
