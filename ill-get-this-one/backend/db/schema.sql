-- "I'll Get This One" — MySQL / MariaDB schema
-- Import this once via cPanel > phpMyAdmin (or the MySQL shell) into your database.
-- Safe to re-run: it only creates tables if they don't already exist.

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Members / accounts (the membership system).
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(80)  NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login tokens (one row per active session).
CREATE TABLE IF NOT EXISTS auth_tokens (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    token      CHAR(64)        NOT NULL,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME        NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (token),
    KEY idx_token_user (user_id),
    CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A group = an ongoing circle of friends whose points float between them.
CREATE TABLE IF NOT EXISTS groups_tbl (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(120)    NOT NULL,
    invite_code CHAR(8)         NOT NULL,
    created_by  BIGINT UNSIGNED NOT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_invite (invite_code),
    KEY idx_group_creator (created_by),
    CONSTRAINT fk_group_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membership of users in groups.
CREATE TABLE IF NOT EXISTS group_members (
    id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id  BIGINT UNSIGNED NOT NULL,
    user_id   BIGINT UNSIGNED NOT NULL,
    role      ENUM('admin','member') NOT NULL DEFAULT 'member',
    joined_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_group_user (group_id, user_id),
    KEY idx_member_user (user_id),
    CONSTRAINT fk_member_group FOREIGN KEY (group_id) REFERENCES groups_tbl(id) ON DELETE CASCADE,
    CONSTRAINT fk_member_user  FOREIGN KEY (user_id)  REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A gathering / event where someone picked up the bill ("I'll get this one").
CREATE TABLE IF NOT EXISTS events (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id     BIGINT UNSIGNED NOT NULL,
    payer_id     BIGINT UNSIGNED NOT NULL,
    description  VARCHAR(200)    NOT NULL DEFAULT '',
    occurred_on  DATE            NOT NULL,
    total_points INT UNSIGNED    NOT NULL DEFAULT 0,
    created_by   BIGINT UNSIGNED NOT NULL,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_event_group (group_id),
    KEY idx_event_payer (payer_id),
    CONSTRAINT fk_event_group FOREIGN KEY (group_id) REFERENCES groups_tbl(id) ON DELETE CASCADE,
    CONSTRAINT fk_event_payer FOREIGN KEY (payer_id) REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Each attendee's estimated cost (in points) for a gathering.
CREATE TABLE IF NOT EXISTS event_shares (
    id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id  BIGINT UNSIGNED NOT NULL,
    points   INT UNSIGNED    NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_event_user (event_id, user_id),
    KEY idx_share_user (user_id),
    CONSTRAINT fk_share_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_share_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
