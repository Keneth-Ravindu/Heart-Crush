const QUESTIONS_PER_LEVEL   = 7;   // 5 -> 7 questions to level up
const BASE_TIME_LEVEL_1     = 30;  // 30 seconds for level 1
const TIME_DECREASE_PER_LVL = 5;   // 3 -> 5 seconds less each level
const MIN_TIME_PER_ROUND    = 8;   // 15 -> 8 seconds floor
const WRONG_PENALTY_SEC     = 10;  // 4 -> 10 seconds lost on wrong answer
const FAST_ANSWER_WINDOW    = 3;   // 5 -> 3 seconds for fast bonus
const FAST_ANSWER_BONUS_SEC = 1;   // 2 -> 1 second bonus
const LEVEL_UP_BONUS        = 3;   // 5 -> 3 bonus points on level-up
const SKIP_PENALTY_SEC      = 15;  // 6 -> 15 seconds penalty for skip
const MAX_STREAK_MULT       = 4;   // 5 -> 4 max multiplier
const HINT_PENALTY_SEC      = 7;   // 4 -> 7 seconds cost for hint

// Persistent state (stored in DB via gameStateHandler.php)
const GAME_STATE_ENDPOINT = "../Controller/gameStateHandler.php";

let timeLeft     = BASE_TIME_LEVEL_1;
let score        = 0;
let numQuestions = 1;
let currentLevel = 1;
let streak       = 0;
let hintUsed     = false;
let stateLoaded = false;
let isPaused = false;
let remainingSkips  = 2; // only 2 skips per game
let remainingHints  = 3; // only 3 hints per game

// Runtime state
let timer = null;
let imgApi, solution;
let lastQuestionStartAt = Date.now();
let scoreSubmitted = false; // prevent duplicate score submits per game

// Audio FX
const correctAnsSound = new Audio("../Assets/audio/correct.mp3");
const wrongAnsSound   = new Audio("../Assets/audio/incorrect.mp3");
const levelUpSound    = new Audio("../Assets/audio/levelComplete.mp3");
const timeOutSound    = new Audio("../Assets/audio/timesUp.mp3");

// BOSS LEVEL CONFIG
const BOSS_EVERY_LEVELS       = 2;    // every 2nd level is a boss
const BOSS_TIME_MULTIPLIER    = 0.5;  // boss rounds = 50% of normal time
const BOSS_WRONG_PENALTY_MULT = 3;    // wrong answers cost 3x time on boss
const BOSS_LEVEL_LABEL_ICON   = "⚔️"; // shown next to level number
// ------------------------------------------------------

// Helpers
const $      = id => document.getElementById(id);
const clamp  = (v, a, b) => Math.max(a, Math.min(b, v));
const pulse  = el => { el.classList.remove("pulse"); void el.offsetWidth; el.classList.add("pulse"); };

/* Boss visual effects */

function applyBossVisuals(active) {
  if (active) {
    document.body.classList.add("boss-level-active");
  } else {
    document.body.classList.remove("boss-level-active");
  }
}

function triggerScreenShake() {
  document.body.classList.add("screen-shake");
  setTimeout(() => {
    document.body.classList.remove("screen-shake");
  }, 350);
}

// Boss audio helper (talks to bgaudio.js)
function updateBossMusicForLevel() {
  if (typeof window.setBossMusicMode === "function") {
    window.setBossMusicMode(isBossLevel(currentLevel));
  }
}

// Boss intro popup
function triggerBossIntro() {
  Swal.fire({
    title: `BOSS LEVEL ${currentLevel}`,
    html: `
      <div class="boss-intro-text">
        Survive the challenge and claim your reward!
      </div>
    `,
    icon: undefined,
    showConfirmButton: false,
    timer: 1300,
    customClass: {
      popup: 'swal-boss-intro',
      title: 'swal-boss-intro-title',
      htmlContainer: 'swal-boss-intro-body'
    }
  });
}

// Simple confetti celebration
function celebrate(kind = "small") {
  if (typeof confetti !== "function") return; // safety if CDN fails

  const isBig = kind === "big";

  confetti({
    particleCount: isBig ? 200 : 80,
    spread: isBig ? 120 : 80,
    startVelocity: isBig ? 45 : 30,
    scalar: isBig ? 1.1 : 0.9,
    ticks: isBig ? 220 : 140,
    origin: { x: 0.5, y: 0.6 }
  });
}

