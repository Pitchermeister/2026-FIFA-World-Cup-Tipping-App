<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Groups Stage Sandbox (PHP)</title>
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
    "teams" => ["Mexico", "England", "Jordan", "Egypt"],
    "matches" => [
      ["home" => "Mexico", "away" => "England"],
      ["home" => "Jordan", "away" => "Egypt"],
      ["home" => "Mexico", "away" => "Jordan"],
      ["home" => "England", "away" => "Egypt"],
      ["home" => "Mexico", "away" => "Egypt"],
      ["home" => "England", "away" => "Jordan"]
    ]
  ],
  "B" => [
    "teams" => ["Canada", "Brazil", "Japan", "Ghana"],
    "matches" => [
      ["home" => "Canada", "away" => "Brazil"],
      ["home" => "Japan", "away" => "Ghana"],
      ["home" => "Canada", "away" => "Japan"],
      ["home" => "Brazil", "away" => "Ghana"],
      ["home" => "Canada", "away" => "Ghana"],
      ["home" => "Brazil", "away" => "Japan"]
    ]
    ],
  "C" => [
    "teams" => ["TeamC1", "TeamC2", "TeamC3", "TeamC4"],
    "matches" => [
      ["home" => "TeamC1", "away" => "TeamC2"],
      ["home" => "TeamC3", "away" => "TeamC4"],
      ["home" => "TeamC2", "away" => "TeamC4"],
      ["home" => "TeamC1", "away" => "TeamC3"],
      ["home" => "TeamC2", "away" => "TeamC3"],
      ["home" => "TeamC4", "away" => "TeamC1"]
    ]
    ],
  "D" => [
    "teams" => ["USA", "Iran", "Colombia", "New-Zealand"],
    "matches" => [
      ["home" => "USA", "away" => "Iran"],
      ["home" => "Colombia", "away" => "New-Zealand"],
      ["home" => "Iran", "away" => "Colombia"],
      ["home" => "USA", "away" => "New-Zealand"],
      ["home" => "New-Zealand", "away" => "Iran"],
      ["home" => "Colombia", "away" => "USA"]
    ]
    ],
    "E" => [
    "teams" => ["TeamE1", "TeamE2", "TeamE3", "TeamE4"],
    "matches" => [
      ["home" => "TeamE1", "away" => "TeamE2"],
      ["home" => "TeamE3", "away" => "TeamE4"],
      ["home" => "TeamE2", "away" => "TeamE4"],
      ["home" => "TeamE1", "away" => "TeamE3"],
      ["home" => "TeamE2", "away" => "TeamE3"],
      ["home" => "TeamE4", "away" => "TeamE1"]
    ]
    ],
    "F" => [
    "teams" => ["TeamF1", "TeamF2", "TeamF3", "TeamF4"],
    "matches" => [
      ["home" => "TeamF1", "away" => "TeamF2"],
      ["home" => "TeamF3", "away" => "TeamF4"],
      ["home" => "TeamF2", "away" => "TeamF4"],
      ["home" => "TeamF1", "away" => "TeamF3"],
      ["home" => "TeamF2", "away" => "TeamF3"],
      ["home" => "TeamF4", "away" => "TeamF1"]
    ]
    ],
    "G" => [
    "teams" => ["TeamG1", "TeamG2", "TeamG3", "TeamG4"],
    "matches" => [
      ["home" => "TeamG1", "away" => "TeamG2"],
      ["home" => "TeamG3", "away" => "TeamG4"],
      ["home" => "TeamG2", "away" => "TeamG4"],
      ["home" => "TeamG1", "away" => "TeamG3"],
      ["home" => "TeamG2", "away" => "TeamG3"],
      ["home" => "TeamG4", "away" => "TeamG1"]
    ]
    ],
    "H" => [
    "teams" => ["TeamH1", "TeamH2", "TeamH3", "TeamH4"],
    "matches" => [
      ["home" => "TeamH1", "away" => "TeamH2"],
      ["home" => "TeamH3", "away" => "TeamH4"],
      ["home" => "TeamH2", "away" => "TeamH4"],
      ["home" => "TeamH1", "away" => "TeamH3"],
      ["home" => "TeamH2", "away" => "TeamH3"],
      ["home" => "TeamH4", "away" => "TeamH1"]
    ]
    ],
    "I" => [
    "teams" => ["TeamI1", "TeamI2", "TeamI3", "TeamI4"],
    "matches" => [
      ["home" => "TeamI1", "away" => "TeamI2"],
      ["home" => "TeamI3", "away" => "TeamI4"],
      ["home" => "TeamI2", "away" => "TeamI4"],
      ["home" => "TeamI1", "away" => "TeamI3"],
      ["home" => "TeamI2", "away" => "TeamI3"],
      ["home" => "TeamI4", "away" => "TeamI1"]
    ]
    ],
    "J" => [
    "teams" => ["TeamJ1", "TeamJ2", "TeamJ3", "TeamJ4"],
    "matches" => [
      ["home" => "TeamJ1", "away" => "TeamJ2"],
      ["home" => "TeamJ3", "away" => "TeamJ4"],
      ["home" => "TeamJ2", "away" => "TeamJ4"],
      ["home" => "TeamJ1", "away" => "TeamJ3"],
      ["home" => "TeamJ2", "away" => "TeamJ3"],
      ["home" => "TeamJ4", "away" => "TeamJ1"]
    ]
    ],
    "K" => [
    "teams" => ["TeamK1", "TeamK2", "TeamK3", "TeamK4"],
    "matches" => [
      ["home" => "TeamK1", "away" => "TeamK2"],
      ["home" => "TeamK3", "away" => "TeamK4"],
      ["home" => "TeamK2", "away" => "TeamK4"],
      ["home" => "TeamK1", "away" => "TeamK3"],
      ["home" => "TeamK2", "away" => "TeamK3"],
      ["home" => "TeamK4", "away" => "TeamK1"]
    ]
    ],
    "L" => [
    "teams" => ["TeamL1", "TeamL2", "TeamL3", "TeamL4"],
    "matches" => [
      ["home" => "TeamL1", "away" => "TeamL2"],
      ["home" => "TeamL3", "away" => "TeamL4"],
      ["home" => "TeamL2", "away" => "TeamL4"],
      ["home" => "TeamL1", "away" => "TeamL3"],
      ["home" => "TeamL2", "away" => "TeamL3"],
      ["home" => "TeamL4", "away" => "TeamL1"]
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
