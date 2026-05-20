<?php
require_once __DIR__ . '/../Controller/config.php';

class Score
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

    public function addScore(int $userId, int $score): bool
    {
        $sql = "INSERT INTO scores (playerID, score) VALUES (?, ?)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $userId, $score);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function getTopScores(int $limit = 10): array
    {
        $sql = "SELECT 
                    s.id           AS score_id,
                    s.playerID     AS player_id,
                    s.score        AS score,
                    s.datentime    AS datentime,
                    u.fullName     AS fullName,
                    u.email        AS email,
                    u.avatar_seed  AS avatar_seed,
                    u.avatar_style AS avatar_style
                FROM scores s
                INNER JOIN users u ON s.playerID = u.id
                ORDER BY s.score DESC, s.datentime ASC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows   = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();

        return $rows;
    }

    public function getBestScoreForUser(int $userId): ?array
    {
        $sql = "SELECT *
                FROM scores
                WHERE playerID = ?
                ORDER BY score DESC, datentime ASC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        $stmt->close();

        return $row ?: null;
    }

    public function getRecentScoresForUser(int $userId, int $limit = 10): array
    {
        $sql = "SELECT *
                FROM scores
                WHERE playerID = ?
                ORDER BY datentime DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ii', $userId, $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows   = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();

        return $rows;
    }

    public function getStatsForUser(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*)   AS gamesPlayed,
                    MAX(score) AS bestScore,
                    AVG(score) AS avgScore
                FROM scores
                WHERE playerID = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [
                'gamesPlayed' => 0,
                'bestScore'   => 0,
                'avgScore'    => 0,
            ];
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row    = $result->fetch_assoc() ?: [];

        $stmt->close();

        return [
            'gamesPlayed' => (int)($row['gamesPlayed'] ?? 0),
            'bestScore'   => (int)($row['bestScore']   ?? 0),
            'avgScore'    => $row['avgScore'] !== null ? (float)$row['avgScore'] : 0.0,
        ];
    }
}
