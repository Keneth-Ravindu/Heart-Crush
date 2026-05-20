<?php
require_once __DIR__ . '/../Controller/config.php';

class User
{
    /** @var mysqli */
    private $db;

    public function __construct($db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            // Uses your existing Database class from config.php
            $this->db = Database::getInstance()->getConnection();
        }
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        $stmt->close();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        $stmt->close();

        return $user ?: null;
    }

    public function emailExists(string $email): bool
    {
        $sql = "SELECT 1 FROM users WHERE email = ? LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = (bool) $result->fetch_row();

        $stmt->close();

        return $exists;
    }

    public function create(
        string $fullName,
        string $email,
        string $passwordHash,
        ?string $avatarSeed = null,
        string $avatarStyle = 'lorelei'
    ): int {
        $sql = "INSERT INTO users (fullName, email, password, avatar_seed, avatar_style)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param(
            'sssss',
            $fullName,
            $email,
            $passwordHash,
            $avatarSeed,
            $avatarStyle
        );

        if (!$stmt->execute()) {
            $stmt->close();
            return 0;
        }

        $newId = (int) $this->db->insert_id;
        $stmt->close();

        return $newId;
    }

    public function updateProfile(
        int $userId,
        string $fullName,
        ?string $avatarSeed,
        string $avatarStyle
    ): bool {
        $sql = "UPDATE users
                SET fullName = ?,
                    avatar_seed = ?,
                    avatar_style = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            'sssi',
            $fullName,
            $avatarSeed,
            $avatarStyle,
            $userId
        );

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updateNameAndEmail(int $userId, string $fullName, string $email): bool
    {
        $sql = "UPDATE users
                SET fullName = ?, email = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssi', $fullName, $email, $userId);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $sql = "UPDATE users
                SET password = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('si', $passwordHash, $userId);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updateAvatar(int $userId, string $avatarStyle, ?string $avatarSeed): bool
    {
        $sql = "UPDATE users
                SET avatar_style = ?, avatar_seed = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssi', $avatarStyle, $avatarSeed, $userId);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}
