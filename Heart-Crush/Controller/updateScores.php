<?php
ini_set('memory_limit', '256M');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Model/Score.php';

// Use the Database singleton
$db         = Database::getInstance()->getConnection();
$scoreModel = new Score($db);

header('Content-Type: application/json');

// 1) Make sure user is logged in
$loggedIn = !empty($_SESSION['user']['logged_in']) || !empty($_SESSION['loggedIn']);
if (!$loggedIn) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Not logged in'
    ]);
    exit;
}

// 2) Read score
$score = null;

// a) form-encoded: score in $_POST
if (isset($_POST['score']) && $_POST['score'] !== '') {
    $score = (int) $_POST['score'];
} else {
    // b) JSON body
    $raw = file_get_contents('php://input');
    if ($raw) {
        $data = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['score'])) {
            $score = (int) $data['score'];
        }
    }
}

if ($score === null || $score < 0) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'No valid score provided'
    ]);
    exit;
}

// 3) Get current user ID for scores.playerID
$userId = $_SESSION['user']['id'] ?? null;

// If haven’t wired user id into the session yet, fall back to email lookup
if (!$userId) {
    $email = $_SESSION['email'] ?? null;
    if ($email) {
        if ($stmt = $db->prepare("SELECT id FROM users WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $userId = (int) $row['id'];
                    // Cache in session for next time
                    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
                        $_SESSION['user'] = [];
                    }
                    $_SESSION['user']['id'] = $userId;
                }
            }
            $stmt->close();
        }
    }
}

if (!$userId) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Could not determine player ID'
    ]);
    exit;
}

// 4) Insert into scores using the Score model
$ok = $scoreModel->addScore($userId, $score);

if ($ok) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Score saved',
        'score'   => $score,
        'player'  => $userId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error saving score'
    ]);
}

