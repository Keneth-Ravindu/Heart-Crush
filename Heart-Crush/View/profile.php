<?php
require_once __DIR__ . '/../Controller/config.php';
require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../Model/Score.php';

// Use the Database singleton
$db         = Database::getInstance()->getConnection();
$userModel  = new User($db);
$scoreModel = new Score($db);

// Login check
$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    Database::redirect('login.php');
}

// Get current user id from session
$currentUserId = $_SESSION['user']['id'] ?? null;

// If no id, try to recover from email
if (!$currentUserId) {
    $email = $_SESSION['email'] ?? null;
    if ($email) {
        $userRow = $userModel->findByEmail($email);
        if ($userRow) {
            $currentUserId = (int)$userRow['id'];

            if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
                $_SESSION['user'] = [];
            }
            $_SESSION['user']['id'] = $currentUserId;
        }
    }
}

if (!$currentUserId) {
    // Cannot identify user → go to login
    Database::redirect('login.php');
    exit;
}

// Load full user row from DB
$userRow = $userModel->findById($currentUserId);
if (!$userRow) {
    // User not found in DB – force logout/login cycle
    Database::redirect('login.php');
    exit;
}

// Basic user info (prefer DB, fall back to session)
$displayName = $userRow['fullName'] ?: (
    $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player')
);

$email = $userRow['email'] ?? (
    $_SESSION['email'] ?? $displayName
);

// Avatar from DB (fallbacks to session or derived)
$avatarStyle = $userRow['avatar_style']
    ?? ($_SESSION['user']['avatar_style'] ?? 'lorelei');

$avatarSeed  = $userRow['avatar_seed']
    ?? ($_SESSION['user']['avatar_seed']
        ?? ($displayName ?: $email ?: 'HeartCrushPlayer'));

$avatarUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=164&radius=50";

// Pull some basic stats from scores table via Score model
$stats = $scoreModel->getStatsForUser($currentUserId);

$gamesPlayed = (int)($stats['gamesPlayed'] ?? 0);
$bestScore   = (int)($stats['bestScore']   ?? 0);
$avgScore    = (int)round((float)($stats['avgScore'] ?? 0.0));
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Heart Crush — Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Icons -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Global styles -->
    <link rel="stylesheet" href="../Assets/css/navbar.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/mainStyle.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/cursor.css">

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="../Assets/JS/bgaudio.js"></script>
</head>
<body>
    <div class="image-container index-v2 profile-shell">
        <!-- Glowing Heart Particles -->
        <div class="hearts">
            <?php for ($i = 1; $i <= 15; $i++): ?>
                <span style="--i:<?= $i ?>"></span>
            <?php endfor; ?>
        </div>

        <!-- Navbar -->
        <?php include __DIR__ . '/navbar.php'; ?>

        <!-- Main Profile Content -->
        <main class="profile-main">
            <section class="profile-card">
                <div class="profile-left">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar-inner">
                            <img
                                src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                                alt="Avatar of <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>"
                            >
                        </div>
                    </div>
                    <div class="profile-badge">
                        <i class="bi bi-heart-fill"></i> HeartCrush Player
                    </div>
                    <div class="profile-hearts">
                        <span>❤️</span><span>💖</span><span>💘</span><span>💝</span>
                    </div>
                </div>

                <div class="profile-right">
                    <div>
                        <div class="profile-name">
                            <span><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="profile-email">
                            <i class="bi bi-envelope"></i>
                            <span><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>

                        <div class="profile-meta">
                            <span class="meta-pill"><i class="bi bi-controller"></i> Puzzle Lover</span>
                            <span class="meta-pill"><i class="bi bi-lightning-charge"></i> Fast Thinker</span>
                            <span class="meta-pill"><i class="bi bi-stars"></i> Leaderboard Ready</span>
                        </div>
                    </div>

                    <div class="profile-stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Games Played</div>
                            <div class="stat-value"><?= $gamesPlayed ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Best Score</div>
                            <div class="stat-value"><?= $bestScore ?></div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Average Score</div>
                            <div class="stat-value"><?= $avgScore ?></div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="game.php" class="profile-btn">
                            <i class="bi bi-play-fill"></i>
                            <span>Play Now</span>
                        </a>
                        <a href="scores.php" class="profile-btn alt">
                            <i class="bi bi-trophy"></i>
                            <span>View Leaderboard</span>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- Floating Bottom-Right Sound Button + Hover Volume -->
        <div class="links audio-controls">
            <!-- Slider -->
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
