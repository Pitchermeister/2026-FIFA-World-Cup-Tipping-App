<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Groups Stage Sandbox</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th, td { text-align: center; vertical-align: middle; }
  </style>
</head>
<body>

<div class="container my-5">
  <h1 class="text-center mb-4">Groups Stage Sandbox</h1>

<?php
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

// Prepare empty standings structure for each group
$standings_all = [];
foreach ($groups as $gName => $gdata) {
  foreach ($gdata['teams'] as $team) {
    $standings_all[$gName][$team] = ['gf' => 0, 'ga' => 0, 'gd' => 0, 'pts' => 0];
  }
}

// This will hold third-place entries across groups
$thirdplace_all = [];

// ---- Process form submission for all groups ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($groups as $gName => $gdata) {
    $matches = $gdata['matches'];

    foreach ($matches as $i => $m) {
      // input names include group prefix to avoid collision
      $homeKey = "{$gName}_home_$i";
      $awayKey = "{$gName}_away_$i";

      $homeScore = $_POST[$homeKey] ?? '';
      $awayScore = $_POST[$awayKey] ?? '';

      if ($homeScore !== '' && $awayScore !== '') {
        $h = (int)$homeScore;
        $a = (int)$awayScore;

        // Update goals for/against
        $standings_all[$gName][$m['home']]['gf'] += $h;
        $standings_all[$gName][$m['home']]['ga'] += $a;
        $standings_all[$gName][$m['away']]['gf'] += $a;
        $standings_all[$gName][$m['away']]['ga'] += $h;

        // Update points
        if ($h > $a) {
          $standings_all[$gName][$m['home']]['pts'] += 3;
        } elseif ($h < $a) {
          $standings_all[$gName][$m['away']]['pts'] += 3;
        } else {
          $standings_all[$gName][$m['home']]['pts'] += 1;
          $standings_all[$gName][$m['away']]['pts'] += 1;
        }
      }
    } // end matches loop

    // calculate gd
    foreach ($standings_all[$gName] as $team => $data) {
      $standings_all[$gName][$team]['gd'] = $data['gf'] - $data['ga'];
    }

    // sort standings - points, then GD, then GF
    uasort($standings_all[$gName], function($a, $b) {
      if ($a['pts'] != $b['pts']) return $b['pts'] - $a['pts'];
      if ($a['gd'] != $b['gd']) return $b['gd'] - $a['gd'];
      return $b['gf'] - $a['gf'];
    });

    // extract 3rd place for this group (after sort)
    $pos = 1;
    foreach ($standings_all[$gName] as $team => $data) {
      if ($pos === 3) {
        $thirdplace_all[] = [
          'group' => $gName,
          'team'  => $team,
          'gf'    => $data['gf'],
          'ga'    => $data['ga'],
          'gd'    => $data['gd'],
          'pts'   => $data['pts']
        ];
        break; // found 3rd, no need to continue
      }
      $pos++;
    }
    // Determine 1st, 2nd, 3rd per group
$group_winners = [];
$group_runners = [];
$group_third = [];

