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
// Assuming IDs 1-72 are always Group Stage based on your schedule
$stmt = $pdo->query("SELECT * FROM matches WHERE id BETWEEN 1 AND 72 ORDER BY id ASC");
$matches_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize by Group
$groups = [];
$matchMap = [];

foreach ($matches_db as $m) {
    $g = $m['group_name']; // e.g. 'A'
    if (!isset($groups[$g])) {
        $groups[$g] = ['matches' => [], 'teams' => []];
    }
    
    $groups[$g]['matches'][] = $m;
    
    // Collect unique teams for standings
    if (!in_array($m['team1'], $groups[$g]['teams'])) $groups[$g]['teams'][] = $m['team1'];
    if (!in_array($m['team2'], $groups[$g]['teams'])) $groups[$g]['teams'][] = $m['team2'];

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

// Variables for View (Merge DB tips with POST data if any)
$savedData = $user_tips;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Overwrite with POST data so input doesn't reset on error/random
    foreach ($matchMap as $id => $m) {
        $hKey = "match_{$id}_home";
        $aKey = "match_{$id}_away";
        if (isset($_POST[$hKey])) $savedData[$hKey] = $_POST[$hKey];
        if (isset($_POST[$aKey])) $savedData[$aKey] = $_POST[$aKey];
    }
}

// === 3. Handle POST Logic ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    // A. Fill Random (PHP Logic)
    if ($action === 'random') {
        foreach ($matchMap as $id => $m) {
            $savedData["match_{$id}_home"] = rand(0, 3);
            $savedData["match_{$id}_away"] = rand(0, 3);
        }
    }
    
    // B. Save & Proceed
    elseif ($action === 'save') {
        // 1. Validate: Check if ALL matches have scores
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
            // 2. Save to Database
            $pdo->beginTransaction();
            try {
                $sql = "INSERT INTO tips (user_id, match_id, tip_home, tip_away) 
                        VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE tip_home = VALUES(tip_home), tip_away = VALUES(tip_away)";
                $stmt = $pdo->prepare($sql);

                foreach ($matchMap as $id => $m) {
                    $h = (int)$savedData["match_{$id}_home"];
                    $a = (int)$savedData["match_{$id}_away"];
                    $stmt->execute([$user_id, $id, $h, $a]);
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database Error: " . $e->getMessage();
            }

            if (!$error) {
                // 3. Calculate Standings (For KO Phase Logic)
                // We need to know who wins based on these tips to generate the KO bracket
                
                $stats = [];
                // Init Teams
                foreach ($groups as $gName => $gData) {
                    foreach ($gData['teams'] as $team) {
                        $stats[$gName][$team] = ['name' => $team, 'gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0];
                    }
                }

                // Process Scores
                foreach ($matchMap as $id => $m) {
                    $gName = $m['group_name'];
                    $t1 = $m['team1'];
                    $t2 = $m['team2'];
                    $h = (int)$savedData["match_{$id}_home"];
                    $a = (int)$savedData["match_{$id}_away"];

                    $stats[$gName][$t1]['gf'] += $h; $stats[$gName][$t1]['ga'] += $a;
                    $stats[$gName][$t2]['gf'] += $a; $stats[$gName][$t2]['ga'] += $h;

                    if ($h > $a) $stats[$gName][$t1]['pts'] += 3;
                    elseif ($a > $h) $stats[$gName][$t2]['pts'] += 3;
                    else {
                        $stats[$gName][$t1]['pts'] += 1;
                        $stats[$gName][$t2]['pts'] += 1;
                    }
                }

                // Sort & Extract
                $group_winners = [];
                $group_runners = [];
                $third_place_candidates = [];

                foreach ($stats as $gName => &$teams) {
                    foreach ($teams as &$t) {
                        $t['gd'] = $t['gf'] - $t['ga'];
                    }
                    unset($t);

                    uasort($teams, function($a, $b) {
                        if ($b['pts'] != $a['pts']) return $b['pts'] - $a['pts'];
                        if ($b['gd'] != $a['gd']) return $b['gd'] - $a['gd'];
                        return $b['gf'] - $a['gf'];
                    });

                    $sortedKeys = array_keys($teams);
                    $group_winners[$gName] = $sortedKeys[0];
                    $group_runners[$gName] = $sortedKeys[1];
                    
                    if (isset($sortedKeys[2])) {
                        $t3Name = $sortedKeys[2];
                        $t3Data = $teams[$t3Name];
                        $t3Data['group'] = $gName;
                        $third_place_candidates[] = $t3Data;
                    }
                }
                unset($teams);

                // Top 8 Third Place
                usort($third_place_candidates, function($a, $b) {
                    if ($b['pts'] != $a['pts']) return $b['pts'] - $a['pts'];
                    if ($b['gd'] != $a['gd']) return $b['gd'] - $a['gd'];
                    return $b['gf'] - $a['gf'];
                });
                
                $group_third = [];
                $top8 = array_slice($third_place_candidates, 0, 8);
                foreach ($top8 as $t) {
                    $group_third[$t['group']] = $t['name'];
                }

                // Save Calculated State to Session (Needed for KO logic)
                $_SESSION['group_winners'] = $group_winners;
                $_SESSION['group_runners'] = $group_runners;
                $_SESSION['group_third'] = $group_third;
                
                // Clear any temp KO data as groups changed
                unset($_SESSION['saved_post']);

                header("Location: ko-phase.php");
                exit;
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
    <?php foreach ($groups as $gName => $gData): ?>
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
              foreach ($gData['matches'] as $m) {
                $mid = $m['id'];
                $home = $m['team1'];
                $away = $m['team2'];
                $date = $m['date'];
                $time = $m['time'];

                $homeName = "match_{$mid}_home";
                $awayName = "match_{$mid}_away";

                $homeVal = $savedData[$homeName] ?? '';
                $awayVal = $savedData[$awayName] ?? '';

                echo "<tr>
                        <td>{$date}</td>
                        <td><small class='text-muted'>{$time}</small></td>
                        <td class='text-end fw-bold'>{$home}</td>
                        <td class='text-start fw-bold'>{$away}</td>
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