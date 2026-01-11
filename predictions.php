<?php
session_start();
require 'db.php'; // Database connection

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$error = "";

// === 1. Load Group Matches from DB ===
$stmt = $pdo->query("SELECT * FROM matches WHERE id BETWEEN 1 AND 72 ORDER BY id ASC");
$matches_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize by Group
$groups = [];
$matchMap = [];

foreach ($matches_db as $m) {
    $g = $m['group_name']; // e.g. 'A'
    $groups[$g][] = $m;
    $matchMap[$m['id']] = $m;
}
ksort($groups); 

// === 2. Load Existing User Tips from DB ===
$user_tips = [];
$stmt = $pdo->prepare("SELECT match_id, tip_home, tip_away FROM tips WHERE user_id = ?");
$stmt->execute([$user_id]);
while ($row = $stmt->fetch()) {
    $user_tips["match_{$row['match_id']}_home"] = $row['tip_home'];
    $user_tips["match_{$row['match_id']}_away"] = $row['tip_away'];
}

// Variables for View
$savedData = $user_tips;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $savedData = array_merge($savedData, $_POST);
}

// === 3. Handle POST Logic ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // A. Fill Random
    if ($action === 'random') {
        foreach ($matchMap as $id => $m) {
            $savedData["match_{$id}_home"] = rand(0, 3);
            $savedData["match_{$id}_away"] = rand(0, 3);
        }
    }
    
    // B. Save & Proceed
    elseif ($action === 'save') {
        $allFilled = true;
        foreach ($matchMap as $id => $m) {
            $valH = $savedData["match_{$id}_home"] ?? '';
            $valA = $savedData["match_{$id}_away"] ?? '';
            if ($valH === '' || $valA === '') {
                $allFilled = false; break;
            }
        }

        if (!$allFilled) {
            $error = "⚠️ You must predict scores for ALL matches before proceeding.";
        } else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO tips (user_id, match_id, tip_home, tip_away) 
                                       VALUES (?, ?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE tip_home = VALUES(tip_home), tip_away = VALUES(tip_away)");

                foreach ($matchMap as $id => $m) {
                    $h = (int)$savedData["match_{$id}_home"];
                    $a = (int)$savedData["match_{$id}_away"];
                    $stmt->execute([$user_id, $id, $h, $a]);
                }
                $pdo->commit();
                
                // Calculate Standings for KO Phase
                $stats = []; 
                foreach ($matchMap as $id => $m) {
                    $g = $m['group_name'];
                    $t1 = $m['team1'];
                    $t2 = $m['team2'];
                    $h = (int)$savedData["match_{$id}_home"];
                    $a = (int)$savedData["match_{$id}_away"];
                    
                    if (!isset($stats[$g][$t1])) $stats[$g][$t1] = ['name'=>$t1, 'pts'=>0, 'gf'=>0, 'ga'=>0, 'gd'=>0];
                    if (!isset($stats[$g][$t2])) $stats[$g][$t2] = ['name'=>$t2, 'pts'=>0, 'gf'=>0, 'ga'=>0, 'gd'=>0];

                    $stats[$g][$t1]['gf'] += $h; $stats[$g][$t1]['ga'] += $a;
                    $stats[$g][$t2]['gf'] += $a; $stats[$g][$t2]['ga'] += $h;

                    if ($h > $a) $stats[$g][$t1]['pts'] += 3;
                    elseif ($a > $h) $stats[$g][$t2]['pts'] += 3;
                    else {
                        $stats[$g][$t1]['pts'] += 1;
                        $stats[$g][$t2]['pts'] += 1;
                    }
                }

                $group_winners = [];
                $group_runners = [];
                $third_place_candidates = [];

                foreach ($stats as $g => &$teams) {
                    foreach ($teams as &$t) $t['gd'] = $t['gf'] - $t['ga'];
                    
                    uasort($teams, function($a, $b) {
                        if ($b['pts'] != $a['pts']) return $b['pts'] - $a['pts'];
                        if ($b['gd'] != $a['gd']) return $b['gd'] - $a['gd'];
                        return $b['gf'] - $a['gf'];
                    });

                    $ranked = array_values($teams);
                    $group_winners[$g] = $ranked[0]['name'];
                    $group_runners[$g] = $ranked[1]['name'];
                    if (isset($ranked[2])) {
                        $ranked[2]['group'] = $g;
                        $third_place_candidates[] = $ranked[2];
                    }
                }

                uasort($third_place_candidates, function($a, $b) {
                    if ($b['pts'] != $a['pts']) return $b['pts'] - $a['pts'];
                    if ($b['gd'] != $a['gd']) return $b['gd'] - $a['gd'];
                    return $b['gf'] - $a['gf'];
                });
                
                $group_third = [];
                $top8 = array_slice($third_place_candidates, 0, 8);
                foreach ($top8 as $t) $group_third[$t['group']] = $t['name'];

                // === FIX: Set ALL Session Variables needed for KO Phase ===
                $_SESSION['group_predictions'] = $savedData; // This fixes the redirect loop
                $_SESSION['group_winners'] = $group_winners;
                $_SESSION['group_runners'] = $group_runners;
                $_SESSION['group_third'] = $group_third;
                unset($_SESSION['saved_post']); 

                header("Location: ko-phase.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Predictions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th, td { text-align: center; vertical-align: middle; }
    .score-input { max-width: 60px; margin: 0 auto; font-weight: bold; }
  </style>
</head>
<body>

<div class="container my-5">
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-center mb-0">Predictions</h1>
    <a href="home.php" class="btn btn-outline-secondary btn-sm">Home</a>
  </div>

  <?php if ($error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (empty($groups)): ?>
      <div class="alert alert-warning text-center">No matches found in Database.</div>
  <?php else: ?>

  <form method="post">
    <?php foreach ($groups as $gName => $matches): ?>
      <h2 class="mt-4">Group <?= htmlspecialchars($gName) ?></h2>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th class="text-end">Home Team</th>
              <th class="text-start">Away Team</th>
              <th style="width: 80px;">Home</th>
              <th style="width: 80px;">Away</th>
            </tr>
          </thead>
          <tbody>
            <?php
              foreach ($matches as $m) {
                $mid = $m['id'];
                $homeName = "match_{$mid}_home";
                $awayName = "match_{$mid}_away";
                $homeVal = $savedData[$homeName] ?? '';
                $awayVal = $savedData[$awayName] ?? '';
                
                // Format time: remove seconds
                $timeFormatted = substr($m['time'], 0, 5);

                echo "<tr>
                        <td>{$m['date']}</td>
                        <td><small class='text-muted'>{$timeFormatted}</small></td>
                        <td class='text-end fw-bold'>{$m['team1']}</td>
                        <td class='text-start fw-bold'>{$m['team2']}</td>
                        <td><input type='number' class='form-control text-center score-input' name='$homeName' min='0' max='9' value='".htmlspecialchars($homeVal)."'></td>
                        <td><input type='number' class='form-control text-center score-input' name='$awayName' min='0' max='9' value='".htmlspecialchars($awayVal)."'></td>
                      </tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-center gap-2 mt-4 mb-5">
        <button type="submit" name="action" value="random" class="btn btn-warning">Fill Random Scores</button>
        <button type="submit" name="action" value="save" class="btn btn-primary px-4">Save & Proceed</button>
    </div>
  </form>

  <?php endif; ?>
</div>

</body>
</html>