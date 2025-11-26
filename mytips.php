<?php
session_start();

// Redirect if no predictions found
if (!isset($_SESSION['group_predictions'])) {
    echo "<div style='text-align:center; margin-top:50px;'>No predictions found. <a href='predictions.php'>Go to Predictions</a></div>";
    exit;
}

// === 1. RETRIEVE DATA ===
$group_preds = $_SESSION['group_predictions'];
$ko_preds    = $_SESSION['saved_post'] ?? [];

$group_winners = $_SESSION['group_winners'] ?? [];
$group_runners = $_SESSION['group_runners'] ?? [];
$group_third   = $_SESSION['group_third']   ?? [];

// Helper to get specific team inputs
function getGroupScore($g, $id, $type, $data) {
    return $data["{$g}_{$type}_{$id}"] ?? '';
}

// === 2. DEFINE STRUCTURES (Must match previous files) ===

// Groups Data
$groups = [
  "A" => [ "teams" => ["Mexico", "Croatia", "Jordan", "Egypt"],
           "matches" => [ ["home" => "Mexico", "away" => "Croatia"], ["home" => "Jordan", "away" => "Egypt"], ["home" => "Mexico", "away" => "Jordan"], ["home" => "Croatia", "away" => "Egypt"], ["home" => "Mexico", "away" => "Egypt"], ["home" => "Croatia", "away" => "Jordan"] ] ],
  "B" => [ "teams" => ["Canada", "Morocco", "Austria", "Ghana"],
           "matches" => [ ["home" => "Canada", "away" => "Morocco"], ["home" => "Austria", "away" => "Ghana"], ["home" => "Canada", "away" => "Austria"], ["home" => "Morocco", "away" => "Ghana"], ["home" => "Canada", "away" => "Ghana"], ["home" => "Morocco", "away" => "Austria"] ] ],
  "C" => [ "teams" => ["Spain", "ITA/NIR/WAL/BIH", "TUR/ROU/SVK/KOS", "Uzbekistan"],
           "matches" => [ ["home" => "Spain", "away" => "ITA/NIR/WAL/BIH"], ["home" => "TUR/ROU/SVK/KOS", "away" => "Uzbekistan"], ["home" => "ITA/NIR/WAL/BIH", "away" => "Uzbekistan"], ["home" => "Spain", "away" => "TUR/ROU/SVK/KOS"], ["home" => "ITA/NIR/WAL/BIH", "away" => "TUR/ROU/SVK/KOS"], ["home" => "Uzbekistan", "away" => "Spain"] ] ],
  "D" => [ "teams" => ["USA", "Algeria", "Colombia", "New-Zealand"],
           "matches" => [ ["home" => "USA", "away" => "Algeria"], ["home" => "Colombia", "away" => "New-Zealand"], ["home" => "Algeria", "away" => "Colombia"], ["home" => "USA", "away" => "New-Zealand"], ["home" => "New-Zealand", "away" => "Algeria"], ["home" => "Colombia", "away" => "USA"] ] ],
  "E" => [ "teams" => ["Argentina", "Uruguay", "Australia", "Qatar"],
           "matches" => [ ["home" => "Argentina", "away" => "Uruguay"], ["home" => "Australia", "away" => "Qatar"], ["home" => "Uruguay", "away" => "Qatar"], ["home" => "Argentina", "away" => "Australia"], ["home" => "Uruguay", "away" => "Australia"], ["home" => "Qatar", "away" => "Argentina"] ] ],
  "F" => [ "teams" => ["France", "Switzerland", "UKR/SWE/POL/ALB", "COD/JAM/NCL"],
           "matches" => [ ["home" => "France", "away" => "Switzerland"], ["home" => "UKR/SWE/POL/ALB", "away" => "COD/JAM/NCL"], ["home" => "Switzerland", "away" => "COD/JAM/NCL"], ["home" => "France", "away" => "UKR/SWE/POL/ALB"], ["home" => "Switzerland", "away" => "UKR/SWE/POL/ALB"], ["home" => "COD/JAM/NCL", "away" => "France"] ] ],
  "G" => [ "teams" => ["England", "Japan", "Norway", "IRQ/BOL/SUR"],
           "matches" => [ ["home" => "England", "away" => "Japan"], ["home" => "Norway", "away" => "IRQ/BOL/SUR"], ["home" => "Japan", "away" => "IRQ/BOL/SUR"], ["home" => "England", "away" => "Norway"], ["home" => "Japan", "away" => "Norway"], ["home" => "IRQ/BOL/SUR", "away" => "England"] ] ],
  "H" => [ "teams" => ["Brazil", "Senegal", "Panama", "Saudi Arabia"],
           "matches" => [ ["home" => "Brazil", "away" => "Senegal"], ["home" => "Panama", "away" => "Saudi Arabia"], ["home" => "Senegal", "away" => "Saudi Arabia"], ["home" => "Brazil", "away" => "Panama"], ["home" => "Senegal", "away" => "Panama"], ["home" => "Saudi Arabia", "away" => "Brazil"] ] ],
  "I" => [ "teams" => ["Portugal", "Iran", "Scotland", "South Africa"],
           "matches" => [ ["home" => "Portugal", "away" => "Iran"], ["home" => "Scotland", "away" => "South Africa"], ["home" => "Iran", "away" => "South Africa"], ["home" => "Portugal", "away" => "Scotland"], ["home" => "Iran", "away" => "Scotland"], ["home" => "South Africa", "away" => "Portugal"] ] ],
  "J" => [ "teams" => ["Netherlands", "DEN/MKD/CZE/IRL", "Paraguay", "Cabo Verde"],
           "matches" => [ ["home" => "Netherlands", "away" => "DEN/MKD/CZE/IRL"], ["home" => "Paraguay", "away" => "Cabo Verde"], ["home" => "DEN/MKD/CZE/IRL", "away" => "Cabo Verde"], ["home" => "Netherlands", "away" => "Paraguay"], ["home" => "DEN/MKD/CZE/IRL", "away" => "Paraguay"], ["home" => "Cabo Verde", "away" => "Netherlands"] ] ],
  "K" => [ "teams" => ["Belgium", "South Korea", "Tunisia", "Curaçao"],
           "matches" => [ ["home" => "Belgium", "away" => "South Korea"], ["home" => "Tunisia", "away" => "Curaçao"], ["home" => "South Korea", "away" => "Curaçao"], ["home" => "Belgium", "away" => "Tunisia"], ["home" => "South Korea", "away" => "Tunisia"], ["home" => "Curaçao", "away" => "Belgium"] ] ],
  "L" => [ "teams" => ["Germany", "Ecuador", "Côte d'Ivoire", "Haiti"],
           "matches" => [ ["home" => "Germany", "away" => "Ecuador"], ["home" => "Côte d'Ivoire", "away" => "Haiti"], ["home" => "Ecuador", "away" => "Haiti"], ["home" => "Germany", "away" => "Côte d'Ivoire"], ["home" => "Ecuador", "away" => "Côte d'Ivoire"], ["home" => "Haiti", "away" => "Germany"] ] ]
];

