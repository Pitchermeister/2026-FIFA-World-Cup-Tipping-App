<?php
session_start();

// === 1. Load Groups and Teams from teams.txt ===
$groups = [];
$teamsFile = "teams.txt";

if (file_exists($teamsFile)) {
    $lines = file($teamsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode("|", $line);
        if (count($parts) >= 2) {
            $g = trim($parts[0]);
            $t = trim($parts[1]);
            
            // Initialize group if not exists
            if (!isset($groups[$g])) {
                $groups[$g] = [
                    "teams" => [],
                    "matches" => []
                ];
            }
            // Add team
            $groups[$g]["teams"][] = $t;
        }
    }
}

// === 2. Generate Matches Dynamically ===
// Pattern: 0v1, 2v3, 0v2, 1v3, 0v3, 1v2
foreach ($groups as $gName => &$gData) {
    $t = $gData['teams'];
    // Only generate matches if we have 4 teams
    if (count($t) >= 4) {
        $gData['matches'] = [
            ["home" => $t[0], "away" => $t[1]],
            ["home" => $t[2], "away" => $t[3]],
            ["home" => $t[0], "away" => $t[2]],
            ["home" => $t[1], "away" => $t[3]],
            ["home" => $t[0], "away" => $t[3]],
            ["home" => $t[1], "away" => $t[2]]
        ];
    }
}
unset($gData); // break reference