foreach ($standings_all as $gName => $st) {
    $teams = array_keys($st);
    $group_winners[$gName] = $teams[0]; // 1st place
    $group_runners[$gName] = $teams[1]; // 2nd place
    $group_third[$gName] = $teams[2];   // 3rd place
}
$_SESSION['group_winners'] = $group_winners;
$_SESSION['group_runners'] = $group_runners;
$_SESSION['group_third'] = $group_third;
  } // end groups loop
} // end POST
?>

  <!-- SINGLE FORM for both groups -->
  <form method="post">
    <?php foreach ($groups as $gName => $gdata): ?>
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

                // use group-prefixed input names
                $homeName = "{$gName}_home_$id";
                $awayName = "{$gName}_away_$id";

                $homeVal = $_POST[$homeName] ?? '';
                $awayVal = $_POST[$awayName] ?? '';
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
                        <td>{$m['home']}</td>
                        <td>{$m['away']}</td>
                        <td><input type='number' class='form-control' name='$homeName' min='0' max='9' value='".htmlspecialchars($homeVal)."'></td>
                        <td><input type='number' class='form-control' name='$awayName' min='0' max='9' value='".htmlspecialchars($awayVal)."'></td>
                        <td><strong>".htmlspecialchars($resultText)."</strong></td>
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

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>

  <!-- Render each group's standings -->
  <?php foreach ($standings_all as $gName => $st): ?>
    <div class="mt-5">
      <h3 class="text-center">Group <?= htmlspecialchars($gName) ?> Standings</h3>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Pos</th>
              <th>Country</th>
              <th>Goals Scored</th>
              <th>Goals Conceded</th>
              <th>Goal Diff</th>
              <th>Points</th>
            </tr>
          </thead>
          <tbody>
            <?php $pos = 1; foreach ($st as $team => $data): ?>
              <tr>
                <td><?= $pos ?></td>
                <td><?= htmlspecialchars($team) ?></td>
                <td><?= $data['gf'] ?></td>
                <td><?= $data['ga'] ?></td>
                <td><?= $data['gd'] ?></td>
                <td><?= $data['pts'] ?></td>
              </tr>
            <?php $pos++; endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; 


  usort($thirdplace_all, function($a, $b) {
  if ($a['pts'] != $b['pts']) return $b['pts'] - $a['pts']; // points
  if ($a['gd'] != $b['gd']) return $b['gd'] - $a['gd']; // GD
  return $b['gf'] - $a['gf']; // goals scored
});
?>

  <!-- Combined Third-place Table -->
  <div class="mt-5">
    <h3 class="text-center">Third Place Teams (All Groups)</h3>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Country</th>
            <th>Goals Scored</th>
            <th>Goals Conceded</th>
            <th>Goal Diff</th>
            <th>Points</th>
            <th>Group</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $i = 1;
            foreach ($thirdplace_all as $entry) {
              echo "<tr>
                      <td>$i</td>
                      <td>".htmlspecialchars($entry['team'])."</td>
                      <td>{$entry['gf']}</td>
                      <td>{$entry['ga']}</td>
                      <td>{$entry['gd']}</td>
                      <td>{$entry['pts']}</td>
                      <td>".htmlspecialchars($entry['group'])."</td>
                    </tr>";
              $i++;
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>

<?php endif; ?>

</div> <!-- container -->

<script>
document.addEventListener('DOMContentLoaded', function () {
  // select all number inputs inside the main form
  const scoreInputs = document.querySelectorAll('form input[type="number"]');
  const saveBtn = document.querySelector('form button[type="submit"]');
  const fillRandomBtn = document.getElementById('fillRandom'); // add this button in HTML

  // helper: update predicted result for a single row
  function updateRowResult(input) {
    const row = input.closest('tr');
    if (!row) return;
    const homeInput = row.querySelector('input[type="number"][name*="_home_"]');
    const awayInput = row.querySelector('input[type="number"][name*="_away_"]');
    const resultCell = row.querySelector('td:last-child');

    const homeVal = homeInput && homeInput.value !== '' ? parseInt(homeInput.value, 10) : null;
    const awayVal = awayInput && awayInput.value !== '' ? parseInt(awayInput.value, 10) : null;

    if (homeVal !== null && awayVal !== null) {
      const homeTeam = row.cells[2].innerText.trim();
      const awayTeam = row.cells[3].innerText.trim();
      if (homeVal > awayVal) resultCell.innerHTML = "<strong>"+homeTeam+"</strong>";
      else if (homeVal < awayVal) resultCell.innerHTML = "<strong>"+awayTeam+"</strong>";
      else resultCell.innerHTML = "<strong>Draw</strong>";
    } else {
      resultCell.textContent = '–';
    }
  }

  // helper: check all score inputs — enable Save when all filled
  function checkAllFilled() {
    const allFilled = Array.from(scoreInputs).every(i => i.value !== '' && i.value !== null);
    if (saveBtn) saveBtn.disabled = !allFilled;
  }

  // attach listener to each score input
  scoreInputs.forEach(input => {
    input.addEventListener('input', (e) => {
      updateRowResult(e.target);
      checkAllFilled();
    });
  });

  // Fill random scores (0..3) for empty inputs and run update
  if (fillRandomBtn) {
    fillRandomBtn.addEventListener('click', () => {
      scoreInputs.forEach(inp => {
        if (inp.value === '' || inp.value === null) {
          inp.value = Math.floor(Math.random() * 4); // 0..3
          // manually trigger input event so result updates
          inp.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
      checkAllFilled();
    });
  }

  // initial run on page load (populated values from POST)
  scoreInputs.forEach(i => updateRowResult(i));
  checkAllFilled();
});
</script>


</body>
</html>
