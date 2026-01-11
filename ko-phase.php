<?php
session_start();
require 'db.php'; 

// Check prerequisites
if (!isset($_SESSION['group_winners'])) {
    header("Location: predictions.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// === 1. Load Data ===
$group_winners = $_SESSION['group_winners']; // Array: ['A' => 'Germany', 'B' => 'USA'...]
$group_runners = $_SESSION['group_runners'];
$group_third   = $_SESSION['group_third'];   // Top 8 only

$saved_post = $_SESSION['saved_post'] ?? [];

// Load from DB if session is empty
if (empty($saved_post)) {
    $stmt = $pdo->prepare("SELECT match_id, tip_winner FROM tips WHERE user_id = ? AND match_id BETWEEN 73 AND 104");
    $stmt->execute([$user_id]);
    while ($row = $stmt->fetch()) {
        $saved_post["winner_" . $row['match_id']] = $row['tip_winner'];
    }
    $_SESSION['saved_post'] = $saved_post;
}

// Load Matches
$matches = [];
$stmt = $pdo->query("SELECT * FROM matches WHERE id BETWEEN 73 AND 104 ORDER BY id ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $matches[$row['id']] = [
        'id' => $row['id'],
        'date' => $row['date'],
        'time' => $row['time'],
        'stage' => $row['group_name'],
        'p1' => $row['team1'],
        'p2' => $row['team2']
    ];
}

// Organize into Stages
$stages = [
    'Round of 32' => [], 'Round of 16' => [], 'Quarter Finals' => [],
    'Semi Finals' => [], 'Third Place' => [], 'Final' => []
];
foreach ($matches as $m) {
    $stages[$m['stage']][] = $m;
}

// === 2. THE SIMPLIFIED LOGIC ===

// This function takes a code like "A1", "Winner 73", or "A3/B3" 
// and returns a LIST (Array) of real team names.
function getTeams($code, $gw, $gr, $gt, $picks, $all_matches) {
    $list = [];
    
    // 1. Handle combinations (e.g. "A3/B3/C3")
    if (strpos($code, '/') !== false) {
        $parts = explode('/', $code);
        foreach ($parts as $part) {
            // Recursive call for each part
            $subList = getTeams($part, $gw, $gr, $gt, $picks, $all_matches);
            foreach ($subList as $name) $list[] = $name;
        }
        return $list;
    }

    $code = trim($code);
    $realName = $code; // Default to the code itself if we can't find a name

    // 2. Check for Group Positions (Length is 2, e.g. "A1")
    if (strlen($code) == 2) {
        $groupLetter = $code[0]; // "A"
        $position    = $code[1]; // "1"
        
        if ($position == '1') {
            $realName = $gw[$groupLetter] ?? $code;
        } elseif ($position == '2') {
            $realName = $gr[$groupLetter] ?? $code;
        } elseif ($position == '3') {
            // Only show if this 3rd place team qualified
            if (isset($gt[$groupLetter])) {
                $realName = $gt[$groupLetter];
            } else {
                return []; // Return empty list (Team didn't qualify)
            }
        }
    }
    
    // 3. Check for "Winner 73"
    elseif (strpos($code, 'Winner ') === 0) {
        $prevID = (int)substr($code, 7); // Remove "Winner " to get ID
        if (!empty($picks["winner_$prevID"])) {
            $realName = $picks["winner_$prevID"];
        }
    }
    
    // 4. Check for "Loser 101"
    elseif (strpos($code, 'Loser ') === 0) {
        $prevID = (int)substr($code, 6); // Remove "Loser "
        $prevWinner = $picks["winner_$prevID"] ?? null;
        
        if ($prevWinner && isset($all_matches[$prevID])) {
            $prevMatch = $all_matches[$prevID];
            // Find who played in that match
            $t1_opts = getTeams($prevMatch['p1'], $gw, $gr, $gt, $picks, $all_matches);
            $t2_opts = getTeams($prevMatch['p2'], $gw, $gr, $gt, $picks, $all_matches);
            $all_opts = array_merge($t1_opts, $t2_opts);
            
            // The loser is whoever is NOT the winner
            foreach ($all_opts as $t) {
                if ($t !== $prevWinner) $list[] = $t;
            }
            return $list; // Return directly
        }
    }

    if ($realName) {
        $list[] = $realName;
    }
    
    return array_unique($list);
}

// === 3. Check if a round is finished ===
function isComplete($start, $end, $data) {
    for ($i = $start; $i <= $end; $i++) {
        if (empty($data["winner_$i"])) return false;
    }
    return true;
}

// === 4. Handle Save Button ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // Save inputs to Session
    foreach ($matches as $id => $m) {
        if (isset($_POST["winner_$id"])) {
            $saved_post["winner_$id"] = $_POST["winner_$id"];
        }
    }
    $_SESSION['saved_post'] = $saved_post;

    if ($action === 'save') {
        $anchor = "r32";
        $error = null;

        // Check stages in order
        if (!isComplete(73, 88, $saved_post)) {
            $error = "Please complete the Round of 32.";
            $anchor = "r32";
        } elseif (!isComplete(89, 96, $saved_post)) {
            $error = "Please complete the Round of 16.";
            $anchor = "r16";
        } elseif (!isComplete(97, 100, $saved_post)) {
            $error = "Please complete the Quarter Finals.";
            $anchor = "qf";
        } elseif (!isComplete(101, 102, $saved_post)) {
            $error = "Please complete the Semi Finals.";
            $anchor = "sf";
        } elseif (!isComplete(103, 104, $saved_post)) {
            $error = "Please complete the Finals.";
            $anchor = "final";
        }

        if ($error) {
            // Stay here, show error
            header("Location: ko-phase.php#" . $anchor);
            exit;
        } else {
            // Save to DB
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO tips (user_id, match_id, tip_winner) 
                                       VALUES (?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE tip_winner = VALUES(tip_winner)");
                foreach ($saved_post as $k => $v) {
                    if (strpos($k, 'winner_') === 0 && !empty($v)) {
                        $mid = (int)str_replace('winner_', '', $k);
                        $stmt->execute([$user_id, $mid, $v]);
                    }
                }
                $pdo->commit();
                header("Location: mytips.php");
                exit;
            } catch (Exception $e) { $pdo->rollBack(); }
        }
    }
}