// === 3. Handle form submission ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Initialize data structures
    $standings_all = [];
    foreach ($groups as $gName => $gdata) {
        foreach ($gdata['teams'] as $team) {
            $standings_all[$gName][$team] = ['gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0];
        }
    }

    // Process all matches and calculate points
    foreach ($groups as $gName => $gdata) {
        foreach ($gdata['matches'] as $i => $m) {
            $homeKey = "{$gName}_home_$i";
            $awayKey = "{$gName}_away_$i";
            $homeScore = $_POST[$homeKey] ?? '';
            $awayScore = $_POST[$awayKey] ?? '';

            if ($homeScore !== '' && $awayScore !== '') {
                $h = (int)$homeScore;
                $a = (int)$awayScore;
                
                // Update stats
                $standings_all[$gName][$m['home']]['gf'] += $h;
                $standings_all[$gName][$m['home']]['ga'] += $a;
                $standings_all[$gName][$m['away']]['gf'] += $a;
                $standings_all[$gName][$m['away']]['ga'] += $h;

                if ($h > $a) {
                    $standings_all[$gName][$m['home']]['pts'] += 3;
                } elseif ($h < $a) {
                    $standings_all[$gName][$m['away']]['pts'] += 3;
                } else {
                    $standings_all[$gName][$m['home']]['pts'] += 1;
                    $standings_all[$gName][$m['away']]['pts'] += 1;
                }
            }
        }

        // Calculate GD
        foreach ($standings_all[$gName] as $team => $data) {
            $standings_all[$gName][$team]['gd'] = $data['gf'] - $data['ga'];
        }

        // Sort this group
        uasort($standings_all[$gName], function($a, $b) {
            if ($a['pts'] != $b['pts']) return $b['pts'] - $a['pts']; // Points desc
            if ($a['gd'] != $b['gd']) return $b['gd'] - $a['gd']; // GD desc
            return $b['gf'] - $a['gf']; // GF desc
        });
    }

    // Extract Winners
    $group_winners = [];
    $group_runners = [];
    $group_third = [];

    foreach ($standings_all as $gName => $st) {
        $teams = array_keys($st); // Get teams in sorted order
        $group_winners[$gName] = $teams[0];
        $group_runners[$gName] = $teams[1];
        $group_third[$gName] = $teams[2];
    }

    // Save to Session
    $_SESSION['group_predictions'] = $_POST;
    $_SESSION['group_winners'] = $group_winners;
    $_SESSION['group_runners'] = $group_runners;
    $_SESSION['group_third'] = $group_third;
    
    // Clear any previous KO saved data so we start fresh
    unset($_SESSION['saved_post']); 

    header("Location: ko-phase.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Predictions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th, td { text-align: center; vertical-align: middle; }
    .result-cell { font-weight: bold; color: #0d6efd; }
  </style>
</head>
<body>

<div class="container my-5">
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="text-center mb-0">Predictions</h1>
    <a href="home.php" class="btn btn-outline-secondary btn-sm">Home</a>
  </div>

  <?php if (empty($groups)): ?>
      <div class="alert alert-warning text-center">
        No teams found! Please ask the Admin to setup teams in the Admin Panel.
      </div>
  <?php else: ?>

  <form method="post">
    <?php 
    // If we have saved data in session (returning user), use it. Otherwise use POST (if any)
    $savedData = $_SESSION['group_predictions'] ?? [];
    
    foreach ($groups as $gName => $gdata): ?>
      <h2 class="mt-4">Group <?= htmlspecialchars($gName) ?></h2>
      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Date & Time</th>
              <th>Home Team</th>
              <th>Away Team</th>
              <th>Home Scored</th>
              <th>Away Scored</th>
              <th>Predicted Result</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $id = 0;
              // Ensure we have matches generated
              if (isset($gdata['matches']) && count($gdata['matches']) > 0) {
                  foreach ($gdata['matches'] as $m) {
                    $date = date("Y-m-d", strtotime("+$id days"));
                    $time = sprintf("%02d:%02d", rand(14,22), "00");

                    $homeName = "{$gName}_home_$id";
                    $awayName = "{$gName}_away_$id";

                    $homeVal = $savedData[$homeName] ?? ($_POST[$homeName] ?? '');
                    $awayVal = $savedData[$awayName] ?? ($_POST[$awayName] ?? '');
                    
                    $resultText = "–";
                    
                    if ($homeVal !== '' && $awayVal !== '') {
                      $h = (int)$homeVal; $a = (int)$awayVal;
                      if ($h > $a) $resultText = $m['home'];
                      elseif ($h < $a) $resultText = $m['away'];
                      else $resultText = "Draw";
                    }

                    echo "<tr>
                            <td>" . ($id + 1) . "</td>
                            <td>$date $time</td>
                            <td class='team-home'>{$m['home']}</td>
                            <td class='team-away'>{$m['away']}</td>
                            <td><input type='number' class='form-control' name='$homeName' min='0' max='9' value='".htmlspecialchars($homeVal)."'></td>
                            <td><input type='number' class='form-control' name='$awayName' min='0' max='9' value='".htmlspecialchars($awayVal)."'></td>
                            <td class='result-cell'>".htmlspecialchars($resultText)."</td>
                          </tr>";
                    $id++;
                  }
              } else {
                  echo "<tr><td colspan='7'>Not enough teams to generate matches.</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>

    <div class="text-center mt-4">
      <button type="button" id="fillRandom" class="btn btn-warning me-2">Fill Random Scores</button>
      <button type="submit" class="btn btn-primary px-4" disabled>Save & Proceed to KO Phase</button>
    </div>
  </form>

  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const scoreInputs = document.querySelectorAll('form input[type="number"]');
  const saveBtn = document.querySelector('form button[type="submit"]');
  const fillRandomBtn = document.getElementById('fillRandom');

  // Helper: check all score inputs to enable save button
  function checkAllFilled() {
    if(scoreInputs.length === 0) return;
    const allFilled = Array.from(scoreInputs).every(i => i.value !== '' && i.value !== null);
    if (saveBtn) saveBtn.disabled = !allFilled;
  }

  scoreInputs.forEach(input => {
    input.addEventListener('input', () => {
      checkAllFilled();
    });
  });

  if (fillRandomBtn) {
    fillRandomBtn.addEventListener('click', () => {
      scoreInputs.forEach(inp => {
        if (inp.value === '' || inp.value === null) {
          inp.value = Math.floor(Math.random() * 4);
          // Trigger input event to update valid state
          inp.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
      checkAllFilled();
    });
  }

  // Initial check
  checkAllFilled();
});
</script>
</body>
</html>