// Puzzle card “pop” animation on big moments
function flashPuzzleCard() {
  const card = document.querySelector(".puzzle-card");
  if (!card) return;
  card.classList.remove("level-celebrate");
  void card.offsetWidth; // reflow
  card.classList.add("level-celebrate");
}

function isBossLevel(level) {
  return level > 0 && (level % BOSS_EVERY_LEVELS === 0);
}

function levelBaseTime(level) {
  // Start with linear reduction
  let linear = BASE_TIME_LEVEL_1 - (level - 1) * TIME_DECREASE_PER_LVL;
  let base   = clamp(linear, MIN_TIME_PER_ROUND, 999);

  // After level 10, time shrinks faster (exponential drop)
  if (level > 10) {
    const extraLevels = level - 10;
    const factor      = Math.pow(0.85, extraLevels); // each level after 10 shrinks by 15%
    base = Math.max(Math.round(base * factor), MIN_TIME_PER_ROUND);
  }

  // Boss levels have less time on top of that
  if (isBossLevel(level)) {
    base = Math.max(Math.round(base * BOSS_TIME_MULTIPLIER), MIN_TIME_PER_ROUND);
  }

  return base;
}

function multiplierFromStreak(streak) {
  return clamp(1 + Math.floor(streak / 2), 1, MAX_STREAK_MULT);
}

// DB-backed game state helpers

async function loadGameStateFromServer() {
  try {
    const res = await fetch(`${GAME_STATE_ENDPOINT}?action=load`, {
      credentials: "same-origin"
    });

    if (!res.ok) {
      throw new Error("HTTP " + res.status);
    }

    const data = await res.json();
    if (data && data.status === "ok" && data.state) {
      const s = data.state;

      timeLeft     = parseInt(s.time_left     ?? BASE_TIME_LEVEL_1, 10);
      score        = parseInt(s.score         ?? 0, 10);
      numQuestions = parseInt(s.num_questions ?? 1, 10);
      currentLevel = parseInt(s.current_level ?? 1, 10);
      streak       = parseInt(s.streak        ?? 0, 10);
      hintUsed     = !!parseInt(s.hint_used   ?? 0, 10);

      // read pause from server if present, else assume not paused
      const pausedVal = parseInt(s.is_paused ?? 0, 10);
      isPaused = pausedVal === 1;

      // timeLeft <= 0 is treated as "game over" and will be handled on init.
      if (!Number.isFinite(timeLeft) || isNaN(timeLeft)) {
        timeLeft = 0;
      }
    }
  } catch (err) {
    console.error("[HC] Failed to load game state from server:", err);
    // Keep defaults on error
  } finally {
    stateLoaded = true;
  }
}

function saveGameState(extra = {}) {
  try {
    const payload = new URLSearchParams();
    payload.append("action", "save");
    payload.append("time_left", String(timeLeft));
    payload.append("score", String(score));
    payload.append("num_questions", String(numQuestions));
    payload.append("current_level", String(currentLevel));
    payload.append("streak", String(streak));
    payload.append("hint_used", hintUsed ? "1" : "0");

    // always send pause state
    payload.append("is_paused", isPaused ? "1" : "0");

    if (typeof extra.is_muted !== "undefined") {
      payload.append("is_muted", extra.is_muted ? "1" : "0");
    }

    fetch(GAME_STATE_ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      credentials: "same-origin",
      body: payload.toString()
    }).catch(() => {});
  } catch (err) {
    console.error("[HC] Failed to save game state:", err);
  }
}

function resetGameOnServer() {
  try {
    fetch(GAME_STATE_ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      credentials: "same-origin",
      body: "action=reset"
    }).catch(() => {});
  } catch (err) {
    console.error("[HC] Failed to reset game state:", err);
  }
}

