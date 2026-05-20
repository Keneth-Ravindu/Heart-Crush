<?php
require_once __DIR__ . '/../Controller/registerHandler.php';
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Heart Crush — Register</title>

    <!-- Modern Bootstrap & Icons -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- App styles -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../Assets/css/style.css" type="text/css">
    <link rel="stylesheet" href="../Assets/css/cursor.css">

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="../Assets/JS/bgaudio.js"></script>
    <script defer src="../Assets/JS/main.js"></script>
</head>

<body>
    <div class="image-container">
        <!-- Glowing Heart Particles -->
        <div class="hearts">
            <span style="--i:1"></span><span style="--i:2"></span><span style="--i:3"></span><span style="--i:4"></span><span style="--i:5"></span>
            <span style="--i:6"></span><span style="--i:7"></span><span style="--i:8"></span><span style="--i:9"></span><span style="--i:10"></span>
            <span style="--i:11"></span><span style="--i:12"></span><span style="--i:13"></span><span style="--i:14"></span><span style="--i:15"></span>
        </div>

        <!-- Navbar -->
        <div class="navbar">
            <a href="index.php" class="navbar-logo-link">
                <h1 class="navbar-logo">
                    <span class="navbar-logo-text">Heart</span>
                    <span class="navbar-logo-heart"><i class="bi bi-suit-heart-fill"></i></span>
                    <span class="navbar-logo-text">Crush</span>
                </h1>
            </a>
        </div>

        <!-- Registration Form -->
        <div class="container">
            <div class="form-wrapper">
                <h1 class="text-center">Create Account</h1>
                <h3 class="text-center">WELCOME</h3>

                <form method="post" class="form-align" autocomplete="off">
                    <!-- Display Name -->
                    <div class="form-group">
                        <label for="fullName"><i class="bi bi-person-fill"></i></label>
                        <input
                            type="text"
                            class="input-field"
                            id="fullName"
                            name="fullName"
                            placeholder="Display name"
                            required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email"><i class="bi bi-envelope-fill"></i></label>
                        <input
                            type="email"
                            class="input-field"
                            id="email"
                            name="email"
                            placeholder="Email address"
                            required>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password"><i class="bi bi-lock-fill"></i></label>
                        <input
                            type="password"
                            class="input-field"
                            id="password"
                            name="password"
                            placeholder="Password"
                            minlength="4"
                            required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirmPassword"><i class="bi bi-shield-lock-fill"></i></label>
                        <input
                            type="password"
                            class="input-field"
                            id="confirmPassword"
                            name="confirmPassword"
                            placeholder="Confirm password"
                            minlength="4"
                            required>
                    </div>

                    <div class="text-center" style="margin-top:8px;">
                        <button class="loginbtn" name="register">Register</button>
                    </div>
                </form>

                <div class="text-center">
                    <h6 class="regtxt">Already have an account?</h6>
                    <a href="login.php" id="reglink">
                        <button class="regbtn" type="button">Back to Login</button>
                    </a>
                </div>
            </div>
        </div>

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

    <?php
    // Decide if we should redirect to login after the alert (success OR "user exists")
    $userExistsMessage = 'User already exists with this email.';
    $shouldRedirectToLogin =
        (!empty($alertType) && $alertType === 'success') ||
        (!empty($alertMessages)
            && count($alertMessages) === 1
            && $alertMessages[0] === $userExistsMessage);
    ?>

    <?php if (!empty($alertType) && !empty($alertMessages)): ?>
        <script>
            // Expose data for authAlerts.js
            window.hcAlertType = <?php echo json_encode($alertType); ?>;
            window.hcAlertHtml = <?php
                $safe = [];
                foreach ($alertMessages as $m) {
                    $safe[] = htmlspecialchars($m, ENT_QUOTES, 'UTF-8');
                }
                echo json_encode(implode('<br>', $safe));
            ?>;
            window.hcIsRegister = true;
            window.hcRedirectToLogin = <?php echo $shouldRedirectToLogin ? 'true' : 'false'; ?>;
        </script>
    <?php endif; ?>

    <script src="../Assets/JS/authAlerts.js"></script>
    <div class="hc-cursor"></div>
    <div class="hc-cursor-outline"></div>
    <script src="../Assets/js/cursor.js" defer></script>

</body>

</html>
