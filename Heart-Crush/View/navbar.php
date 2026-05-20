<?php

$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);

$displayName = $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player');

$email = $_SESSION['email'] ?? $displayName;

$avatarStyle = $_SESSION['user']['avatar_style'] ?? 'lorelei';
$avatarSeed  = $_SESSION['user']['avatar_seed']
    ?? ($displayName ?: $email ?: 'HeartCrushPlayer');

$avatarUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=64&radius=50";

// Detect current page for active tab highlight
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="hc-nav">

    <!-- Left: player pill -->
    <div class="hc-nav-left">
        <?php if ($loggedIn): ?>
        <div class="hc-nav-user">
            <div class="hc-nav-avatar-ring">
                <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="hc-nav-avatar-main" alt="Avatar">
            </div>

            <div class="hc-nav-user-text">
                <span class="hc-nav-user-label">Player</span>
                <span class="hc-nav-username">
                    <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Center: logo -->
    <div class="hc-nav-center">
        <a href="index.php" class="hc-nav-logo-link">
            <h1 class="hc-nav-logo">
                <span class="hc-nav-logo-text">Heart</span>
                <span class="hc-nav-logo-heart"><i class="bi bi-suit-heart-fill"></i></span>
                <span class="hc-nav-logo-text">Crush</span>
            </h1>
        </a>
    </div>

    <!-- Right: navigation icons -->
    <div class="hc-nav-right">

        <a href="index.php"
            class="hc-nav-item <?= $currentPage === 'index.php' ? 'hc-nav-item--active' : '' ?>"
            title="Home">
            <span class="hc-nav-item-inner"><i class="bi bi-house-door"></i></span>
        </a>

        <a href="game.php"
            class="hc-nav-item <?= $currentPage === 'game.php' ? 'hc-nav-item--active' : '' ?>"
            title="Play">
            <span class="hc-nav-item-inner"><i class="bi bi-controller"></i></span>
        </a>

        <a href="scores.php"
            class="hc-nav-item <?= $currentPage === 'scores.php' ? 'hc-nav-item--active' : '' ?>"
            title="Scores">
            <span class="hc-nav-item-inner"><i class="bi bi-trophy"></i></span>
        </a>

        <a href="settings.php"
            class="hc-nav-item <?= $currentPage === 'settings.php' ? 'hc-nav-item--active' : '' ?>"
            title="Settings">
            <span class="hc-nav-item-inner"><i class="bi bi-gear"></i></span>
        </a>

        <a href="profile.php"
            class="hc-nav-item <?= $currentPage === 'profile.php' ? 'hc-nav-item--active' : '' ?>"
            title="Profile">
            <span class="hc-nav-item-inner hc-nav-item-avatar">
                <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="hc-nav-avatar-small" alt="Avatar">
            </span>
        </a>

        <?php if ($loggedIn): ?>
        <a href="../Controller/logout.php"
            class="hc-nav-item"
            title="Logout">
            <span class="hc-nav-item-inner"><i class="bi bi-box-arrow-right"></i></span>
        </a>
        <?php endif; ?>

    </div>

</nav>
