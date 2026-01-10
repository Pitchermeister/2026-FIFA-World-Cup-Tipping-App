<?php
session_start();
require 'db.php'; // Database connection

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Redirect if no group predictions calculated in session
if (!isset($_SESSION['group_winners'])) {
    header("Location: predictions.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$group_winners = $_SESSION['group_winners'];
$group_runners = $_SESSION['group_runners'];
$group_third   = $_SESSION['group_third']; // Top 8 Qualified 3rd Place Teams
$saved_post    = $_SESSION['saved_post'] ?? []; // User's current KO tips from Session

$error_msg = "";

// === 1. Load KO Matches from DB ===
$stmt = $pdo->query("SELECT * FROM matches WHERE id BETWEEN 73 AND 104 ORDER BY id ASC");
$matches_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matches = [];
$stages = [
    'Round of 32' => [], 'Round of 16' => [], 'Quarter Finals' => [],
    'Semi Finals' => [], 'Third Place' => [], 'Final' => []
];

foreach ($matches_db as $m) {
    $matches[$m['id']] = [
        'id' => $m['id'],
        'date' => $m['date'],
        'time' => $m['time'],
        'stage' => $m['group_name'],
        'p1' => $m['team1'],
        'p2' => $m['team2']
    ];
    $stages[$m['group_name']][] = $matches[$m['id']];
}

// (Removed "Load Existing Tips from DB" block as requested)

// === 2. Logic Functions (Resolvers) ===

// Resolve placeholders (A1, Winner 73, etc.) to Team Names
function resolvePossibleTeams($placeholder, $gw, $gr, $gt, $picks, $all_matches) {
    $candidates = [];
    $parts = explode('/', $placeholder); // Handle "A3/B3/C3"

    foreach ($parts as $p) {
        $p = trim($p);
        $teamName = "";

        // Case 1: Group Positions
        if (preg_match('/^([A-L])1$/', $p, $m)) {
            $teamName = $gw[$m[1]] ?? $p;
        }
        elseif (preg_match('/^([A-L])2$/', $p, $m)) {
            $teamName = $gr[$m[1]] ?? $p;
        }
        elseif (preg_match('/^([A-L])3$/', $p, $m)) {
            // Only add if this 3rd place team QUALIFIED (exists in the Top 8 list)
            if (isset($gt[$m[1]])) {
                $teamName = $gt[$m[1]];
            } else {
                continue; // Skip disqualified teams
            }
        }
        
        // Case 2: Winner of Previous Match
        elseif (preg_match('/^Winner (\d+)$/i', $p, $m)) {
            $prevID = (int)$m[1];
            $teamName = $picks["winner_$prevID"] ?? null;
        }
        
        // Case 3: Loser of Previous Match
        elseif (preg_match('/^Loser (\d+)$/i', $p, $m)) {
            $prevID = (int)$m[1];
            $prevWinner = $picks["winner_$prevID"] ?? null;
            
            if ($prevWinner && isset($all_matches[$prevID])) {
                $prevMatch = $all_matches[$prevID];
                $prevOpts = getMatchOptions($prevMatch, $gw, $gr, $gt, $picks, $all_matches);
                $losers = array_diff($prevOpts, [$prevWinner]);
                foreach ($losers as $l) $candidates[] = $l;
            } else {
                $teamName = null;
            }
        } 
        else {
            $teamName = $p; // Literal string (e.g. if Admin typed a name directly)
        }
        
        if ($teamName && !in_array($teamName, $candidates)) {
            $candidates[] = $teamName;
        }
    }
    return $candidates;
}

// Get dropdown options
function getMatchOptions($m, $gw, $gr, $gt, $picks, $all_matches) {
    $side1 = resolvePossibleTeams($m['p1'], $gw, $gr, $gt, $picks, $all_matches);
    $side2 = resolvePossibleTeams($m['p2'], $gw, $gr, $gt, $picks, $all_matches);
    $all = array_merge($side1, $side2);
    return array_values(array_unique($all));
}

// Get Table Display String
function getTeamDisplay($placeholder, $gw, $gr, $gt, $picks, $all_matches) {
    $candidates = resolvePossibleTeams($placeholder, $gw, $gr, $gt, $picks, $all_matches);
    if (empty($candidates)) return "-";
    // Show all candidates separated by slash
    return implode(" / ", $candidates);
}

// Helper: Check completion
function isRangeComplete($start, $end, $data) {
    for ($i = $start; $i <= $end; $i++) {
        if (empty($data["winner_$i"])) return false;
    }
    return true;
}

// === 3. Handle POST (Save) ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // Capture inputs
    foreach ($matches as $id => $m) {
        if (isset($_POST["winner_$id"])) {
            $saved_post["winner_$id"] = $_POST["winner_$id"];
        }
    }
    $_SESSION['saved_post'] = $saved_post;

    if ($action === 'save') {
        $valError = null;
        $anchor = "r32";

        // Check stages sequentially
        if (!isRangeComplete(73, 88, $saved_post)) {
            $valError = "Please complete the Round of 32.";
            $anchor = "r32";
        } elseif (!isRangeComplete(89, 96, $saved_post)) {
            $valError = "Please complete the Round of 16.";
            $anchor = "r16";
        } elseif (!isRangeComplete(97, 100, $saved_post)) {
            $valError = "Please complete the Quarter Finals.";
            $anchor = "qf";
        } elseif (!isRangeComplete(101, 102, $saved_post)) {
            $valError = "Please complete the Semi Finals.";
            $anchor = "sf";
        } elseif (!isRangeComplete(103, 104, $saved_post)) {
            $valError = "Please predict the Finals.";
            $anchor = "final";
        }

        if ($valError) {
            $error_msg = $valError;
            // Jump to problem area
            header("Location: ko-phase.php#" . $anchor);
            exit;
        } else {
            // All good -> Save to DB
            try {
                $pdo->beginTransaction();
                $sql = "INSERT INTO tips (user_id, match_id, tip_winner) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE tip_winner = VALUES(tip_winner)";
                $stmt = $pdo->prepare($sql);

                foreach ($saved_post as $key => $val) {
                    if (strpos($key, 'winner_') === 0 && !empty($val)) {
                        $mid = (int)str_replace('winner_', '', $key);
                        $stmt->execute([$user_id, $mid, $val]);
                    }
                }
                $pdo->commit();
                
                header("Location: mytips.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error_msg = "Database Error: " . $e->getMessage();
            }
        }
    }
}

// Determine Visibility
$show_r32 = true;
$show_r16 = isRangeComplete(73, 88, $saved_post);
$show_qf  = isRangeComplete(89, 96, $saved_post) && $show_r16;
$show_sf  = isRangeComplete(97, 100, $saved_post) && $show_qf;
$show_final = isRangeComplete(101, 102, $saved_post) && $show_sf;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>KO Phase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> 
    body{background:#f8f9fa} 
    table{background:#fff} 
    th,td{text-align:center;vertical-align:middle} 
  </style>
</head>
<body>
<div class="container my-5">
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-center mb-0">Knockout Phase</h1>
    <a href="home.php" class="btn btn-outline-secondary btn-sm">Home</a>
  </div>

  <?php if ($error_msg): ?>
      <div class="alert alert-danger text-center alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($error_msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
  <?php endif; ?>

  <?php if (empty($matches)): ?>
      <div class="alert alert-warning text-center">No KO matches found in database.</div>
  <?php else: ?>

  <form method="post">
    
    <?php 
    $renderList = [
        'Round of 32' => $show_r32,
        'Round of 16' => $show_r16,
        'Quarter Finals' => $show_qf,
        'Semi Finals' => $show_sf,
        'Third Place' => $show_final,
        'Final' => $show_final
    ];
    
    $anchorIds = [
        'Round of 32' => 'r32', 'Round of 16' => 'r16', 'Quarter Finals' => 'qf',
        'Semi Finals' => 'sf', 'Third Place' => 'final', 'Final' => 'final'
    ];

    foreach ($renderList as $stageName => $isVisible):
        if (!$isVisible || empty($stages[$stageName])) continue;
        $anchorId = $anchorIds[$stageName] ?? '';
    ?>
        <div id="<?= $anchorId ?>" class="mt-5">
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
                            
                            $options = getMatchOptions($m, $group_winners, $group_runners, $group_third, $saved_post, $matches);
                            $t1_display = getTeamDisplay($m['p1'], $group_winners, $group_runners, $group_third, $saved_post, $matches);
                            $t2_display = getTeamDisplay($m['p2'], $group_winners, $group_runners, $group_third, $saved_post, $matches);
                            
                            $selected = $saved_post["winner_$id"] ?? '';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($m['date']) ?></td>
                            <td><?= htmlspecialchars($m['time']) ?></td>
                            <td><?= htmlspecialchars($t1_display) ?></td>
                            <td><?= htmlspecialchars($t2_display) ?></td>
                            <td>
                                <select name="winner_<?= $id ?>" class="form-select text-center fw-bold">
                                    <option value="">-- Select Winner --</option>
                                    <?php foreach ($options as $opt): ?>
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
  <?php endif; ?>
</div>
</body>
</html>