// PAUSE / RESUME HELPERS (SweetAlert version)
async function showPauseDialog() {
  // Check if we are on a boss level
  const boss = isBossLevel(currentLevel);

  let quoteHtml = "";

  try {
    const response = await fetch(`../Controller/narutoQuotes.php?type=${boss ? "boss" : "normal"}`);

    if (response.ok) {
      const data = await response.json();
      const quote = data.quote || "Believe in yourself and keep moving forward.";
      const character = data.character || "Naruto Uzumaki";

      // Naruto quote appears at the top of the popup
      quoteHtml = `
        <div class="pause-naruto-quote" style="margin-bottom: 10px;">
          "<span>${quote}</span>"
          <br>
          <small>🍃 ${character}</small>
        </div>
        <hr class="pause-divider" style="margin: 12px 0;">
      `;
    }
  } catch (error) {
    console.error("Failed to load Naruto quote:", error);
  }

  Swal.fire({
    title: "Game Paused",
    html: `
      ${quoteHtml}
      <p>What would you like to do?</p>
    `,
    icon: "info",
    showCancelButton: true,
    confirmButtonText: "Continue",
    cancelButtonText: "Exit",
    allowOutsideClick: false,
    allowEscapeKey: false,
    reverseButtons: true,
    customClass: {
      popup: "swal-pause",
      confirmButton: "swal-btn-confirm-primary",
      cancelButton: "swal-btn-cancel-ghost"
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Continue game
      resumeGame();
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      // Exit game
      exitGame();
    } else {
      resumeGame();
    }
  });
}



function pauseGame() {
  if (isPaused) return;

  isPaused = true;
  clearInterval(timer);  // stop countdown

  // Save paused state
  saveGameState();

  // Show SweetAlert pause dialog
  showPauseDialog();
}

function resumeGame() {
  if (!isPaused) return;

  isPaused = false;

  // Save resumed state (not paused)
  saveGameState();

  // Restart timer from remaining time
  startTicking();
}

function exitGame() {
  // Mark as paused before leaving
  isPaused = true;
  saveGameState();

  // Redirect to home
  window.location.href = "../View/index.php";
}



// UI / timer helpers
function updateUI() {
  $("question-number").textContent = numQuestions;
  $("score").textContent           = score;
  $("timer").textContent           = timeLeft;

  // Level display with boss icon
  $("level-no").textContent = isBossLevel(currentLevel)
    ? `${currentLevel} ${BOSS_LEVEL_LABEL_ICON}`
    : currentLevel;

  // Toggle boss-level CSS class on <body>
  if (isBossLevel(currentLevel)) {
    document.body.classList.add("boss-level");
  } else {
    document.body.classList.remove("boss-level");
  }

  // circular timer ring
  const total = levelBaseTime(currentLevel);
  const pct   = clamp(Math.round((timeLeft / total) * 100), 0, 100);
  const ring  = $("timerRing");
  if (ring) {
    ring.style.setProperty("--progress", pct + "%");
    ring.style.setProperty("--progress-raw", pct);
  }
}

function startTicking() {
  clearInterval(timer);

  // don't start countdown if game is paused
  if (isPaused) return;

  timer = setInterval(() => {
    timeLeft--;
    if (timeLeft <= 0) {
      timeLeft = 0;
      updateUI();
      handleTimeOut();
    } else {
      updateUI();
    }
  }, 1000);
}

function gamenote(msg) {
  $("note").textContent = msg;
}

function resetForNewQuestion() {
  hintUsed = false;
  lastQuestionStartAt = Date.now();
  $("answer").value = "";
  $("answer").focus();
  gamenote("Ready?");
}

function applyWrongPenalty() {
  const boss    = isBossLevel(currentLevel);
  const penalty = WRONG_PENALTY_SEC * (boss ? BOSS_WRONG_PENALTY_MULT : 1);

  timeLeft = clamp(timeLeft - penalty, 0, 999);
  updateUI();
  return penalty;
}

function applyFastAnswerBonus() {
  const elapsed = Math.round((Date.now() - lastQuestionStartAt) / 1000);
  if (elapsed <= FAST_ANSWER_WINDOW) {
    timeLeft = clamp(timeLeft + FAST_ANSWER_BONUS_SEC, 0, 999);
    gamenote(`Nice & quick! +${FAST_ANSWER_BONUS_SEC}s`);
    updateUI();
  }
}


// GAME OVER POPUP

function showGameOverPopup(finalScore) {
  clearInterval(timer);

  Swal.fire({
    title: "Game Over",
    text: `Your final score is ${finalScore}. What would you like to do?`,
    icon: "info",
    showCancelButton: true,
    confirmButtonText: "Try Again",
    cancelButtonText: "Quit",
    allowOutsideClick: false,
    allowEscapeKey: false,
    reverseButtons: true
  }).then((result) => {
    // Always hide joke box when dialog is closed
    const jokeBox = document.getElementById("joke-box");
    if (jokeBox) {
      jokeBox.classList.add("d-none");
    }

    if (result.isConfirmed) {
      hardReset(false);
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      timeLeft       = levelBaseTime(1);
      score          = 0;
      numQuestions   = 1;
      currentLevel   = 1;
      streak         = 0;
      hintUsed       = false;
      scoreSubmitted = false;

      // Persist reset state
      saveGameState();

      // Now go to scores page
      window.location.href = "../View/scores.php";
    }
  });
}