// KO Data (R32)
$r32 = [
    73 => ["date"=>"2026-06-28", "t1"=>$group_runners["A"]??"A2", "t2"=>$group_runners["B"]??"B2"],
    74 => ["date"=>"2026-06-29", "t1"=>$group_winners["E"]??"E1", "t2"=>"3rd Group"],
    75 => ["date"=>"2026-06-29", "t1"=>$group_winners["F"]??"F1", "t2"=>$group_runners["C"]??"C2"],
    76 => ["date"=>"2026-06-29", "t1"=>$group_winners["C"]??"C1", "t2"=>$group_runners["F"]??"F2"],
    77 => ["date"=>"2026-06-30", "t1"=>$group_winners["I"]??"I1", "t2"=>"3rd Group"],
    78 => ["date"=>"2026-06-30", "t1"=>$group_runners["E"]??"E2", "t2"=>$group_runners["I"]??"I2"],
    79 => ["date"=>"2026-06-30", "t1"=>$group_winners["A"]??"A1", "t2"=>"3rd Group"],
    80 => ["date"=>"2026-07-01", "t1"=>$group_winners["L"]??"L1", "t2"=>"3rd Group"],
    81 => ["date"=>"2026-07-01", "t1"=>$group_winners["D"]??"D1", "t2"=>"3rd Group"],
    82 => ["date"=>"2026-07-01", "t1"=>$group_winners["G"]??"G1", "t2"=>"3rd Group"],
    83 => ["date"=>"2026-07-02", "t1"=>$group_runners["K"]??"K2", "t2"=>$group_runners["L"]??"L2"],
    84 => ["date"=>"2026-07-02", "t1"=>$group_winners["H"]??"H1", "t2"=>$group_runners["J"]??"J2"],
    85 => ["date"=>"2026-07-02", "t1"=>$group_winners["B"]??"B1", "t2"=>"3rd Group"],
    86 => ["date"=>"2026-07-03", "t1"=>$group_winners["J"]??"J1", "t2"=>$group_runners["H"]??"H2"],
    87 => ["date"=>"2026-07-03", "t1"=>$group_winners["K"]??"K1", "t2"=>"3rd Group"],
    88 => ["date"=>"2026-07-03", "t1"=>$group_runners["D"]??"D2", "t2"=>$group_runners["G"]??"G2"],
];

