<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Group A Sandbox (PHP Version)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th, td { text-align: center; vertical-align: middle; }
  </style>
</head>
<body>

<div class="container my-5">
  <h1 class="text-center mb-4">Group A Sandbox</h1>

  <?php
  // Teams
  $teams_A = ["Mexico", "England", "Jordan", "Egypt"];

  // Matches (6 games total)
  $matches_A = [
      ["home" => "Mexico", "away" => "England"],
      ["home" => "Jordan", "away" => "Egypt"],
      ["home" => "Mexico", "away" => "Jordan"],
      ["home" => "England", "away" => "Egypt"],
      ["home" => "Mexico", "away" => "Egypt"],
      ["home" => "England", "away" => "Jordan"]
  ];

  // Initialize standings
  $standings_A = [];
  foreach ($teams_A as $team) {
      $standings_A[$team] = [
          "gf" => 0, // goals for
          "ga" => 0, // goals against
          "gd" => 0, // goal difference
          "pts" => 0 // points
      ];
  }

  $thirdplace = [];
  $groupName="A";

  // If form submitted -> process
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      foreach ($matches_A as $i => $m) {
          $homeScore = $_POST["home_$i"] ?? '';
          $awayScore = $_POST["away_$i"] ?? '';

          if ($homeScore !== '' && $awayScore !== '') {
              $home = (int)$homeScore;
              $away = (int)$awayScore;

              // Goals for / against
              $standings_A[$m["home"]]["gf"] += $home;
              $standings_A[$m["home"]]["ga"] += $away;
              $standings_A[$m["away"]]["gf"] += $away;
              $standings_A[$m["away"]]["ga"] += $home;

              // Points
              if ($home > $away) {
                  $standings_A[$m["home"]]["pts"] += 3;
              } elseif ($home < $away) {
                  $standings_A[$m["away"]]["pts"] += 3;
              } else {
                  $standings_A[$m["home"]]["pts"] += 1;
                  $standings_A[$m["away"]]["pts"] += 1;
              }
          }
      }

      // Calculate goal difference
      foreach ($standings_A as $team => $data) {
          $standings_A[$team]["gd"] = $data["gf"] - $data["ga"];
      }

      // Sort standings
      uasort($standings_A, function($a, $b) {
          if ($a["pts"] != $b["pts"]) return $b["pts"] - $a["pts"];
          if ($a["gd"] != $b["gd"]) return $b["gd"] - $a["gd"];
          return $b["gf"] - $a["gf"];
      });
  }
  ?>

  <!-- Predictions Table -->
  <form method="post">
    <div class="table-responsive">
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
          foreach ($matches_A as $m) {
              $date = date("Y-m-d", strtotime("+$id days"));
              $time = sprintf("%02d:%02d", rand(14, 22), "00");

              $homeVal = $_POST["home_$id"] ?? '';
              $awayVal = $_POST["away_$id"] ?? '';
              $resultText = "–";

              if ($homeVal !== '' && $awayVal !== '') {
                  $h = (int)$homeVal;
                  $a = (int)$awayVal;
                  if ($h > $a) $resultText = $m["home"];
                  elseif ($h < $a) $resultText = $m["away"];
                  else $resultText = "Draw";
              }

              echo "<tr>
                      <td>" . ($id + 1) . "</td>
                      <td>$date $time</td>
                      <td>{$m['home']}</td>
                      <td>{$m['away']}</td>
                      <td><input type='number' class='form-control' name='home_$id' min='0' max='9' value='$homeVal'></td>
                      <td><input type='number' class='form-control' name='away_$id' min='0' max='9' value='$awayVal'></td>
                      <td><strong>$resultText</strong></td>
                    </tr>";
              $id++;
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-primary px-4">Save & Show Standings</button>
    </div>
  </form>

  <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
  <!-- Standings Table -->
  <div id="standingsContainer" class="mt-5">
    <h2 class="text-center mb-3">Group A Standings</h2>
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
          <?php
          $pos = 1;
          foreach ($standings_A as $team => $data) {
              echo "<tr>
                      <td>$pos</td>
                      <td>$team</td>
                      <td>{$data['gf']}</td>
                      <td>{$data['ga']}</td>
                      <td>{$data['gd']}</td>
                      <td>{$data['pts']}</td>
                    </tr>";
                if($pos===3) {
                    array_push($thirdplace, [$team, $data['gf'],$data['ga'],$data['gd'],$data['pts'],$groupName]);
                }
              $pos++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<div class="container my-5">
  <h1 class="text-center mb-4">Group B Sandbox</h1>

  <?php
  $teams_B = ["Canada", "Brazil", "Japan", "Ghana"];
$matches_B = [
  ["home" => "Canada", "away" => "Brazil"],
  ["home" => "Japan", "away" => "Ghana"],
  ["home" => "Canada", "away" => "Japan"],
  ["home" => "Brazil", "away" => "Ghana"],
  ["home" => "Canada", "away" => "Ghana"],
  ["home" => "Brazil", "away" => "Japan"]
];

  // Initialize standings
  $standings_B = [];
  foreach ($teams_B as $team) {
      $standings_B[$team] = [
          "gf" => 0, // goals for
          "ga" => 0, // goals against
          "gd" => 0, // goal difference
          "pts" => 0 // points
      ];
  }

  $groupName="B";

  // If form submitted -> process
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
      foreach ($matches_B as $i => $m) {
          $homeScore = $_POST["home_$i"] ?? '';
          $awayScore = $_POST["away_$i"] ?? '';

          if ($homeScore !== '' && $awayScore !== '') {
              $home = (int)$homeScore;
              $away = (int)$awayScore;

              // Goals for / against
              $standings_B[$m["home"]]["gf"] += $home;
              $standings_B[$m["home"]]["ga"] += $away;
              $standings_B[$m["away"]]["gf"] += $away;
              $standings_B[$m["away"]]["ga"] += $home;

              // Points
              if ($home > $away) {
                  $standings_B[$m["home"]]["pts"] += 3;
              } elseif ($home < $away) {
                  $standings_B[$m["away"]]["pts"] += 3;
              } else {
                  $standings_B[$m["home"]]["pts"] += 1;
                  $standings_B[$m["away"]]["pts"] += 1;
              }
          }
      }

      // Calculate goal difference
      foreach ($standings_B as $team => $data) {
          $standings_B[$team]["gd"] = $data["gf"] - $data["ga"];
      }

      // Sort standings
      uasort($standings_B, function($a, $b) {
          if ($a["pts"] != $b["pts"]) return $b["pts"] - $a["pts"];
          if ($a["gd"] != $b["gd"]) return $b["gd"] - $a["gd"];
          return $b["gf"] - $a["gf"];
      });
  }
  ?>

  <!-- Predictions Table -->
  <form method="post">
    <div class="table-responsive">
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
          foreach ($matches_B as $m) {
              $date = date("Y-m-d", strtotime("+$id days"));
              $time = sprintf("%02d:%02d", rand(14, 22), "00");

              $homeVal = $_POST["home_$id"] ?? '';
              $awayVal = $_POST["away_$id"] ?? '';
              $resultText = "–";

              if ($homeVal !== '' && $awayVal !== '') {
                  $h = (int)$homeVal;
                  $a = (int)$awayVal;
                  if ($h > $a) $resultText = $m["home"];
                  elseif ($h < $a) $resultText = $m["away"];
                  else $resultText = "Draw";
              }

              echo "<tr>
                      <td>" . ($id + 1) . "</td>
                      <td>$date $time</td>
                      <td>{$m['home']}</td>
                      <td>{$m['away']}</td>
                      <td><input type='number' class='form-control' name='home_$id' min='0' max='9' value='$homeVal'></td>
                      <td><input type='number' class='form-control' name='away_$id' min='0' max='9' value='$awayVal'></td>
                      <td><strong>$resultText</strong></td>
                    </tr>";
              $id++;
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-primary px-4">Save & Show Standings</button>
    </div>
  </form>

  <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
  <!-- Standings Table -->
  <div id="standingsContainer" class="mt-5">
    <h2 class="text-center mb-3">Group B Standings</h2>
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
          <?php
          $pos = 1;
          foreach ($standings_B as $team => $data) {
              echo "<tr>
                      <td>$pos</td>
                      <td>$team</td>
                      <td>{$data['gf']}</td>
                      <td>{$data['ga']}</td>
                      <td>{$data['gd']}</td>
                      <td>{$data['pts']}</td>
                    </tr>";
                if($pos===3) {
                    array_push($thirdplace, [$team, $data['gf'],$data['ga'],$data['gd'],$data['pts'],$groupName]);
                }
              $pos++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

    <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
  <!-- Third Place Table -->
  <div id="thirdsContainer" class="mt-5">
    <h2 class="text-center mb-3">Third Place Standings</h2>
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
            <th>Group</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $pos = 1;
          foreach ($thirdplace as $third) {
              echo "<tr>
                      <td>$third[0]</td>
                      <td>$third[1]</td>
                      <td>$third[2]</td>
                      <td>$third[3]</td>
                      <td>$third[4]</td>
                      <td>$third[5]</td>
                      <td>$third[6]</td>
                      <td>{
                    </tr>";
              $pos++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
</div>

</body>
</html>