// SAVE SCORE + QUIT
function saveScoreAndQuit(finalScore) {
  fetch("../Controller/updateScores.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "score=" + encodeURIComponent(finalScore)
  }).finally(() => {
    window.location.href = "../View/scores.php";
  });
}

async function fetchRandomJoke() {
  const jokeBox     = document.getElementById("joke-box");
  const setupEl     = document.getElementById("joke-setup");
  const punchlineEl = document.getElementById("joke-punchline");

  if (!jokeBox || !setupEl || !punchlineEl) return;

  try {
    const res = await fetch("https://official-joke-api.appspot.com/jokes/random");
    if (!res.ok) throw new Error("Network response was not ok");

    const data = await res.json();
    setupEl.textContent     = data.setup || "Need a break?";
    punchlineEl.textContent = data.punchline || "";
    jokeBox.classList.remove("d-none");
  } catch (err) {
    console.error("Error fetching joke:", err);
    setupEl.textContent     = "Even the joke server is tired… 🥲";
    punchlineEl.textContent = "";
    jokeBox.classList.remove("d-none");
  }
}

function submitScoreOnce() {
  if (scoreSubmitted) return;
  scoreSubmitted = true;

  fetch("../Controller/updateScores.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ score })
  }).catch(() => {});
}

function handleTimeOut() {
  clearInterval(timer);
  timeOutSound.play();

  // Persist score server-side
  submitScoreOnce();

  // Save final state
  saveGameState();

  // Show a fun joke after game over
  fetchRandomJoke();

  // Single popup with Try Again / Quit
  showGameOverPopup(score);
}

function levelUp() {
  const justBeatBoss = isBossLevel(currentLevel);
  const nextLevel    = currentLevel + 1;
  const bossComing   = isBossLevel(nextLevel);

  levelUpSound.play();

  if (justBeatBoss) {
    triggerScreenShake();
    Swal.fire({
      title: "BOSS DEFEATED!",
      html: `
        <div class="swal-boss-chip">VICTORY</div>
        <div class="swal-boss-chest">
          <div class="chest-lid"></div>
          <div class="chest-base"></div>
          <div class="chest-glow"></div>
        </div>
        <div class="swal-levelup-bonus">
          +${LEVEL_UP_BONUS} bonus points
        </div>
      `,
      icon: undefined,
      showConfirmButton: false,
      timer: 1800,
      customClass: {
        popup: 'swal-boss-defeated',
        title: 'swal-levelup-title',
        htmlContainer: 'swal-levelup-text'
      }
    });
  } else {
    Swal.fire({
      title: bossComing ? `LEVEL ${currentLevel} CLEARED` : "LEVEL UP!",
      html: `
        <div class="swal-levelup-chip">
          ${bossComing ? "BOSS INCOMING" : "STAGE CLEARED"}
        </div>
        <div class="swal-levelup-main">
          <span class="swal-levelup-label">Level</span>
          <span class="swal-levelup-number">${nextLevel}</span>
        </div>
        <div class="swal-levelup-bonus">
          +${LEVEL_UP_BONUS} bonus points
        </div>
      `,
      icon: undefined,
      showConfirmButton: false,
      timer: 1500,
      customClass: {
        popup: 'swal-levelup',
        title: 'swal-levelup-title',
        htmlContainer: 'swal-levelup-text'
      }
    });
  }

  // Move to the next level
  score        += LEVEL_UP_BONUS;
  currentLevel  = nextLevel;
  numQuestions  = 1;
  timeLeft      = levelBaseTime(currentLevel);

  applyBossVisuals(isBossLevel(currentLevel));
  pulse($("level-no"));
  updateUI();
  updateBossMusicForLevel();

  flashPuzzleCard();
  celebrate(justBeatBoss || bossComing ? "big" : "small");

  saveGameState();
}

function handleCorrect() {
  streak++;
  const mult     = multiplierFromStreak(streak);
  const baseGain = 1;
  const gained   = baseGain * mult;
  score += gained;

  pulse($("score"));
  correctAnsSound.play();

  gamenote(`Correct! Streak ×${mult} (+${gained})`);
  applyFastAnswerBonus();

  if (mult >= 4) {
    celebrate("small");
  }

  numQuestions++;
  if (numQuestions > QUESTIONS_PER_LEVEL) {
    levelUp();
    fetchImage();
  } else {
    fetchImage();
  }

  saveGameState();
}

