-- ================================================
-- FIFA WORLD CUP 2026 TIPPING GAME - DATABASE SCHEMA
-- ================================================

-- Datenbank erstellen
CREATE DATABASE IF NOT EXISTS fifa_tipping_2026
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE fifa_tipping_2026;

-- ================================================
-- TABELLE: users
-- ================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELLE: teams
-- ================================================
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name CHAR(1) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_team (group_name, team_name),
    INDEX idx_group (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELLE: matches
-- ================================================
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_date DATE NOT NULL,
    match_time TIME NOT NULL,
    team1_id INT NOT NULL,
    team2_id INT NOT NULL,
    group_name CHAR(1) NOT NULL,
    is_knockout BOOLEAN DEFAULT FALSE,
    knockout_round VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team1_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (team2_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_match_date (match_date),
    INDEX idx_group (group_name),
    INDEX idx_knockout (is_knockout, knockout_round)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELLE: results
-- ================================================
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL UNIQUE,
    home_score TINYINT UNSIGNED DEFAULT NULL,
    away_score TINYINT UNSIGNED DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    INDEX idx_match_id (match_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELLE: group_predictions
-- ================================================
CREATE TABLE group_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    home_prediction TINYINT UNSIGNED NOT NULL,
    away_prediction TINYINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_match (user_id, match_id),
    INDEX idx_user (user_id),
    INDEX idx_match (match_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELLE: knockout_predictions
-- ================================================
CREATE TABLE knockout_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    predicted_winner_id INT DEFAULT NULL,
    home_prediction TINYINT UNSIGNED DEFAULT NULL,
    away_prediction TINYINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (predicted_winner_id) REFERENCES teams(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_ko_match (user_id, match_id),
    INDEX idx_user (user_id),
    INDEX idx_match (match_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- VIEW: Standings berechnen (optional für Performance)
-- ================================================
CREATE OR REPLACE VIEW user_standings AS
SELECT 
    u.id,
    u.username,
    u.profile_pic,
    COUNT(CASE 
        WHEN gp.home_prediction = r.home_score 
        AND gp.away_prediction = r.away_score 
        THEN 1 
    END) * 3 AS exact_score_points,
    COUNT(CASE 
        WHEN (gp.home_prediction - gp.away_prediction) = (r.home_score - r.away_score)
        AND NOT (gp.home_prediction = r.home_score AND gp.away_prediction = r.away_score)
        THEN 1 
    END) * 2 AS goal_diff_points,
    COUNT(CASE 
        WHEN SIGN(gp.home_prediction - gp.away_prediction) = SIGN(r.home_score - r.away_score)
        AND (gp.home_prediction - gp.away_prediction) != (r.home_score - r.away_score)
        THEN 1 
    END) * 1 AS winner_points,
    (
        COUNT(CASE 
            WHEN gp.home_prediction = r.home_score 
            AND gp.away_prediction = r.away_score 
            THEN 1 
        END) * 3 +
        COUNT(CASE 
            WHEN (gp.home_prediction - gp.away_prediction) = (r.home_score - r.away_score)
            AND NOT (gp.home_prediction = r.home_score AND gp.away_prediction = r.away_score)
            THEN 1 
        END) * 2 +
        COUNT(CASE 
            WHEN SIGN(gp.home_prediction - gp.away_prediction) = SIGN(r.home_score - r.away_score)
            AND (gp.home_prediction - gp.away_prediction) != (r.home_score - r.away_score)
            THEN 1 
        END) * 1
    ) AS total_points
FROM users u
LEFT JOIN group_predictions gp ON u.id = gp.user_id
LEFT JOIN results r ON gp.match_id = r.match_id
WHERE u.role = 'user'
GROUP BY u.id, u.username, u.profile_pic
ORDER BY total_points DESC;

-- ================================================
-- ADMIN USER ANLEGEN (Passwort: admin123)
-- ================================================
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Hinweis: Passwort 'admin123' - NACH SETUP ÄNDERN!

-- ================================================
-- FERTIG!
-- ================================================