// Logic for showing rounds
$show_r32 = true;
$show_r16 = isComplete(73, 88, $saved_post);
$show_qf  = isComplete(89, 96, $saved_post) && $show_r16;
$show_sf  = isComplete(97, 100, $saved_post) && $show_qf;
$show_final = isComplete(101, 102, $saved_post) && $show_sf;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>KO Phase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> body{background:#f8f9fa} table{background:#fff} th,td{text-align:center;vertical-align:middle} </style>
</head>
<body>
<div class="container my-5">
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-center mb-0">Knockout Phase</h1>
    <a href="home.php" class="btn btn-outline-secondary btn-sm">Home</a>
  </div>

  <form method="post">
    <?php 
    $viewConfig = [
        'Round of 32' => ['show' => $show_r32, 'id' => 'r32'],
        'Round of 16' => ['show' => $show_r16, 'id' => 'r16'],
        'Quarter Finals' => ['show' => $show_qf, 'id' => 'qf'],
        'Semi Finals' => ['show' => $show_sf, 'id' => 'sf'],
        'Third Place' => ['show' => $show_final, 'id' => 'final'],
        'Final' => ['show' => $show_final, 'id' => 'final']
    ];

    foreach ($viewConfig as $stageName => $cfg):
        if (!$cfg['show'] || empty($stages[$stageName])) continue;
    ?>
        <div id="<?= $cfg['id'] ?>" class="mt-5">
            <h3 class="text-center mb-3"><?= htmlspecialchars($stageName) ?></h3>
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Team 1</th>
                            <th>Team 2</th>
                            <th style="width: 250px;">Who Advances?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stages[$stageName] as $m): 
                            $id = $m['id'];
                            
                            // 1. Get List of Teams for Team 1 column
                            $teams1 = getTeams($m['p1'], $group_winners, $group_runners, $group_third, $saved_post, $matches);
                            $label1 = empty($teams1) ? "-" : implode(" / ", $teams1);

                            // 2. Get List of Teams for Team 2 column
                            $teams2 = getTeams($m['p2'], $group_winners, $group_runners, $group_third, $saved_post, $matches);
                            $label2 = empty($teams2) ? "-" : implode(" / ", $teams2);

                            // 3. Merge for Dropdown
                            $dropdownOptions = array_unique(array_merge($teams1, $teams2));
                            
                            $selected = $saved_post["winner_$id"] ?? '';
                            $timeStr = substr($m['time'], 0, 5);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($m['date']) ?></td>
                            <td><?= $timeStr ?></td>
                            <td><?= htmlspecialchars($label1) ?></td>
                            <td><?= htmlspecialchars($label2) ?></td>
                            <td>
                                <select name="winner_<?= $id ?>" class="form-select text-center fw-bold">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($dropdownOptions as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>" <?= ($selected === $opt ? 'selected' : '') ?>>
                                            <?= htmlspecialchars($opt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="text-center mt-5 mb-5">
        <button type="submit" name="action" value="save" class="btn btn-primary px-4">
            <?= $show_final ? "Save & Finish" : "Save & Unlock Next Round" ?>
        </button>
    </div>

  </form>
</div>
</body>
</html>