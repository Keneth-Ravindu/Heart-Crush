--  HeartCrush
--  (Users + Scores + Game State + Remember Tokens, with pause support)
-- 1) Create database (if not exists) and select it
CREATE DATABASE IF NOT EXISTS heartCrush CHARACTER
SET
  utf8mb4 COLLATE utf8mb4_unicode_ci;

USE heartCrush;

-- 2) Drop old tables if they exist
SET
  FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS remember_tokens;

DROP TABLE IF EXISTS game_state;

DROP TABLE IF EXISTS scores;

DROP TABLE IF EXISTS users;

SET
  FOREIGN_KEY_CHECKS = 1;

-- 3) Users table
CREATE TABLE
  users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fullName VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- password_hash() output
    -- Avatar fields for DiceBear
    avatar_seed VARCHAR(100) DEFAULT NULL,
    avatar_style VARCHAR(50) NOT NULL DEFAULT 'lorelei',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4) Scores table
CREATE TABLE
  scores (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    playerID INT UNSIGNED NOT NULL, -- references users.id
    score INT NOT NULL,
    datentime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_scores_player (playerID),
    CONSTRAINT fk_scores_users FOREIGN KEY (playerID) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 5) Game state table (persistent progress + audio + pause)
CREATE TABLE
  game_state (
    user_id INT UNSIGNED NOT NULL,
    time_left INT NOT NULL DEFAULT 30,
    score INT NOT NULL DEFAULT 0,
    num_questions INT NOT NULL DEFAULT 1,
    current_level INT NOT NULL DEFAULT 1,
    streak INT NOT NULL DEFAULT 0,
    hint_used TINYINT (1) NOT NULL DEFAULT 0,
    is_muted TINYINT (1) NOT NULL DEFAULT 0,
    is_paused TINYINT (1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_game_state_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 6) Remember-me tokens table (persistent login via secure cookies)
CREATE TABLE
  remember_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    selector CHAR(18) NOT NULL,
    hashed_validator CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_selector (selector),
    KEY idx_remember_user (user_id),
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;