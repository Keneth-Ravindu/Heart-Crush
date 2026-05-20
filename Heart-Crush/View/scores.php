<?php
require_once __DIR__ . '/../Controller/config.php';
require_once __DIR__ . '/../Model/Score.php';

// Use the Database singleton
$db         = Database::getInstance()->getConnection();
$scoreModel = new Score($db);

// Login check
$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    Database::redirect('login.php');
}

// Basic user/session info
$displayName = $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player');
$email = $_SESSION['email'] ?? $displayName;

// Avatar from session (falls back to name/email)
$avatarStyle = $_SESSION['user']['avatar_style'] ?? 'lorelei';
$avatarSeed  = $_SESSION['user']['avatar_seed']
    ?? ($displayName ?: $email ?: 'HeartCrushPlayer');

$avatarUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=64&radius=50";

// Current user id (for optional highlighting)
$currentUserId = $_SESSION['user']['id'] ?? null;

// Fetch top 5 scores using the Score model
// Expecting columns: score_id, player_id, score, datentime, fullName, email, avatar_style, avatar_seed
$scores = $scoreModel->getTopScores(5);
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Heart Crush — Leaderboard</title>

    <!-- Bootstrap & Icons -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App styles & JS -->
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

        <main class="container">
            <section class="form-wrapper" style="text-align:center;">
                <h1 class="text-center" style="margin-bottom:20px; color:#ddd;">
                    Leaderboard💞
                </h1>

                <table class="table table-dark table-striped table-hover align-middle fancy-leaderboard">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Player</th>
                            <th scope="col">Score</th>
                            <th scope="col">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($scores)): ?>
                        <?php $rank = 1; ?>
                        <?php foreach ($scores as $row): ?>
                            <?php
                                $position = $rank;

                                // Medal for top 3
                                $medal = '';
                                if     ($position === 1) $medal = '🥇';
                                elseif ($position === 2) $medal = '🥈';
                                elseif ($position === 3) $medal = '🥉';

                                // Player name/email
                                $playerName  = $row['fullName'] ?: ($row['email'] ?? 'Unknown Player');
                                $playerEmail = $row['email'] ?? '';

                                // Player avatar (row-specific, from users table)
                                $pStyle = $row['avatar_style'] ?? $avatarStyle;
                                $pSeed  = $row['avatar_seed']
                                    ?? ($playerName ?: $playerEmail ?: 'HeartCrushPlayer');

                                $playerAvatarUrl = "https://api.dicebear.com/7.x/{$pStyle}/svg?seed="
                                    . urlencode($pSeed) . "&size=48&radius=50";

                                $isCurrent = $currentUserId && ((int)$row['player_id'] === (int)$currentUserId);
                                $rowClass  = $isCurrent ? 'table-success' : '';
                            ?>
                            <tr class="leaderboard-row rank-<?= $position ?> <?= $rowClass ?>">
                                <!-- Rank / Medal -->
                                <th scope="row">
                                    <span class="rank-badge">
                                        <?= $medal !== '' ? $medal : $position ?>
                                    </span>
                                </th>

                                <!-- Player (avatar + name) -->
                                <td class="player-cell">
                                    <img src="<?= htmlspecialchars($playerAvatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Avatar"
                                        class="player-avatar">
                                    <div class="player-meta">
                                        <span class="player-name">
                                            <?= htmlspecialchars($playerName, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <span class="player-email">
                                            <?= htmlspecialchars($playerEmail, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Score -->
                                <td class="score-cell"><?= (int)$row['score']; ?></td>

                                <!-- Time -->
                                <td class="time-cell">
                                    <?= htmlspecialchars($row['datentime'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                            </tr>
                            <?php $rank++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No scores yet. Be the first to play! 💗
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <div class="back-to-game-wrapper">
                    <a href="game.php" class="back-to-game-link">
                        <button class="loginbtn back-to-game-btn">Back to Game</button>
                    </a>
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
