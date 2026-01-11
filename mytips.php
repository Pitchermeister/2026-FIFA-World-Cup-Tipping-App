<?php
session_start();
require 'db.php';

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION["user_id"];

// === 1. LOAD DATA FROM DB ===

// A. Load Matches & Actual Results
$matches = [];
$stmt = $pdo->query("SELECT * FROM matches ORDER BY id ASC");
while ($row = $stmt->fetch()) {
    $matches[$row['id']] = $row;
}

// B. Load User Tips
$user_tips = [];
$stmt = $pdo->prepare("SELECT * FROM tips WHERE user_id = ?");
$stmt->execute([$user_id]);
while ($row = $stmt->fetch()) {
    $user_tips[$row['match_id']] = $row;
}

// Check if user has any tips
if (empty($user_tips)) {
    echo "<div class='container my-5 text-center'>
            <div class='alert alert-info'>You haven't made any predictions yet.</div>
            <a href='predictions.php' class='btn btn-primary'>Go to Predictions</a>
          </div>";
    exit;
}

// Points Map for KO
$ko_points_map = [
    'Round of 32' => 2,
    'Round of 16' => 4,
    'Quarter Finals' => 8,
    'Semi Finals' => 16,
    'Third Place' => 24,
    'Final' => 32
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Tips</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f0f2f5; }
    .phase-header { background-color: #212529; color: #fff; padding: 10px; margin-top: 30px; border-radius: 5px; }
    .table th { background-color: #e9ecef; font-size: 0.9rem; text-align: center; vertical-align: middle; }
    .table td { vertical-align: middle; text-align: center; }
    .tip-score { font-weight: bold; color: #0d6efd; }
    .actual-res { font-weight: bold; color: #198754; }
    .cb-cell { width: 40px; }
    input[type=checkbox] { transform: scale(1.2); pointer-events: none; }
    .points-cell { font-weight: bold; font-size: 1.1rem; }
    .match-meta { display: block; font-size: 0.75rem; color: #6c757d; }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>My Predictions</h1>
    <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
  </div>

  <!-- GROUP PHASE -->
  <h3 class="phase-header">Group Phase (Matches 1-72)</h3>
  <div class="table-responsive shadow-sm bg-white rounded">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Group</th>
          <th>Match</th>
          <th>Tip (Score)</th>
          <th>Actual Result</th>
          <th title="Correct Score (1pt)">Exact<br>Score</th>
          <th title="Correct Goal Diff (1pt)">Correct<br>GD</th>
          <th title="Correct Winner (1pt)">Correct<br>Winner</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $totalGroupPoints = 0;
        
        for ($id = 1; $id <= 72; $id++) {
            if (!isset($matches[$id])) continue;
            $m = $matches[$id];
            
            // Format time clean (HH:MM)
            $timeFormatted = substr($m['time'], 0, 5);
            
            // User Tip
            $t = $user_tips[$id] ?? null;
            $uh = $t['tip_home'] ?? null;
            $ua = $t['tip_away'] ?? null;
            $hasTip = ($uh !== null && $ua !== null);

            // Actual Result
            $rh = $m['score_home'];
            $ra = $m['score_away'];
            $hasResult = ($rh !== null && $ra !== null);

            // Points Logic
            $p_score = 0; $p_gd = 0; $p_winner = 0;

            if ($hasTip && $hasResult) {
                // 1. Exact Score
                if ($uh == $rh && $ua == $ra) $p_score = 1;
                // 2. Goal Diff
                if (($uh - $ua) == ($rh - $ra)) $p_gd = 1;
                // 3. Winner
                if (($uh <=> $ua) == ($rh <=> $ra)) $p_winner = 1;
            }

            $rowPoints = $p_score + $p_gd + $p_winner;
            $totalGroupPoints += $rowPoints;

            // Display
            $tipStr = $hasTip ? "$uh : $ua" : "-";
            
            $actualStr = "-";
            if ($hasResult) {
                $wText = "Draw";
                if ($rh > $ra) $wText = $m['team1'];
                if ($ra > $rh) $wText = $m['team2'];
                $actualStr = "$rh : $ra <br><small class='text-muted'>$wText</small>";
            }

            $cbScore = $p_score ? "checked" : "";
            $cbGD    = $p_gd ? "checked" : "";
            $cbWin   = $p_winner ? "checked" : "";
            $rowClass = ($rowPoints === 3) ? "table-success" : "";

            echo "<tr class='$rowClass'>
                    <td class='fw-bold'>$id</td>
                    <td>{$m['date']}<br>{$timeFormatted}</td>
                    <td><span class='badge bg-light text-dark border'>{$m['group_name']}</span></td>
                    <td>{$m['team1']} <span class='text-muted'>vs</span> {$m['team2']}</td>
                    <td class='tip-score'>{$tipStr}</td>
                    <td class='actual-res'>{$actualStr}</td>
                    <td class='cb-cell'><input type='checkbox' $cbScore></td>
                    <td class='cb-cell'><input type='checkbox' $cbGD></td>
                    <td class='cb-cell'><input type='checkbox' $cbWin></td>
                    <td class='points-cell'>{$rowPoints}</td>
                   </tr>";
        }
        ?>
        <tr class="table-dark">
            <td colspan="9" class="text-end fw-bold">Total Group Points:</td>
            <td class="fw-bold"><?= $totalGroupPoints ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- KO PHASE -->
  <h3 class="phase-header">Knockout Phase (Matches 73-104)</h3>
  <div class="table-responsive shadow-sm bg-white rounded">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Stage</th>
          <th>Match</th>
          <th>Your Tip</th>
          <th>Actual Winner</th>
          <th>Correct Tip?</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $totalKoPoints = 0;

        for ($id = 73; $id <= 104; $id++) {
            if (!isset($matches[$id])) continue;
            $m = $matches[$id];
            
            // Format time clean
            $timeFormatted = substr($m['time'], 0, 5);

            // Use DB values directly (no resolving)
            $t1 = $m['team1'];
            $t2 = $m['team2'];

            // Tip & Result
            $myTip = $user_tips[$id]['tip_winner'] ?? "-";
            $realWinner = $m['winner_ko'] ?? "-";

            // Points
            $points = 0;
            $isCorrect = false;
            
            if ($myTip !== "-" && $realWinner !== "-" && $realWinner !== null) {
                // Compare strings
                if (strcasecmp(trim($myTip), trim($realWinner)) === 0) {
                    $isCorrect = true;
                    $points = $ko_points_map[trim($m['group_name'])] ?? 0;
                }
            }

            $totalKoPoints += $points;
            $cbCorrect = $isCorrect ? "checked" : "";
            $rowClass = $isCorrect ? "table-success" : "";

            echo "<tr class='$rowClass'>
                    <td class='fw-bold'>$id</td>
                    <td>{$m['date']}<br>{$timeFormatted}</td>
                    <td><span class='badge bg-info text-dark'>{$m['group_name']}</span></td>
                    <td>{$t1} <span class='text-muted'>vs</span> {$t2}</td>
                    <td class='tip-score'>{$myTip}</td>
                    <td class='actual-res'>{$realWinner}</td>
                    <td class='cb-cell'><input type='checkbox' $cbCorrect></td>
                    <td class='points-cell'>{$points}</td>
                  </tr>";
        }
        ?>
        <tr class="table-dark">
            <td colspan="7" class="text-end fw-bold">Total KO Points:</td>
            <td class="fw-bold"><?= $totalKoPoints ?></td>
        </tr>
        <tr class="bg-warning">
            <td colspan="7" class="text-end fw-bold h5">GRAND TOTAL:</td>
            <td class="fw-bold h5"><?= ($totalGroupPoints + $totalKoPoints) ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  
  <div class="text-center mt-5 mb-5">
    <p class="text-muted">Good luck with your predictions!</p>
  </div>

</div>

</body>
</html>