function handleWrong() {
  wrongAnsSound.play();

  const boss        = isBossLevel(currentLevel);
  const suddenDeath = (currentLevel >= 12) || (timeLeft <= 5);

  if (suddenDeath) {
    if (boss) {
      triggerScreenShake();
    }
    gamenote("Sudden death… one mistake was enough.");
    timeLeft = 0;
    updateUI();
    handleTimeOut(); // will submit score + show game over popup
    return;
  }

  // non-sudden-death case

  streak = 0;

  if (boss) {
    triggerScreenShake();
  }

  const penaltyUsed = applyWrongPenalty();

  Swal.fire({
    title: boss ? "Boss Hit You!" : "Wrong Answer",
    text: `-${penaltyUsed}s penalty${boss ? " (Boss rage!)" : ""}`,
    icon: boss ? "warning" : "error",
    timer: 1100,
    showConfirmButton: false,
    customClass: {
      popup: 'swal-mini-error'
    }
  });

  gamenote(boss ? "The boss is furious! Be careful…" : "Try again!");

  saveGameState();
}


function useHint() {
  // No hints on boss levels
  if (isBossLevel(currentLevel)) {
    Swal.fire({
      title: "No hints this time!",
      text: "Boss levels must be solved without hints.",
      icon: "warning",
      timer: 1100,
      showConfirmButton: false
    });
    return;
  }

  if (hintUsed) {
    Swal.fire({
      title: "Hint already used",
      icon: "info",
      timer: 900,
      showConfirmButton: false
    });
    return;
  }

  if (remainingHints <= 0) {
    Swal.fire({
      title: "No hints left",
      text: "You’ve used all your hints for this run.",
      icon: "info",
      timer: 1100,
      showConfirmButton: false
    });
    return;
  }

  hintUsed = true;
  remainingHints--;

  const isEven = (parseInt(solution, 10) % 2 === 0);
  timeLeft     = clamp(timeLeft - HINT_PENALTY_SEC, 0, 999);
  updateUI();

  Swal.fire({
    title: "Hint",
    text: `The answer is ${isEven ? "even" : "odd"}. (-${HINT_PENALTY_SEC}s, ${remainingHints} hint(s) left)`,
    icon: "question"
  });

  saveGameState();
}


// Commands: "?" for hint, "skip" for skip
function handleCommandOrAnswer(rawValue) {
  const trimmed = String(rawValue ?? "").trim();
  const v       = trimmed.toLowerCase();

  console.log("[HC answer]", { raw: rawValue, trimmed, v });

  if (trimmed === "?") {
    useHint();
    return { kind: "hint" };
  }

if (v === "skip") {
  // No skipping on boss levels
  if (isBossLevel(currentLevel)) {
    Swal.fire({
      title: "Can't skip a boss!",
      text: "Face the challenge head on.",
      icon: "warning",
      timer: 1100,
      showConfirmButton: false
    });
    return { kind: "blocked-skip" };
  }

  if (remainingSkips <= 0) {
    Swal.fire({
      title: "No skips left",
      text: "You’ve used all your skips for this run.",
      icon: "info",
      timer: 1100,
      showConfirmButton: false
    });
    return { kind: "no-skip" };
  }

  remainingSkips--;
  timeLeft = clamp(timeLeft - SKIP_PENALTY_SEC, 0, 999);
  streak   = 0;

  Swal.fire({
    title: "Skipped",
    text: `-${SKIP_PENALTY_SEC}s penalty. (${remainingSkips} skip(s) left)`,
    icon: "warning",
    timer: 900,
    showConfirmButton: false,
    customClass: {
      popup: "swal-mini-warn"
    }
  });

  fetchImage();
  updateUI();
  saveGameState();
  return { kind: "skip" };
}


  if (!/^-?\d+$/.test(v)) {
    Swal.fire({
      title: "Invalid Input",
      text: "Enter a number, '?' for a hint, or 'skip'.",
      icon: "error",
      customClass: {
        popup: "swal-mini-error"
      }
    });
    return { kind: "invalid" };
  }

  return { kind: "number", value: parseInt(v, 10) };
}

