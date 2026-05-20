<?php
require_once __DIR__ . '/../Controller/config.php';

class GameState
{
    /** @var mysqli */
    private $db;

    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
    }

    public function findByUserId(int $userId): ?array
    {
        $sql = "SELECT *
                FROM game_state
                WHERE user_id = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $state  = $result->fetch_assoc();

        $stmt->close();

        return $state ?: null;
    }

    public function upsertState(int $userId, array $data): bool
    {
        // Defaults for any missing keys
        $defaults = [
            'time_left'     => 30,
            'score'         => 0,
            'num_questions' => 1,
            'current_level' => 1,
            'streak'        => 0,
            'hint_used'     => 0,
            'is_muted'      => 0,
            'is_paused'     => 0,
        ];

        $data = array_merge($defaults, $data);

        $sql = "INSERT INTO game_state (
                    user_id,
                    time_left,
                    score,
                    num_questions,
                    current_level,
                    streak,
                    hint_used,
                    is_muted,
                    is_paused,
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
                ON DUPLICATE KEY UPDATE
                    time_left     = VALUES(time_left),
                    score         = VALUES(score),
                    num_questions = VALUES(num_questions),
                    current_level = VALUES(current_level),
                    streak        = VALUES(streak),
                    hint_used     = VALUES(hint_used),
                    is_muted      = VALUES(is_muted),
                    is_paused     = VALUES(is_paused),
                    updated_at    = VALUES(updated_at)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            'iiiiiiiii',
            $userId,
            $data['time_left'],
            $data['score'],
            $data['num_questions'],
            $data['current_level'],
            $data['streak'],
            $data['hint_used'],
            $data['is_muted'],
            $data['is_paused']
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updateAudio(int $userId, bool $isMuted): bool
    {
        $sql = "INSERT INTO game_state (user_id, is_muted, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    is_muted   = VALUES(is_muted),
                    updated_at = VALUES(updated_at)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $muted = $isMuted ? 1 : 0;

        $stmt->bind_param('ii', $userId, $muted);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updatePause(int $userId, bool $isPaused): bool
    {
        $sql = "INSERT INTO game_state (user_id, is_paused, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    is_paused  = VALUES(is_paused),
                    updated_at = VALUES(updated_at)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $paused = $isPaused ? 1 : 0;

        $stmt->bind_param('ii', $userId, $paused);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function deleteForUser(int $userId): bool
    {
        $sql = "DELETE FROM game_state WHERE user_id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $userId);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
