<?php
require 'db.php';

echo "<h2>⚙️ Database Setup & Migration</h2>";

try {
    // === 1. Create Tables ===
    
    // Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT '',
        role VARCHAR(20) DEFAULT 'user',
        total_points INT DEFAULT 0
    )");
    echo "✅ Table 'users' created.<br>";

    // Matches Table
    // We stick to the text file logic: ID 1-104 is fixed
    $pdo->exec("CREATE TABLE IF NOT EXISTS matches (
        id INT PRIMARY KEY,
        date DATE,
        time TIME,
        group_name VARCHAR(50),
        team1 VARCHAR(100),
        team2 VARCHAR(100),
        score_home INT DEFAULT NULL,
        score_away INT DEFAULT NULL,
        winner_ko VARCHAR(100) DEFAULT NULL
    )");
    echo "✅ Table 'matches' created.<br>";

    // Tips Table
    // Unique constraint ensures one tip per match per user
    $pdo->exec("CREATE TABLE IF NOT EXISTS tips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        match_id INT NOT NULL,
        tip_home INT DEFAULT NULL,
        tip_away INT DEFAULT NULL,
        tip_winner VARCHAR(100) DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
        UNIQUE KEY user_match (user_id, match_id)
    )");
    echo "✅ Table 'tips' created.<br>";

    echo "<hr>";

    // === 2. Migrate Users (users.txt) ===
    if (file_exists("users.txt")) {
        $lines = file("users.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, profile_picture, role) VALUES (?, ?, ?, ?)");
        
        foreach ($lines as $line) {
            $parts = explode("|", $line);
            if (count($parts) >= 2) {
                // format: username|hash|profile|role
                $u = trim($parts[0]);
                $h = trim($parts[1]);
                $p = trim($parts[2] ?? '');
                $r = trim($parts[3] ?? 'user');
                
                $stmt->execute([$u, $h, $p, $r]);
                $count++;
            }
        }
        echo "📦 Migrated $count users from users.txt<br>";
    } else {
        echo "⚠️ users.txt not found. Skipping user migration.<br>";
    }

    // === 3. Migrate Matches (matches.txt) ===
    if (file_exists("matches.txt")) {
        $lines = file("matches.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        // INSERT IGNORE keeps existing IDs if you run this twice
        $stmt = $pdo->prepare("INSERT IGNORE INTO matches (id, date, time, group_name, team1, team2) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($lines as $line) {
            $parts = explode("|", $line);
            // format: ID|Date|Time|Group|Team1|Team2
            if (count($parts) >= 6) {
                $id = (int)$parts[0];
                $date = trim($parts[1]);
                $time = trim($parts[2]);
                $grp = trim($parts[3]);
                $t1 = trim($parts[4]);
                $t2 = trim($parts[5]);

                $stmt->execute([$id, $date, $time, $grp, $t1, $t2]);
                $count++;
            }
        }
        echo "📦 Migrated $count matches from matches.txt<br>";
    } else {
        echo "⚠️ matches.txt not found. Skipping match migration.<br>";
    }

    // === 4. Migrate Results (results.txt) - Optional Update ===
    if (file_exists("results.txt")) {
        $lines = file("results.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $count = 0;
        $stmtGroup = $pdo->prepare("UPDATE matches SET score_home=?, score_away=? WHERE id=?");
        $stmtKO = $pdo->prepare("UPDATE matches SET winner_ko=? WHERE id=?");

        foreach ($lines as $line) {
            $parts = explode("|", $line);
            $id = (int)$parts[0];
            
            if (count($parts) === 3) {
                // Group: ID|Home|Away
                $stmtGroup->execute([(int)$parts[1], (int)$parts[2], $id]);
                $count++;
            } elseif (count($parts) === 2) {
                // KO: ID|Winner
                $stmtKO->execute([trim($parts[1]), $id]);
                $count++;
            }
        }
        echo "📦 Migrated $count results from results.txt<br>";
    }

    echo "<hr><h3>🎉 Database Setup Complete!</h3>";
    echo "You can now delete the .txt files if you wish (but keeping a backup is smart).";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>