function handleInput() {
  const btn = $("btnGo");
  if (btn) {
    btn.disabled = true;
    setTimeout(() => (btn.disabled = false), 300);
  }

  if (isPaused) {
    return;
  }

  if (timeLeft <= 0) {
    handleTimeOut();
    return;
  }

  const raw = $("answer").value;
  if (raw.trim() === "") {
    Swal.fire({
      title: "Empty Answer",
      text: "Please enter an answer",
      icon: "error",
      customClass: {
        popup: "swal-mini-error"
      }
    });
    return;
  }

  const result = handleCommandOrAnswer(raw);

  if (!result || result.kind !== "number") {
    $("answer").value = "";
    $("answer").focus();
    return;
  }

  if (result.value == solution) {
    handleCorrect();
  } else {
    handleWrong();
  }

  $("answer").value = "";
  $("answer").focus();
}

// Marc Conrad heart game api 
function fetchImage() {
  fetch("https://marcconrad.com/uob/heart/api.php")
    .then(r => r.json())
    .then(data => {
      imgApi   = data.question;
      solution = String(data.solution).trim();
      $("imgApi").src = imgApi;
      resetForNewQuestion();

      clearInterval(timer);
      startTicking();
      updateUI();

      const firstQuestionThisLevel = (numQuestions === 1);

      updateBossMusicForLevel();
      applyBossVisuals(isBossLevel(currentLevel));

      if (firstQuestionThisLevel && isBossLevel(currentLevel)) {
        triggerScreenShake();
        triggerBossIntro();
      }
    })
    .catch(() => {
      Swal.fire({
        title: "Network Error",
        text: "Could not load a new puzzle. Try again.",
        icon: "error",
        customClass: { popup: "swal-mini-error" }
      });
    });
}

function hardReset(confirmFirst = true) {
  const doReset = () => {
    clearInterval(timer);

    if (score > 0) {
      submitScoreOnce();
    }

    timeLeft       = levelBaseTime(1);
    score          = 0;
    numQuestions   = 1;
    currentLevel   = 1;
    streak         = 0;
    hintUsed       = false;
    scoreSubmitted = false;
    remainingSkips = 2;
    remainingHints = 3;


    resetGameOnServer();
    saveGameState();

    updateUI();
    fetchImage();
    gamenote("New game started. Good luck!");
  };

  if (!confirmFirst) {
    doReset();
    return;
  }

  Swal.fire({
    title: "Start Over?",
    text: "This will reset your level, score, and timer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, reset",
    cancelButtonText: "Cancel",
    customClass: {
      popup: "swal-reset",
      confirmButton: "swal-btn-confirm-warning",
      cancelButton: "swal-btn-cancel-ghost"
    }
  }).then(res => {
    if (res.isConfirmed) doReset();
  });
}


// INIT

document.addEventListener("DOMContentLoaded", () => {
  (async () => {
    await loadGameStateFromServer();

    // 🔹 Extra safety: start-of-game must never exceed base time
    if (currentLevel === 1 && numQuestions === 1) {
      const base = levelBaseTime(1);  // 30
      if (timeLeft > base) {
        timeLeft = base;
      }
    }

    if (timeLeft <= 0 || isNaN(timeLeft)) {
      timeLeft       = levelBaseTime(1);
      score          = 0;
      numQuestions   = 1;
      currentLevel   = 1;
      streak         = 0;
      hintUsed       = false;
      scoreSubmitted = false;
      remainingSkips = 2;
      remainingHints = 3;

      saveGameState();
      gamenote("New game started. Good luck!");
    }

    updateUI();
    fetchImage();

    // If DB says game is paused, show SweetAlert pause dialog
    if (isPaused) {
      clearInterval(timer);
      showPauseDialog();
    }

    $("answer").addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        handleInput();
      } else if (e.key === "?" && !e.shiftKey) {
        e.preventDefault();
        useHint();
      }
    });

    const resetBtn = document.getElementById("resetbtn");
    if (resetBtn) {
      resetBtn.addEventListener("click", () => hardReset(true));
    }

    // Pause button
    const pauseBtn = document.getElementById("pauseBtn");
    if (pauseBtn) {
      pauseBtn.addEventListener("click", () => {
        pauseGame();
      });
    }

    // ESC key -> pause
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        if (!isPaused) {
          pauseGame();
        }
        // when SweetAlert is open, allowEscapeKey:false prevents ESC from closing it
      }
    });
  })();
});
