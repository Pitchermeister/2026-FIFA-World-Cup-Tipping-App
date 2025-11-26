<?php
session_start();

// === Define groups and matches ===
$groups = [
  "A" => [
    "teams" => ["Mexico", "Croatia", "Jordan", "Egypt"],
    "matches" => [
      ["home" => "Mexico", "away" => "Croatia"],
      ["home" => "Jordan", "away" => "Egypt"],
      ["home" => "Mexico", "away" => "Jordan"],
      ["home" => "Croatia", "away" => "Egypt"],
      ["home" => "Mexico", "away" => "Egypt"],
      ["home" => "Croatia", "away" => "Jordan"]
    ]
  ],
  "B" => [
    "teams" => ["Canada", "Morocco", "Austria", "Ghana"],
    "matches" => [
      ["home" => "Canada", "away" => "Morocco"],
      ["home" => "Austria", "away" => "Ghana"],
      ["home" => "Canada", "away" => "Austria"],
      ["home" => "Morocco", "away" => "Ghana"],
      ["home" => "Canada", "away" => "Ghana"],
      ["home" => "Morocco", "away" => "Austria"]
    ]
    ],
  "C" => [
    "teams" => ["Spain", "ITA/NIR/WAL/BIH", "TUR/ROU/SVK/KOS", "Uzbekistan"],
    "matches" => [
      ["home" => "Spain", "away" => "ITA/NIR/WAL/BIH"],
      ["home" => "TUR/ROU/SVK/KOS", "away" => "Uzbekistan"],
      ["home" => "ITA/NIR/WAL/BIH", "away" => "Uzbekistan"],
      ["home" => "Spain", "away" => "TUR/ROU/SVK/KOS"],
      ["home" => "ITA/NIR/WAL/BIH", "away" => "TUR/ROU/SVK/KOS"],
      ["home" => "Uzbekistan", "away" => "Spain"]
    ]
    ],
  "D" => [
    "teams" => ["USA", "Algeria", "Colombia", "New-Zealand"],
    "matches" => [
      ["home" => "USA", "away" => "Algeria"],
      ["home" => "Colombia", "away" => "New-Zealand"],
      ["home" => "Algeria", "away" => "Colombia"],
      ["home" => "USA", "away" => "New-Zealand"],
      ["home" => "New-Zealand", "away" => "Algeria"],
      ["home" => "Colombia", "away" => "USA"]
    ]
    ],
    "E" => [
    "teams" => ["Argentina", "Uruguay", "Australia", "Qatar"],
    "matches" => [
      ["home" => "Argentina", "away" => "Uruguay"],
      ["home" => "Australia", "away" => "Qatar"],
      ["home" => "Uruguay", "away" => "Qatar"],
      ["home" => "Argentina", "away" => "Australia"],
      ["home" => "Uruguay", "away" => "Australia"],
      ["home" => "Qatar", "away" => "Argentina"]
    ]
    ],
    "F" => [
    "teams" => ["France", "Switzerland", "UKR/SWE/POL/ALB", "COD/JAM/NCL"],
    "matches" => [
      ["home" => "France", "away" => "Switzerland"],
      ["home" => "UKR/SWE/POL/ALB", "away" => "COD/JAM/NCL"],
      ["home" => "Switzerland", "away" => "COD/JAM/NCL"],
      ["home" => "France", "away" => "UKR/SWE/POL/ALB"],
      ["home" => "Switzerland", "away" => "UKR/SWE/POL/ALB"],
      ["home" => "COD/JAM/NCL", "away" => "France"]
    ]
    ],
    "G" => [
    "teams" => ["England", "Japan", "Norway", "IRQ/BOL/SUR"],
    "matches" => [
      ["home" => "England", "away" => "Japan"],
      ["home" => "Norway", "away" => "IRQ/BOL/SUR"],
      ["home" => "Japan", "away" => "IRQ/BOL/SUR"],
      ["home" => "England", "away" => "Norway"],
      ["home" => "Japan", "away" => "Norway"],
      ["home" => "IRQ/BOL/SUR", "away" => "England"]
    ]
    ],
    "H" => [
    "teams" => ["Brazil", "Senegal", "Panama", "Saudi Arabia"],
    "matches" => [
      ["home" => "Brazil", "away" => "Senegal"],
      ["home" => "Panama", "away" => "Saudi Arabia"],
      ["home" => "Senegal", "away" => "Saudi Arabia"],
      ["home" => "Brazil", "away" => "Panama"],
      ["home" => "Senegal", "away" => "Panama"],
      ["home" => "Saudi Arabia", "away" => "Brazil"]
    ]
    ],
    "I" => [
    "teams" => ["Portugal", "Iran", "Scotland", "South Africa"],
    "matches" => [
      ["home" => "Portugal", "away" => "Iran"],
      ["home" => "Scotland", "away" => "South Africa"],
      ["home" => "Iran", "away" => "South Africa"],
      ["home" => "Portugal", "away" => "Scotland"],
      ["home" => "Iran", "away" => "Scotland"],
      ["home" => "South Africa", "away" => "Portugal"]
    ]
    ],
    "J" => [
    "teams" => ["Netherlands", "DEN/MKD/CZE/IRL", "Paraguay", "Cabo Verde"],
    "matches" => [
      ["home" => "Netherlands", "away" => "DEN/MKD/CZE/IRL"],
      ["home" => "Paraguay", "away" => "Cabo Verde"],
      ["home" => "DEN/MKD/CZE/IRL", "away" => "Cabo Verde"],
      ["home" => "Netherlands", "away" => "Paraguay"],
      ["home" => "DEN/MKD/CZE/IRL", "away" => "Paraguay"],
      ["home" => "Cabo Verde", "away" => "Netherlands"]
    ]
    ],
    "K" => [
    "teams" => ["Belgium", "South Korea", "Tunisia", "Curaçao"],
    "matches" => [
      ["home" => "Belgium", "away" => "South Korea"],
      ["home" => "Tunisia", "away" => "Curaçao"],
      ["home" => "South Korea", "away" => "Curaçao"],
      ["home" => "Belgium", "away" => "Tunisia"],
      ["home" => "South Korea", "away" => "Tunisia"],
      ["home" => "Curaçao", "away" => "Belgium"]
    ]
    ],
    "L" => [
    "teams" => ["Germany", "Ecuador", "Côte d'Ivoire", "Haiti"],
    "matches" => [
      ["home" => "Germany", "away" => "Ecuador"],
      ["home" => "Côte d'Ivoire", "away" => "Haiti"],
      ["home" => "Ecuador", "away" => "Haiti"],
      ["home" => "Germany", "away" => "Côte d'Ivoire"],
      ["home" => "Ecuador", "away" => "Côte d'Ivoire"],
      ["home" => "Haiti", "away" => "Germany"]
    ]
    ]
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Initialize data structures
    $standings_all = [];
    foreach ($groups as $gName => $gdata) {
        foreach ($gdata['teams'] as $team) {
            $standings_all[$gName][$team] = ['gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0];
        }
    }

    // 2. Process all matches and calculate points
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

    // 3. Extract Winners (THIS MUST BE OUTSIDE THE LOOP ABOVE)
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
  <h1 class="text-center mb-4">Predictions</h1>

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
              foreach ($gdata['matches'] as $m) {
                $date = date("Y-m-d", strtotime("+$id days"));
                $time = sprintf("%02d:%02d", rand(14,22), "00");

                $homeName = "{$gName}_home_$id";
                $awayName = "{$gName}_away_$id";

                // Retrieve value from Saved Session data (or POST if submitting failed)
                $homeVal = $savedData[$homeName] ?? ($_POST[$homeName] ?? '');
                $awayVal = $savedData[$awayName] ?? ($_POST[$awayName] ?? '');
                
                $resultText = "–";
                
                // Pure PHP Rendering for result column
                // This will only show if data exists (e.g. after coming back from KO phase)
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
            ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>

    <div class="text-center mt-4">
      <button type="button" id="fillRandom" class="btn btn-warning me-2">Fill Random Scores</button>
      <button type="submit" class="btn btn-primary px-4" disabled>Save & Show Standings</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const scoreInputs = document.querySelectorAll('form input[type="number"]');
  const saveBtn = document.querySelector('form button[type="submit"]');
  const fillRandomBtn = document.getElementById('fillRandom');

  // Helper: check all score inputs to enable save button
  function checkAllFilled() {
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