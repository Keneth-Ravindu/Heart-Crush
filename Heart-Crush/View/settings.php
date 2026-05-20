<?php
require_once __DIR__ . '/../Controller/userSettingsHandler.php';

// Basic user data from session
$displayName = $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player');

$email = $_SESSION['email'] ?? '';

// Avatar from session with fallbacks
$avatarStyle = $_SESSION['user']['avatar_style'] ?? 'lorelei';
$avatarSeed  = $_SESSION['user']['avatar_seed']
    ?? ($displayName ?: $email ?: 'HeartCrushPlayer');

$avatarUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=30&radius=50";

// Options for dropdowns
$seedOptions = [
    'HeartCrushPlayer',
    'StarCrush',
    'LoveWizard',
    'PixelHeart',
    'LuckyCharm',
    'MoonFox',
    'CandyKnight'
];

$styleOptions = [
    'lorelei'     => 'Lorelei',
    'adventurer'  => 'Adventurer',
    'pixel-art'   => 'Pixel Art',
    'bottts'      => 'Bottts'
];

$avatarPreviewUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=120&radius=50";
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Heart Crush – Settings</title>

    <!-- Bootstrap & Icons -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Your app styles -->
    <link rel="stylesheet" href="../Assets/css/mainStyle.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/navbar.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/cursor.css">

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="../Assets/JS/bgaudio.js"></script>
</head>
<body>
    <div class="image-container settings-page">
        <div class="hearts">
            <span style="--i:1"></span><span style="--i:2"></span><span style="--i:3"></span>
            <span style="--i:4"></span><span style="--i:5"></span><span style="--i:6"></span>
            <span style="--i:7"></span><span style="--i:8"></span><span style="--i:9"></span>
            <span style="--i:10"></span><span style="--i:11"></span><span style="--i:12"></span>
            <span style="--i:13"></span><span style="--i:14"></span><span style="--i:15"></span>
        </div>

        <?php include __DIR__ . '/navbar.php'; ?>

        <main class="container settings-container">
            <section class="settings-shell">
                <!-- Profile header -->
                <header class="settings-profile-header d-flex align-items-center">
                    <div class="settings-profile-avatar">
                        <img
                            src="<?= htmlspecialchars($avatarPreviewUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Avatar"
                            class="settings-profile-avatar-img"
                        >
                    </div>
                    <div class="settings-profile-info">
                        <h2 class="settings-profile-name">
                            <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                        <p class="settings-profile-email">
                            <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <span class="settings-profile-badge">
                            <i class="bi bi-heart-fill"></i> HeartCrush Player
                        </span>
                    </div>
                </header>

                <!-- Messages -->
                <?php if (!empty($settingsSuccess)): ?>
                    <div class="alert alert-success settings-alert">
                        <?php foreach ($settingsSuccess as $msg): ?>
                            <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settingsErrors)): ?>
                    <div class="alert alert-danger settings-alert">
                        <?php foreach ($settingsErrors as $msg): ?>
                            <div><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Layout: side panel + tabs -->
                <div class="row g-0 settings-layout">
                    <!-- Sidebar -->
                    <aside class="col-md-3 settings-sidebar">
                        <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active"
                                    id="tab-profile-btn"
                                    data-bs-toggle="pill"
                                    data-bs-target="#tab-profile"
                                    type="button"
                                    role="tab"
                                    aria-controls="tab-profile"
                                    aria-selected="true">
                                <i class="bi bi-person"></i>
                                <span>Profile</span>
                            </button>
                            <button class="nav-link"
                                    id="tab-password-btn"
                                    data-bs-toggle="pill"
                                    data-bs-target="#tab-password"
                                    type="button"
                                    role="tab"
                                    aria-controls="tab-password"
                                    aria-selected="false">
                                <i class="bi bi-shield-lock"></i>
                                <span>Password</span>
                            </button>
                            <button class="nav-link"
                                    id="tab-avatar-btn"
                                    data-bs-toggle="pill"
                                    data-bs-target="#tab-avatar"
                                    type="button"
                                    role="tab"
                                    aria-controls="tab-avatar"
                                    aria-selected="false">
                                <i class="bi bi-emoji-smile"></i>
                                <span>Avatar</span>
                            </button>
                        </div>
                    </aside>

                    <!-- Panels -->
                    <div class="col-md-9 settings-panels">
                        <div class="tab-content" id="settings-tab-content">
                            <!-- Profile tab -->
                            <div class="tab-pane fade show active" id="tab-profile" role="tabpanel" aria-labelledby="tab-profile-btn">
                                <div class="settings-card">
                                    <h3 class="settings-section-title">Profile</h3>
                                    <p class="settings-section-subtitle">
                                        Update your display name and email address.
                                    </p>

                                    <form method="post" autocomplete="off" class="settings-form">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" name="fullName" class="form-control"
                                                value="<?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control"
                                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                        <button type="submit" name="update_profile"
                                                class="btn btn-primary w-100 settings-btn">
                                            Save Profile
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Password tab -->
                            <div class="tab-pane fade" id="tab-password" role="tabpanel" aria-labelledby="tab-password-btn">
                                <div class="settings-card">
                                    <h3 class="settings-section-title">Change Password</h3>
                                    <p class="settings-section-subtitle">
                                        Choose a strong password to secure your account.
                                    </p>

                                    <form method="post" autocomplete="off" class="settings-form">
                                        <div class="mb-3">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" name="current_password" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" name="confirm_password" class="form-control">
                                        </div>
                                        <button type="submit" name="change_password"
                                                class="btn btn-warning w-100 settings-btn">
                                            Update Password
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Avatar tab -->
                            <div class="tab-pane fade" id="tab-avatar" role="tabpanel" aria-labelledby="tab-avatar-btn">
                                <div class="settings-card">
                                    <h3 class="settings-section-title">Avatar</h3>
                                    <p class="settings-section-subtitle">
                                        Pick your DiceBear avatar style and preset for HeartCrush.
                                    </p>

                                    <div class="row g-4 align-items-center settings-avatar-row">
                                        <!-- Preview -->
                                        <div class="col-lg-4">
                                            <div class="avatar-preview-card text-center">
                                                <p class="mb-2">Preview</p>
                                                <img
                                                    id="avatarPreview"
                                                    src="<?= htmlspecialchars($avatarPreviewUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                    alt="Avatar preview"
                                                    class="avatar-preview-img"
                                                    data-base-url="https://api.dicebear.com/7.x/"
                                                />
                                                <p class="avatar-preview-label mt-3">
                                                    <span id="avatarPreviewSeedLabel">
                                                        <?= htmlspecialchars($avatarSeed, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                    ·
                                                    <span id="avatarPreviewStyleLabel">
                                                        <?= htmlspecialchars($styleOptions[$avatarStyle] ?? $avatarStyle, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Controls -->
                                        <div class="col-lg-8">
                                            <form method="post" autocomplete="off" class="settings-form">
                                                <div class="mb-3">
                                                    <label class="form-label">Avatar Preset</label>
                                                    <select name="avatar_seed" id="avatarSeed" class="form-select">
                                                        <?php foreach ($seedOptions as $seed): ?>
                                                            <option value="<?= htmlspecialchars($seed, ENT_QUOTES, 'UTF-8') ?>"
                                                                <?= $avatarSeed === $seed ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($seed, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="text-muted">
                                                        Each preset generates a unique avatar for you.
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Avatar Style</label>
                                                    <select name="avatar_style" id="avatarStyle" class="form-select">
                                                        <?php foreach ($styleOptions as $styleValue => $styleLabel): ?>
                                                            <option value="<?= htmlspecialchars($styleValue, ENT_QUOTES, 'UTF-8') ?>"
                                                                <?= $avatarStyle === $styleValue ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($styleLabel, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="text-muted">
                                                        Change the art style of your avatar.
                                                    </small>
                                                </div>

                                                <button type="submit" name="update_avatar"
                                                        class="btn btn-info w-100 settings-btn">
                                                    Save Avatar
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- End avatar tab -->
                        </div>
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

        <audio id="music" loop>
            <source type="audio/mp3" src="../Assets/Audio/backgroundMusic.mp3">
        </audio>
    </div>

    <!-- Avatar preview live update -->
    <script src="../Assets/JS/avatar.js" defer></script>
    <div class="hc-cursor"></div>
    <div class="hc-cursor-outline"></div>
    <script src="../Assets/js/cursor.js" defer></script>
</body>
</html>
