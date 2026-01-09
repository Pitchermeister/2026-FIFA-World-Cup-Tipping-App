<?php
/**
 * ================================================
 * MIGRATION SCRIPT - TEXT FILES → MySQL DATABASE
 * ================================================
 * 
 * Dieses Script migriert alle Daten aus den Text-Dateien
 * in die MySQL Datenbank.
 * 
 * WICHTIG: Vorher database_schema.sql ausführen!
 */

// === DATENBANK-VERBINDUNG ===
$host = 'localhost';
$database = 'fifa_tipping_2026';
$username = 'root';  // ANPASSEN!
$password = '';      // ANPASSEN!

echo "====================================\n";
echo "FIFA WC 2026 - DATA MIGRATION\n";
echo "====================================\n\n";

try {
    // Verbindung herstellen
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("❌ Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✅ Database connection successful\n\n";
    
    // UTF-8 einstellen
    $conn->set_charset("utf8mb4");
    
    // === 1. USERS MIGRIEREN ===
    echo "📁 Migrating users.txt...\n";
    
    if (file_exists('users.txt')) {
        $users = file('users.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $user_count = 0;
        
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, profile_pic, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), profile_pic=VALUES(profile_pic), role=VALUES(role)");
        
        foreach ($users as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 4) {
                $user = $parts[0];
                $hash = $parts[1];
                $pic = $parts[2] !== '' ? $parts[2] : null;
                $role = $parts[3];
                
                $stmt->bind_param('ssss', $user, $hash, $pic, $role);
                $stmt->execute();
                $user_count++;
            }
        }
        
        $stmt->close();
        echo "   ✅ Migrated $user_count users\n\n";
    } else {
        echo "   ⚠️  users.txt not found\n\n";
    }
    
    // === 2. TEAMS MIGRIEREN ===
    echo "📁 Migrating teams.txt...\n";
    
    if (file_exists('teams.txt')) {
        $teams = file('teams.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $team_count = 0;
        
        $stmt = $conn->prepare("INSERT INTO teams (group_name, team_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE team_name=VALUES(team_name)");
        
        foreach ($teams as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 2) {
                $group = trim($parts[0]);
                $team = trim($parts[1]);
                
                $stmt->bind_param('ss', $group, $team);
                $stmt->execute();
                $team_count++;
            }
        }
        
        $stmt->close();
        echo "   ✅ Migrated $team_count teams\n\n";
    } else {
        echo "   ⚠️  teams.txt not found\n\n";
    }
    
    // === 3. MATCHES MIGRIEREN ===
    echo "📁 Migrating matches.txt...\n";
    
    if (file_exists('matches.txt')) {
        $matches = file('matches.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $match_count = 0;
        
        foreach ($matches as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 5) {
                $date = $parts[0];
                $time = $parts[1];
                $team1_name = trim($parts[2]);
                $team2_name = trim($parts[3]);
                $group = $parts[4];
                
                // Team IDs finden
                $team1_result = $conn->query("SELECT id FROM teams WHERE team_name = '" . $conn->real_escape_string($team1_name) . "' LIMIT 1");
                $team2_result = $conn->query("SELECT id FROM teams WHERE team_name = '" . $conn->real_escape_string($team2_name) . "' LIMIT 1");
                
                if ($team1_result && $team2_result && $team1_result->num_rows > 0 && $team2_result->num_rows > 0) {
                    $team1_id = $team1_result->fetch_assoc()['id'];
                    $team2_id = $team2_result->fetch_assoc()['id'];
                    
                    // Match einfügen
                    $stmt = $conn->prepare("INSERT INTO matches (match_date, match_time, team1_id, team2_id, group_name) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('ssiis', $date, $time, $team1_id, $team2_id, $group);
                    $stmt->execute();
                    $stmt->close();
                    
                    $match_count++;
                }
            }
        }
        
        echo "   ✅ Migrated $match_count matches\n\n";
    } else {
        echo "   ⚠️  matches.txt not found\n\n";
    }
    
    // === 4. RESULTS MIGRIEREN ===
    echo "📁 Migrating results.txt...\n";
    
    if (file_exists('results.txt') && file_exists('matches.txt')) {
        $results = file('results.txt', FILE_IGNORE_NEW_LINES);
        $matches_lines = file('matches.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $result_count = 0;
        
        // Match IDs holen (in gleicher Reihenfolge wie matches.txt)
        $match_ids = [];
        foreach ($matches_lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 5) {
                $date = $parts[0];
                $time = $parts[1];
                $team1_name = trim($parts[2]);
                $team2_name = trim($parts[3]);
                
                $team1_result = $conn->query("SELECT id FROM teams WHERE team_name = '" . $conn->real_escape_string($team1_name) . "' LIMIT 1");
                $team2_result = $conn->query("SELECT id FROM teams WHERE team_name = '" . $conn->real_escape_string($team2_name) . "' LIMIT 1");
                
                if ($team1_result && $team2_result && $team1_result->num_rows > 0 && $team2_result->num_rows > 0) {
                    $team1_id = $team1_result->fetch_assoc()['id'];
                    $team2_id = $team2_result->fetch_assoc()['id'];
                    
                    $match_result = $conn->query("SELECT id FROM matches WHERE match_date = '$date' AND match_time = '$time' AND team1_id = $team1_id AND team2_id = $team2_id LIMIT 1");
                    
                    if ($match_result && $match_result->num_rows > 0) {
                        $match_ids[] = $match_result->fetch_assoc()['id'];
                    }
                }
            }
        }
        
        // Results einfügen
        $stmt = $conn->prepare("INSERT INTO results (match_id, home_score, away_score) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE home_score=VALUES(home_score), away_score=VALUES(away_score)");
        
        foreach ($results as $index => $line) {
            $line = trim($line);
            if ($line !== '' && isset($match_ids[$index])) {
                $parts = explode('|', $line);
                if (count($parts) >= 2) {
                    $home = (int)$parts[0];
                    $away = (int)$parts[1];
                    $match_id = $match_ids[$index];
                    
                    $stmt->bind_param('iii', $match_id, $home, $away);
                    $stmt->execute();
                    $result_count++;
                }
            }
        }
        
        $stmt->close();
        echo "   ✅ Migrated $result_count results\n\n";
    } else {
        echo "   ⚠️  results.txt or matches.txt not found\n\n";
    }
    
    // === ZUSAMMENFASSUNG ===
    echo "====================================\n";
    echo "✅ MIGRATION COMPLETE!\n";
    echo "====================================\n\n";
    
    // Statistiken
    $user_total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $team_total = $conn->query("SELECT COUNT(*) as count FROM teams")->fetch_assoc()['count'];
    $match_total = $conn->query("SELECT COUNT(*) as count FROM matches")->fetch_assoc()['count'];
    $result_total = $conn->query("SELECT COUNT(*) as count FROM results")->fetch_assoc()['count'];
    
    echo "📊 Database Statistics:\n";
    echo "   Users: $user_total\n";
    echo "   Teams: $team_total\n";
    echo "   Matches: $match_total\n";
    echo "   Results: $result_total\n\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