// Map for later rounds
$rounds_map = [
  // R16
  89 => [74, 77], 90 => [73, 75], 91 => [76, 78], 92 => [79, 80],
  93 => [83, 84], 94 => [81, 82], 95 => [86, 88], 96 => [85, 87],
  // QF
  97 => [89, 90], 98 => [93, 94], 99 => [91, 92], 100 => [95, 96],
  // SF
  101 => [97, 98], 102 => [99, 100]
];

// Date Map for KO
$date_map = [
  89=>"2026-07-04", 90=>"2026-07-04", 91=>"2026-07-05", 92=>"2026-07-05",
  93=>"2026-07-06", 94=>"2026-07-06", 95=>"2026-07-07", 96=>"2026-07-07",
  97=>"2026-07-09", 98=>"2026-07-10", 99=>"2026-07-11", 100=>"2026-07-11",
  101=>"2026-07-14", 102=>"2026-07-15", 103=>"2026-07-18", 104=>"2026-07-19",
];

// Helpers for Random Time (Mock)
function mockTime() { return str_pad(rand(13,21), 2, "0", STR_PAD_LEFT) . ":00"; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Tips</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th, td { text-align: center; vertical-align: middle; }
    /* Keeping the functional bolding, but matching the table style */
    .tip-score { font-weight: bold; color: #0d6efd; }
    .tip-winner { font-weight: bold; color: #198754; }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1>My Predictions</h1>
    <div>
        <!-- Added Home Button -->
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>
  </div>

  <!-- GROUP PHASE TABLE -->
  <h2 class="text-center mt-4 mb-3">Group Phase (Matches 1-72)</h2>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Date & Time</th>
          <th>Match (Home vs Away)</th>
          <th>Tip (Score)</th>
          <th>GD</th>
          <th>Predicted Winner</th>
          <th>Actual Winner</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $matchID = 1;
        foreach ($groups as $gName => $gdata) {
          $localID = 0; // for finding dates relative to start
          foreach ($gdata['matches'] as $m) {
             // Mock date generation
             $date = date("Y-m-d", strtotime("+$localID days"));
             
             // Get Tips
             $hScore = getGroupScore($gName, $localID, 'home', $group_preds);
             $aScore = getGroupScore($gName, $localID, 'away', $group_preds);
             
             // Calculations
             $gd = "-";
             $winner = "-";
             if ($hScore !== '' && $aScore !== '') {
                 $diff = (int)$hScore - (int)$aScore;
                 $gd = ($diff > 0 ? "+" : "") . $diff;
                 
                 if ($diff > 0) $winner = $m['home'];
                 elseif ($diff < 0) $winner = $m['away'];
                 else $winner = "Draw";
             }
             
             echo "<tr>
                    <td>{$matchID}</td>
                    <td>{$date} <small class='text-muted'>".mockTime()."</small></td>
                    <td>{$m['home']} <span class='text-muted'>vs</span> {$m['away']}</td>
                    <td class='tip-score'>" . ($hScore!=='' ? "$hScore : $aScore" : "-") . "</td>
                    <td>{$gd}</td>
                    <td>{$winner}</td>
                    <td class='text-muted'>TBD</td>
                    <td>0</td>
                   </tr>";
             
             $localID++;
             $matchID++;
          }
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- KO PHASE TABLE -->
  <h2 class="text-center mt-5 mb-3">Knockout Phase (Matches 73-104)</h2>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Date & Time</th>
          <th>Match (Team 1 vs Team 2)</th>
          <th>Your Tip (Advancing Team)</th>
          <th>Actual Winner</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Prepare Winners Cache for KO Loop
        $W = [];
        for($i=73; $i<=104; $i++) {
            $W[$i] = $ko_preds["winner_$i"] ?? "";
        }

        // Loop through 73 to 104
        for ($i=73; $i<=104; $i++) {
            $t1 = ""; $t2 = "";
            
            // Logic to get Date
            if ($i <= 88) {
               // R32 Dates from the $r32 array
               $date = $r32[$i]['date'] ?? "TBD";
            } else {
               // Later rounds from $date_map
               $date = $date_map[$i] ?? "TBD";
            }

            // 1. Determine Participants
            if ($i <= 88) {
                // R32: Static map
                $t1 = $r32[$i]['t1'];
                $t2 = $r32[$i]['t2'];
            } elseif ($i <= 102) {
                // R16, QF, SF: Dependent on previous winners
                if (isset($rounds_map[$i])) {
                    $src1 = $rounds_map[$i][0];
                    $src2 = $rounds_map[$i][1];
                    $t1 = $W[$src1] !== "" ? $W[$src1] : "Winner #$src1";
                    $t2 = $W[$src2] !== "" ? $W[$src2] : "Winner #$src2";
                }
            } elseif ($i == 103) {
                // 3rd Place
                $m101_src = $rounds_map[101];
                $teamA = $W[$m101_src[0]] ?? "Win#{$m101_src[0]}";
                $teamB = $W[$m101_src[1]] ?? "Win#{$m101_src[1]}";
                $w101  = $W[101] ?? "";
                $loser101 = ($w101 === $teamA) ? $teamB : $teamA;
                if ($w101 === "") $loser101 = "Loser #101";

                $m102_src = $rounds_map[102];
                $teamC = $W[$m102_src[0]] ?? "Win#{$m102_src[0]}";
                $teamD = $W[$m102_src[1]] ?? "Win#{$m102_src[1]}";
                $w102  = $W[102] ?? "";
                $loser102 = ($w102 === $teamC) ? $teamD : $teamC;
                if ($w102 === "") $loser102 = "Loser #102";

                $t1 = $loser101;
                $t2 = $loser102;
            } elseif ($i == 104) {
                // Final
                $t1 = $W[101] !== "" ? $W[101] : "Winner #101";
                $t2 = $W[102] !== "" ? $W[102] : "Winner #102";
            }

            // 2. Get User Tip
            $myTip = $W[$i] ?? "-";
            
            // 3. Render Row
            echo "<tr>
                    <td>{$i}</td>
                    <td>{$date} <small class='text-muted'>".mockTime()."</small></td>
                    <td>{$t1} <span class='text-muted'>vs</span> {$t2}</td>
                    <td class='tip-winner'>{$myTip}</td>
                    <td class='text-muted'>TBD</td>
                    <td>0</td>
                  </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
  
  <div class="text-center mt-5 mb-5">
    <p class="text-muted">Good luck with your predictions!</p>
  </div>

</div>

</body>
</html>