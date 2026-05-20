<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Model/GameState.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function hc_json_response(int $code, array $data): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$db            = Database::getInstance()->getConnection(); // mysqli
$gameStateRepo = new GameState($db);
function hc_get_current_user_id(mysqli $db): ?int {
    // Common patterns
    if (!empty($_SESSION['user']['id'])) {
        return (int) $_SESSION['user']['id'];
    }
    if (!empty($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }
    if (!empty($_SESSION['id'])) {
        return (int) $_SESSION['id'];
    }

    // Fallback: look up by email if stored
    $email = $_SESSION['email'] ?? null;
    if (!$email) return null;

    if ($stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1")) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stmt->close();
                return (int) $row['id'];
            }
        }
        $stmt->close();
    }
    return null;
}

$userId = hc_get_current_user_id($db);
if ($userId === null) {
    hc_json_response(401, ['status' => 'error', 'message' => 'Not logged in / no user id in session']);
}
function hc_load_game_state(GameState $repo, int $userId, bool $autoCreate = true): array {
    $row = $repo->findByUserId($userId);

    if ($row !== null) {
        $currentLevel = (int)($row['current_level'] ?? 1);
        $numQuestions = (int)($row['num_questions'] ?? 1);
        $timeLeft     = (int)($row['time_left'] ?? 0);

        if ($currentLevel <= 1 && $numQuestions <= 1 && $timeLeft > 30) {
            $timeLeft = 30;
            $row['time_left'] = 30;

            // Persist the corrected value back to DB
            $repo->upsertState($userId, [
                'time_left'     => 30,
                'score'         => (int)($row['score'] ?? 0),
                'num_questions' => $numQuestions,
                'current_level' => $currentLevel,
                'streak'        => (int)($row['streak'] ?? 0),
                'hint_used'     => (int)($row['hint_used'] ?? 0),
                'is_muted'      => (int)($row['is_muted'] ?? 0),
                'is_paused'     => (int)($row['is_paused'] ?? 0),
            ]);
        }

        return $row;
    }

    // No row in DB, create defaults with 30 seconds
    $default = [
        'user_id'       => $userId,
        'time_left'     => 30,
        'score'         => 0,
        'num_questions' => 1,
        'current_level' => 1,
        'streak'        => 0,
        'hint_used'     => 0,
        'is_muted'      => 0,
        'is_paused'     => 0,
    ];

    if (!$autoCreate) {
        return $default;
    }

    // Insert default row via upsert
    $repo->upsertState($userId, $default);

    // Try to load again; if still missing, fall back to defaults
    $row = $repo->findByUserId($userId);
    return $row ?: $default;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'load';

switch ($action) {
    // LOAD 
    case 'load':
        $state = hc_load_game_state($gameStateRepo, $userId, true);
        hc_json_response(200, [
            'status' => 'ok',
            'state'  => $state,
        ]);
        break;

    // SAVE
    case 'save':
        $current = hc_load_game_state($gameStateRepo, $userId, true);

        $timeLeft     = isset($_POST['time_left'])     ? (int)$_POST['time_left']     : (int)$current['time_left'];
        $score        = isset($_POST['score'])         ? (int)$_POST['score']         : (int)$current['score'];
        $numQuestions = isset($_POST['num_questions']) ? (int)$_POST['num_questions'] : (int)$current['num_questions'];
        $currentLevel = isset($_POST['current_level']) ? (int)$_POST['current_level'] : (int)$current['current_level'];
        $streak       = isset($_POST['streak'])        ? (int)$_POST['streak']        : (int)$current['streak'];
        $hintUsed     = isset($_POST['hint_used'])     ? (int)$_POST['hint_used']     : (int)$current['hint_used'];
        $isMuted      = isset($_POST['is_muted'])      ? (int)$_POST['is_muted']      : (int)$current['is_muted'];
        // paused flag
        $isPaused     = isset($_POST['is_paused'])
            ? (int)$_POST['is_paused']
            : (int)($current['is_paused'] ?? 0);

        // sanitize
        $timeLeft     = max(0, $timeLeft);
        $score        = max(0, $score);
        $numQuestions = max(1, $numQuestions);
        $currentLevel = max(1, $currentLevel);
        $streak       = max(0, $streak);
        $hintUsed     = $hintUsed ? 1 : 0;
        $isMuted      = $isMuted ? 1 : 0;
        $isPaused     = $isPaused ? 1 : 0;

        $ok = $gameStateRepo->upsertState($userId, [
            'time_left'     => $timeLeft,
            'score'         => $score,
            'num_questions' => $numQuestions,
            'current_level' => $currentLevel,
            'streak'        => $streak,
            'hint_used'     => $hintUsed,
            'is_muted'      => $isMuted,
            'is_paused'     => $isPaused,
        ]);

        if ($ok) {
            hc_json_response(200, ['status' => 'ok']);
        } else {
            hc_json_response(500, ['status' => 'error', 'message' => 'Failed to update game state']);
        }
        break;

    // RESET
    case 'reset':
        $baseTime = 30;  // start at 30s on reset

        // keep is_muted from existing row if present
        $existing = $gameStateRepo->findByUserId($userId);
        $isMuted  = isset($existing['is_muted']) ? (int)$existing['is_muted'] : 0;
        $isPaused = 0; // on reset we consider game not paused

        $ok = $gameStateRepo->upsertState($userId, [
            'time_left'     => $baseTime,
            'score'         => 0,
            'num_questions' => 1,
            'current_level' => 1,
            'streak'        => 0,
            'hint_used'     => 0,
            'is_muted'      => $isMuted,
            'is_paused'     => $isPaused,
        ]);

        if ($ok) {
            $state = hc_load_game_state($gameStateRepo, $userId, true);
            hc_json_response(200, ['status' => 'ok', 'state' => $state]);
        } else {
            hc_json_response(500, ['status' => 'error', 'message' => 'Failed to reset']);
        }
        break;

    // AUDIO GET
    case 'get_audio':
        $state = hc_load_game_state($gameStateRepo, $userId, true);
        hc_json_response(200, [
            'status'   => 'ok',
            'is_muted' => (int)($state['is_muted'] ?? 0),
        ]);
        break;

    // AUDIO SET
    case 'set_audio':
        $isMuted = !empty($_POST['is_muted']);

        $ok = $gameStateRepo->updateAudio($userId, (bool)$isMuted);

        if ($ok) {
            hc_json_response(200, ['status' => 'ok', 'is_muted' => $isMuted ? 1 : 0]);
        } else {
            hc_json_response(500, ['status' => 'error', 'message' => 'Failed to update audio']);
        }
        break;

    default:
        hc_json_response(400, ['status' => 'error', 'message' => 'Unknown action']);
}
