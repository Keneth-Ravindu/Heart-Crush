<?php
require_once __DIR__ . '/../Controller/config.php';

// Only allow access if logged in
$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    Database::redirect('login.php');
    exit;
}

// Build virtual identity
$displayName = $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player');

$email = $_SESSION['email'] ?? $displayName;

$avatarStyle = $_SESSION['user']['avatar_style'] ?? 'lorelei';
$avatarSeed  = $_SESSION['user']['avatar_seed']
    ?? ($displayName ?: $email ?: 'HeartCrushPlayer');

// Slightly larger avatar for the loading card
$avatarUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=110&radius=50";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loading your HeartCrush session...</title>

    <link rel="stylesheet" href="../Assets/css/style.css?v=<?= time(); ?>">
</head>
<body class="hc-loading-body">
    <div class="hc-loading-backdrop">
        <!-- Starfield layer -->
        <div class="hc-loading-stars"></div>

        <!-- Soft sakura petal shadows-->
        <div class="hc-loading-petal-shadow hc-loading-petal-shadow--1"></div>
        <div class="hc-loading-petal-shadow hc-loading-petal-shadow--2"></div>

        <!-- Optional legacy orbits -->
        <div class="hc-loading-orbit"></div>
        <div class="hc-loading-orbit hc-loading-orbit--2"></div>

        <!-- Manga panel card with hanging lantern -->
        <div class="hc-loading-card">
            <!-- Hanging lantern anchored to the top of the card -->
            <div class="hc-loading-lantern-wrap">
                <div class="hc-loading-lantern-string"></div>
                <div class="hc-loading-lantern">
                    <div class="hc-loading-lantern-glow"></div>
                    <div class="hc-loading-lantern-inner"></div>
                </div>
            </div>

            <div class="hc-loading-card-inner">
                <div class="hc-loading-pill">
                    HEARTCRUSH • LOADING
                </div>

                <div class="hc-loading-avatar-center">
                    <div class="hc-loading-avatar-wrap">
                        <div class="hc-loading-avatar-glow"></div>
                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Avatar"
                            class="hc-loading-avatar">
                    </div>
                </div>

                <div class="hc-loading-text-block">
                    <div class="hc-loading-greeting">
                        WELCOME BACK,
                    </div>
                    <div class="hc-loading-name">
                        <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?> 💘
                    </div>
                    <div class="hc-loading-subtitle">
                        We’re setting up your next puzzle and shuffling the hearts…
                    </div>
                </div>

                <div class="hc-loading-progress-block">
                    <div class="hc-loading-progress-label">
                        Preparing your puzzle room
                    </div>
                    <div class="hc-loading-progress-bar">
                        <div class="hc-loading-progress-bar-fill"></div>
                    </div>
                    <div class="hc-loading-progress-hint">
                        Take a moment. Love (and logic) is on the way.
                    </div>
                </div>

                <div class="hc-loading-fun-fact-block">
                    <div class="hc-loading-fun-fact-title">
                        Did you know?
                    </div>
                    <div id="hcFunFact" class="hc-loading-fun-fact-text">
                        Fetching a fun fact for you...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/js/loading.js" defer></script>
</body>
</html>
