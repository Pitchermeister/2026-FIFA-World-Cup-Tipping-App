<?php
session_start();
require 'db.php';

// === 1. LOAD DATA ===

// A. Load Matches & Actual Results from DB (The Single Source of Truth)
// We get the stage (group_name) and the results (score_home, score_away, winner_ko)
$matches = [];
$stmt = $pdo->query("SELECT * FROM matches");
while ($row = $stmt->fetch()) {
    $matches[$row['id']] = $row;
}

// B. Load Users (Exclude Admins if desired)
$users = [];
$stmt = $pdo->query("SELECT id, username, profile_picture FROM users WHERE role != 'admin' ORDER BY username ASC");
$users_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// C. Load All Tips
$all_tips = [];
$stmt = $pdo->query("SELECT user_id, match_id, tip_home, tip_away, tip_winner FROM tips");
while ($row = $stmt->fetch()) {
    $all_tips[$row['user_id']][$row['match_id']] = $row;
}

// === 2. CALCULATE POINTS ===

$ranking = [];

foreach ($users_db as $u) {
    $uid = $u['id'];
    $tips = $all_tips[$uid] ?? [];
    
    // Initialize Counters
    $stats = [
        'name' => $u['username'],
        'avatar' => $u['profile_picture'],
        'exact' => 0,  // (3)
        'diff' => 0,   // (2)
        'winner' => 0, // (1)
        'r32' => 0,    // (2)
        'r16' => 0,    // (4)
        'qf' => 0,     // (8)
        'sf' => 0,     // (16)
        'bronze' => 0, // (24)
        'final' => 0,  // (32)
        'total' => 0
    ];

    foreach ($tips as $mid => $tip) {
        if (!isset($matches[$mid])) continue;
        $m = $matches[$mid];
        
        // Determine Stage Type
        $isKo = ($mid > 72);
        
        // --- Group Phase Logic ---
        if (!$isKo) {
            // Check if match has a result
            if ($m['score_home'] !== null && $m['score_away'] !== null) {
                // Check if user has a tip
                if ($tip['tip_home'] !== null && $tip['tip_away'] !== null) {
                    $th = (int)$tip['tip_home'];
                    $ta = (int)$tip['tip_away'];
                    $rh = (int)$m['score_home'];
                    $ra = (int)$m['score_away'];

                    // Scoring: Tiered (Highest applies)
                    // If Exact -> 3 pts total
                    if ($th === $rh && $ta === $ra) {
                        $stats['exact']++;
                        $stats['total'] += 3;
                    } 
                    // If Correct GD (but not exact) -> 2 pts total
                    elseif (($th - $ta) === ($rh - $ra)) {
                        $stats['diff']++;
                        $stats['total'] += 2;
                    } 
                    // If Correct Winner (but not GD/Exact) -> 1 pt total
                    elseif (($th <=> $ta) === ($rh <=> $ra)) {
                        $stats['winner']++;
                        $stats['total'] += 1;
                    }
                }
            }
        } 
        // --- KO Phase Logic ---
        else {
            if (!empty($m['winner_ko']) && !empty($tip['tip_winner'])) {
                $userPick = trim($tip['tip_winner']);
                $realWinner = trim($m['winner_ko']);

                if (strcasecmp($userPick, $realWinner) === 0) {
                    // Points based on Stage Name
                    $stage = $m['group_name'];
                    
                    if (strpos($stage, '32') !== false) {
                        $stats['r32']++;
                        $stats['total'] += 2;
                    } elseif (strpos($stage, '16') !== false) {
                        $stats['r16']++;
                        $stats['total'] += 4;
                    } elseif (strpos($stage, 'Quarter') !== false) {
                        $stats['qf']++;
                        $stats['total'] += 8;
                    } elseif (strpos($stage, 'Semi') !== false) {
                        $stats['sf']++;
                        $stats['total'] += 16;
                    } elseif (strpos($stage, 'Third') !== false) {
                        $stats['bronze']++;
                        $stats['total'] += 24;
                    } elseif (strpos($stage, 'Final') !== false) {
                        $stats['final']++;
                        $stats['total'] += 32;
                    }
                }
            }
        }
    }
    $ranking[] = $stats;
}

// === 3. SORTING ===
usort($ranking, function($a, $b) {
    return $b['total'] <=> $a['total'];
});

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Standings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th { text-align: center; vertical-align: middle; background-color: #e9ecef; font-size: 0.85rem; }
    td { text-align: center; vertical-align: middle; font-size: 0.95rem; }
    
    td:nth-child(2) { text-align: left; font-weight: bold; color: #333; min-width: 180px; } /* Username */
    td:first-child { color: #6c757d; width: 40px; } /* Rank */
    td:last-child { font-weight: bold; background-color: #f1f3f5; color: #0d6efd; font-size: 1.1rem; } /* Total */

    .user-avatar {
        width: 30px; height: 30px; border-radius: 50%; object-fit: cover;
        margin-right: 10px; border: 1px solid #dee2e6; vertical-align: middle;
    }
    .user-initial {
        width: 30px; height: 30px; border-radius: 50%; background-color: #dee2e6;
        color: #6c757d; display: inline-flex; align-items: center; justify-content: center;
        margin-right: 10px; font-weight: bold; font-size: 0.8rem; vertical-align: middle;
    }
    .table-warning td { background-color: #fff3cd !important; } /* Highlight current user */
  </style>
</head>
<body>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Standings</h2>
    <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
  </div>

  <div class="table-responsive shadow-sm bg-white rounded">
    <table class="table table-bordered table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Exact Score<br><small class="text-muted">(3)</small></th>
          <th>Goal Diff.<br><small class="text-muted">(2)</small></th>
          <th>Winner<br><small class="text-muted">(1)</small></th>
          <th>R32<br><small class="text-muted">(2)</small></th>
          <th>R16<br><small class="text-muted">(4)</small></th>
          <th>QF<br><small class="text-muted">(8)</small></th>
          <th>SF<br><small class="text-muted">(16)</small></th>
          <th>Bronze<br><small class="text-muted">(24)</small></th>
          <th>Champion<br><small class="text-muted">(32)</small></th>
          <th>Total Points</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($ranking)): ?>
            <tr><td colspan="12" class="text-center py-4 text-muted">No users found.</td></tr>
        <?php else: ?>
            <?php
            $rank = 1;
            $currentUser = $_SESSION['user'] ?? '';
            foreach ($ranking as $r) {
                // Highlight Logic
                $rowClass = ($r['name'] === $currentUser) ? 'table-warning' : '';
                
                // Avatar Logic
                $avatar = "";
                if (!empty($r['avatar']) && file_exists($r['avatar'])) {
                    $avatar = '<img src="'.htmlspecialchars($r['avatar']).'" class="user-avatar">';
                } else {
                    $initial = strtoupper(substr($r['name'], 0, 1));
                    $avatar = '<div class="user-initial">'.$initial.'</div>';
                }

                echo "<tr class='$rowClass'>";
                echo "<td>{$rank}</td>";
                echo "<td>{$avatar}" . htmlspecialchars($r['name']) . "</td>";
                echo "<td>{$r['exact']}</td>";
                echo "<td>{$r['diff']}</td>";
                echo "<td>{$r['winner']}</td>";
                echo "<td>{$r['r32']}</td>";
                echo "<td>{$r['r16']}</td>";
                echo "<td>{$r['qf']}</td>";
                echo "<td>{$r['sf']}</td>";
                echo "<td>{$r['bronze']}</td>";
                echo "<td>{$r['final']}</td>";
                echo "<td>{$r['total']}</td>";
                echo "</tr>";
                $rank++;
            }
            ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>