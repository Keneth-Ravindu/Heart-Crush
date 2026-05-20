<?php
require_once __DIR__ . '/../Controller/config.php';

$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    Database::redirect('login.php');
}

// Safe session access
$displayName = $_SESSION['user']['display_name']
    ?? ($_SESSION['user_name'] ?? 'Player');
$email = $_SESSION['email'] ?? $displayName;

// Avatar from session (falls back to name/email)
$avatarStyle = $_SESSION['user']['avatar_style'] ?? 'lorelei';
$avatarSeed  = $_SESSION['user']['avatar_seed']
    ?? ($displayName ?: $email ?: 'HeartCrushPlayer');

$avatarUrl = "https://api.dicebear.com/7.x/{$avatarStyle}/svg?seed="
    . urlencode($avatarSeed) . "&size=64&radius=50";

$resetGame = isset($_GET['new']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Heart Crush — Game</title>

  <!-- Libs -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Theme -->
  <link rel="stylesheet" href="../Assets/css/game.css?v=<?= time(); ?>" type="text/css">
  <link rel="stylesheet" href="../Assets/css/cursor.css">

  <!-- JS libs (loaded BEFORE game.js) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Confetti (required for celebration effects) -->
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

  <script defer src="../Assets/JS/bgaudio.js"></script>

  <!-- Main game logic -->
  <script src="../Assets/js/game.js" defer></script>
</head>

<body>

<div class="image-container">

  <!-- NAVBAR -->
  <nav class="navbar">
    <h1 class="logo">Heart❤️Crush</h1>
    <div class="links">

      <a href="index.php" title="Home"><i class="bi bi-house custom-icon"></i></a>
      <a href="scores.php" title="Scores"><i class="bi bi-trophy"></i></a>
      <a href="settings.php" title="Settings"><i class="bi bi-gear"></i></a>
      <a href="profile.php" title="Profile">
        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="nav-avatar">
      </a>

      <!-- Pause button in navbar -->
      <button id="pauseBtn" class="pauseBtn" type="button" title="Pause game">
        <i class="bi bi-pause-fill"></i>
      </button>

      <!-- Reset button -->
      <button id="resetbtn" class="resetBtn" title="Reset game" aria-label="Reset game">
        <i class="bi bi-arrow-counterclockwise"></i>
      </button>

      <div class="game-audio audio-controls">
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
  </nav>

  <!-- MAIN CONTENT -->
  <div class="container">
    <div class="sTitle">LET'S PLAY !</div>

    <!-- HUD -->
    <div class="single-Data hud">
      <div class="hud-card">
        <span class="hud-label">Level</span>
        <span class="hud-value" id="level-no">1</span>
      </div>

      <div class="hud-card">
        <span class="hud-label">Question</span>
        <span class="hud-value">
          <span id="question-number">1</span>/7
        </span>
      </div>

      <div class="hud-card">
        <span class="hud-label">Score</span>
        <span class="hud-value" id="score">0</span>
      </div>

      <!-- Circular timer -->
      <div class="timer">
        <div class="timer-ring" id="timerRing"></div>
        <div class="timer-core">
          <span class="timer-label">Time</span>
          <span class="timer-value" id="timer">30</span>
        </div>
      </div>
    </div>

    <!-- PUZZLE CARD (boss + celebration CSS targets .puzzle-card) -->
    <div class="imgApi puzzle-card">
      <img src="" alt="Question Image" id="imgApi" class="color-image puzzle-image">
    </div>

    <!-- ANSWER BAR -->
    <div class="ans-align answer-row">
      <p class="txtAns">
        <i class="bi bi-patch-question"></i>
        Enter the answer ( ? for hint, "skip" to skip ):
      </p>

      <input
        type="text"
        class="input-field answer-input"
        id="answer"
        name="input"
        placeholder="Type a number… ( ? for hint, 'skip' to skip )"
        inputmode="numeric"
      >

      <button
        type="button"
        class="btnGo answer-btn"
        id="btnGo"
        onclick="handleInput()"
      >
        Go!
      </button>
    </div>

    <!-- Note text under puzzle -->
    <div id="note" class="note">Ready?</div>

    <!-- Joke box shown after game over -->
    <div id="joke-box" class="joke-box d-none">
      <h5 class="joke-title">💬 Fun Break</h5>
      <div id="joke-setup"></div>
      <div id="joke-punchline"></div>
    </div>
  </div>

  <!-- Normal background music -->
  <audio id="music" loop>
    <source type="audio/mp3" src="../Assets/audio/backgroundMusic.mp3">
  </audio>

  <!-- Boss background music -->
  <audio id="bossMusic" loop>
    <source type="audio/mp3" src="../Assets/audio/bossMusic.mp3">
  </audio>

  <div class="boss-vignette"></div>
</div> <!-- /.image-container -->

<?php if ($resetGame): ?>
<script>
  // Clear saved game state when ?new is present
  (function () {
    const keys = ['timeLeft', 'score', 'numQuestions', 'currentLevel', 'streak', 'hintUsed'];
    keys.forEach(k => localStorage.removeItem(k));
  })();
</script>
<?php endif; ?>

<div class="hc-cursor"></div>
<div class="hc-cursor-outline"></div>
<script src="../Assets/js/cursor.js" defer></script>
</body>
</html>
