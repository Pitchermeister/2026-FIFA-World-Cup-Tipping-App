<?php
session_start();

// Redirect ONLY when winner_104 was actually selected in the KO form
if (isset($_SESSION['saved_post']['winner_104']) &&
    $_SESSION['saved_post']['winner_104'] !== "" &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['winner_104'])
) {
    header("Location: mytips.php");
    exit;
}




$group_winners = $_SESSION['group_winners'] ?? [];
$group_runners = $_SESSION['group_runners'] ?? [];
$group_third = $_SESSION['group_third'] ?? [];
// Helper: random hour string (minutes always :00)
function randHour() {
  return str_pad(rand(12,22), 2, "0", STR_PAD_LEFT) . ":00";
}

// Round of 32 static data (73..88)
$r32 = [
    73 => ["date"=>"2026-06-28", "t1"=>$group_runners["A"], "t2"=>$group_runners["B"], "label1"=>"A2", "label2"=>"B2"],
    74 => ["date"=>"2026-06-29", "t1"=>$group_winners["E"], "t2"=>$group_third["A"]."/".$group_third["B"]."/".$group_third["C"]."/".$group_third["D"]."/".$group_third["F"], "label1"=>"E1", "label2"=>"A3/B3/C3/D3/F3"],
    75 => ["date"=>"2026-06-29", "t1"=>$group_winners["F"], "t2"=>$group_runners["C"], "label1"=>"F1", "label2"=>"C2"],
    76 => ["date"=>"2026-06-29", "t1"=>$group_winners["C"], "t2"=>$group_runners["F"], "label1"=>"C1", "label2"=>"F2"],
    77 => ["date"=>"2026-06-30", "t1"=>$group_winners["I"], "t2"=>$group_third["C"]."/".$group_third["D"]."/".$group_third["F"]."/".$group_third["G"]."/".$group_third["H"], "label1"=>"I1", "label2"=>"C3/D3/F3/G3/H3"],
    78 => ["date"=>"2026-06-30", "t1"=>$group_runners["E"], "t2"=>$group_runners["I"], "label1"=>"E2", "label2"=>"I2"],
    79 => ["date"=>"2026-06-30", "t1"=>$group_winners["A"], "t2"=>$group_third["C"]."/".$group_third["E"]."/".$group_third["F"]."/".$group_third["H"]."/".$group_third["I"], "label1"=>"A1", "label2"=>"C3/E3/F3/H3/I3"],
    80 => ["date"=>"2026-07-01", "t1"=>$group_winners["L"], "t2"=>$group_third["E"]."/".$group_third["H"]."/".$group_third["I"]."/".$group_third["J"]."/".$group_third["K"], "label1"=>"L1", "label2"=>"E3/H3/I3/J3/K3"],
    81 => ["date"=>"2026-07-01", "t1"=>$group_winners["D"], "t2"=>$group_third["B"]."/".$group_third["E"]."/".$group_third["F"]."/".$group_third["I"]."/".$group_third["J"], "label1"=>"D1", "label2"=>"B3/E3/F3/I3/J3"],
    82 => ["date"=>"2026-07-01", "t1"=>$group_winners["G"], "t2"=>$group_third["A"]."/".$group_third["E"]."/".$group_third["H"]."/".$group_third["I"]."/".$group_third["J"], "label1"=>"G1", "label2"=>"A3/E3/H3/I3/J3"],
    83 => ["date"=>"2026-07-02", "t1"=>$group_runners["K"], "t2"=>$group_runners["L"], "label1"=>"K2", "label2"=>"L2"],
    84 => ["date"=>"2026-07-02", "t1"=>$group_winners["H"], "t2"=>$group_runners["J"], "label1"=>"H1", "label2"=>"J2"],
    85 => ["date"=>"2026-07-02", "t1"=>$group_winners["B"], "t2"=>$group_third["E"]."/".$group_third["F"]."/".$group_third["G"]."/".$group_third["I"]."/".$group_third["J"], "label1"=>"B1", "label2"=>"E3/F3/G3/I3/J3"],
    86 => ["date"=>"2026-07-03", "t1"=>$group_winners["J"], "t2"=>$group_runners["H"], "label1"=>"J1", "label2"=>"H2"],
    87 => ["date"=>"2026-07-03", "t1"=>$group_winners["K"], "t2"=>$group_third["D"]."/".$group_third["E"]."/".$group_third["I"]."/".$group_third["J"]."/".$group_third["L"], "label1"=>"K1", "label2"=>"D3/E3/I3/J3/L3"],
    88 => ["date"=>"2026-07-03", "t1"=>$group_runners["D"], "t2"=>$group_runners["G"], "label1"=>"D2", "label2"=>"G2"],
];



// Mappings for later rounds (using official numbering)
$r16_map = [
  89 => [74, 77],
  90 => [73, 75],
  91 => [76, 78],
  92 => [79, 80],
  93 => [83, 84],
  94 => [81, 82],
  95 => [86, 88],
  96 => [85, 87],
];

$qf_map = [
  97 => [89, 90],
  98 => [93, 94],
  99 => [91, 92],
  100 => [95, 96],
];

$sf_map = [
  101 => [97, 98],
  102 => [99, 100],
];

$third_map = [
  103 => [101, 102], // Loser 101 vs Loser 102
];

$final_map = [
  104 => [101, 102], // Winner 101 vs Winner 102
];

// Date mapping for R16, QF, SF, 3rd, Final
$date_map = [
  89 => "2026-07-04", 90 => "2026-07-04",
  91 => "2026-07-05", 92 => "2026-07-05",
  93 => "2026-07-06", 94 => "2026-07-06",
  95 => "2026-07-07", 96 => "2026-07-07",
  97 => "2026-07-09", 98 => "2026-07-10",
  99 => "2026-07-11", 100 => "2026-07-11",
  101 => "2026-07-14", 102 => "2026-07-15",
  103 => "2026-07-18", 104 => "2026-07-19",
];

// Read saved POST from session or use empty array
$saved_post = $_SESSION['saved_post'] ?? [];

// previous filled stored in session (or 0)
$prev_filled = $_SESSION['filled'] ?? 0;

// If form was just submitted, update saved_post and compute filled
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save incoming POST into session so values persist after redirect
    $_SESSION['saved_post'] = $_POST;
    $saved_post = $_POST;

    // Count how many winner_* fields are filled in POST
    $filled_now = 0;
    for ($i = 73; $i <= 104; $i++) {
        $k = "winner_$i";
        if (isset($_POST[$k]) && $_POST[$k] !== '') $filled_now++;
    }

    // store new filled count in session
    $_SESSION['filled'] = $filled_now;

    // Determine which round (if any) was just unlocked compared to prev_filled
    $anchor = '';
    if ($prev_filled < 16 && $filled_now >= 16) $anchor = 'r16';
    elseif ($prev_filled < 24 && $filled_now >= 24) $anchor = 'qf';
    elseif ($prev_filled < 28 && $filled_now >= 28) $anchor = 'sf';
    elseif ($prev_filled < 30 && $filled_now >= 30) $anchor = 'third'; // third and final unlocked together; scroll to third
    // update prev_filled for next time (already set above)

    // If a new round unlocked, redirect to anchor to auto-scroll
    if ($anchor !== '') {
        // use absolute or relative path depending on where file is located
        $self = htmlspecialchars($_SERVER['PHP_SELF']);
        header("Location: {$self}#{$anchor}");
        exit;
    }
    // else no redirect; continue to render page (POST will show)
}

// Helper: build winners mapping from saved_post (numbers => chosen team)
$W = [];
for ($i = 73; $i <= 104; $i++) {
    $key = "winner_$i";
    if (isset($saved_post[$key]) && $saved_post[$key] !== '') {
        $W[$i] = $saved_post[$key];
    } else {
        $W[$i] = ""; // ensure key exists
    }
}

// Generic round drawing function (renders selects using $saved_post to preserve selection)
function draw_round($title, $map, $date_map, $W, $saved_post, $allowSelect = true, $anchorId = '') {
    if ($anchorId !== '') {
        echo "<a id='" . htmlspecialchars($anchorId) . "'></a>";
    }
    echo "<div class='mt-5'><h2 class='text-center mb-3'>" . htmlspecialchars($title) . "</h2>";
    echo "<div class='table-responsive'><table class='table table-bordered text-center align-middle'>";
    echo "<thead class='table-light'><tr>
            <th>#</th><th>Date & Time</th><th>Team 1</th><th>Team 2</th><th>Who Advances?</th>
          </tr></thead><tbody>";

    foreach ($map as $num => $pair) {
        $a = $pair[0]; $b = $pair[1];
        $t1 = $W[$a] ?? ""; // maybe empty if earlier winner not selected
        $t2 = $W[$b] ?? "";
        $date = $date_map[$num] ?? "";
        $dt = $date ? ($date . " " . randHour()) : randHour();
        $matchLabel = "Winner Match {$a} vs Winner Match {$b}";
        $selectName = "winner_" . $num;
        $selectedVal = $saved_post[$selectName] ?? '';

        echo "<tr>";
        echo "<td>" . htmlspecialchars($num) . "</td>";
        echo "<td>" . htmlspecialchars($dt) . "</td>";
        echo "<td>" . htmlspecialchars($t1) . "</td>";
        echo "<td>" . htmlspecialchars($t2) . "</td>";
        echo "<td>";
        if ($allowSelect) {
            // options: prefer actual team names if available, otherwise placeholder
            $opt1 = $t1 !== "" ? $t1 : "Winner Match {$a}";
            $opt2 = $t2 !== "" ? $t2 : "Winner Match {$b}";

            echo "<select name='" . htmlspecialchars($selectName) . "' class='form-select' required>";
            echo "<option value=''>Select...</option>";
            // mark selected if matches saved_post
            $sel1 = ($selectedVal !== '' && $selectedVal === $opt1) ? 'selected' : '';
            $sel2 = ($selectedVal !== '' && $selectedVal === $opt2) ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($opt1) . "' $sel1>" . htmlspecialchars($opt1) . "</option>";
            echo "<option value='" . htmlspecialchars($opt2) . "' $sel2>" . htmlspecialchars($opt2) . "</option>";
            echo "</select>";
        } else {
            echo "-";
        }
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody></table></div></div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>KO Phase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> body{background:#f8f9fa} table{background:#fff} th,td{text-align:center;vertical-align:middle} </style>
</head>
<body>
<div class="container my-5">
  <h1 class="mb-4 text-center">Knockout Phase Predictions</h1>

  <form method="post" action="#">
    <!-- Round of 32 (always visible) -->
    <div id="r32">
      <h2 class="text-center mb-3">Round of 32</h2>
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light"><tr>
            <th>#</th><th>Date & Time</th><th>Match</th><th>Team 1</th><th>Team 2</th><th>Who Advances?</th>
          </tr></thead>
          <tbody>
            <?php
            // Render R32, preserving selection from $saved_post
            foreach ($r32 as $num => $row) {
                $dt = $row['date'] . " " . randHour();
                $matchText = $row['label1'] . " vs. " . $row['label2'];
                $name = "winner_" . $num;
                $selected = $saved_post[$name] ?? '';
                echo "<tr>
                        <td>" . htmlspecialchars($num) . "</td>
                        <td>" . htmlspecialchars($dt) . "</td>
                        <td>" . htmlspecialchars($matchText) . "</td>
                        <td>" . htmlspecialchars($row['t1']) . "</td>
                        <td>" . htmlspecialchars($row['t2']) . "</td>
                        <td>
                          <select name='" . htmlspecialchars($name) . "' class='form-select' required>
                            <option value=''>Select...</option>
                            <option value='" . htmlspecialchars($row['t1']) . "' " . ($selected === $row['t1'] ? 'selected' : '') . ">" . htmlspecialchars($row['t1']) . "</option>
                            <option value='" . htmlspecialchars($row['t2']) . "' " . ($selected === $row['t2'] ? 'selected' : '') . ">" . htmlspecialchars($row['t2']) . "</option>
                          </select>
                        </td>
                      </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php
    // Determine unlock flags based on current saved_post (or session)
    // Count filled winners in saved_post
    $filledCount = 0;
    for ($i=73; $i<=104; $i++) {
        if (!empty($saved_post["winner_$i"])) $filledCount++;
    }
    $unlock_r16 = ($filledCount >= 16);
    $unlock_qf  = ($filledCount >= 24);
    $unlock_sf  = ($filledCount >= 28);
    $unlock_final = ($filledCount >= 30);

    // Show R16 if unlocked
    if ($unlock_r16) {
        // anchor id r16 will be printed inside draw_round
        draw_round("Round of 16", $r16_map, $date_map, $W, $saved_post, true, 'r16');
    }

    // Show QF if unlocked
    if ($unlock_qf) {
        draw_round("Quarterfinals", $qf_map, $date_map, $W, $saved_post, true, 'qf');
    }

    // Show SF if unlocked
    if ($unlock_sf) {
        draw_round("Semifinals", $sf_map, $date_map, $W, $saved_post, true, 'sf');
    }

    // If SF winners are present, show Third place and Final
    if ($unlock_final) {
        // Build SF participant pairs from W mapping
        $sf_teams = [];
        foreach ($sf_map as $mnum => [$a, $b]) {
            $sf_teams[$mnum] = [$W[$a] ?? "", $W[$b] ?? ""];
        }

        // Determine losers for 103 (Loser 101 vs Loser 102)
        $losers = [];
        foreach ([101, 102] as $mnum) {
            $winnerKey = "winner_$mnum";
            $winnerVal = $saved_post[$winnerKey] ?? '';
            $pair = $sf_teams[$mnum] ?? ['', ''];
            $t1 = $pair[0]; $t2 = $pair[1];
            if ($winnerVal !== '' && $winnerVal === $t1) $losers[$mnum] = $t2;
            elseif ($winnerVal !== '' && $winnerVal === $t2) $losers[$mnum] = $t1;
            else $losers[$mnum] = "Loser Match {$mnum}";
        }

        // Third place (103)
        echo "<a id='third'></a>";
        echo "<div class='mt-5'><h2 class='text-center mb-3'>Third Place Match</h2>";
        echo "<div class='table-responsive'><table class='table table-bordered text-center align-middle'>";
        echo "<thead class='table-light'><tr><th>#</th><th>Date & Time</th><th>Match</th><th>Team 1</th><th>Team 2</th><th>Who Wins?</th></tr></thead><tbody>";
        $dt103 = ($date_map[103] ?? "") . " " . randHour();
        $t1_103 = $losers[101] ?? "Loser Match 101";
        $t2_103 = $losers[102] ?? "Loser Match 102";
        $sel103 = $saved_post['winner_103'] ?? '';
        echo "<tr>
                <td>103</td>
                <td>" . htmlspecialchars($dt103) . "</td>
                <td>Loser Match 101 vs Loser Match 102</td>
                <td>" . htmlspecialchars($t1_103) . "</td>
                <td>" . htmlspecialchars($t2_103) . "</td>
                <td>
                  <select name='winner_103' class='form-select' required>
                    <option value=''>Select...</option>
                    <option value='" . htmlspecialchars($t1_103) . "' " . ($sel103 === $t1_103 ? 'selected' : '') . ">" . htmlspecialchars($t1_103) . "</option>
                    <option value='" . htmlspecialchars($t2_103) . "' " . ($sel103 === $t2_103 ? 'selected' : '') . ">" . htmlspecialchars($t2_103) . "</option>
                  </select>
                </td>
              </tr>";
        echo "</tbody></table></div></div>";

        // Final (104)
        echo "<a id='final'></a>";
        echo "<div class='mt-5'><h2 class='text-center mb-3'>Final</h2>";
        echo "<div class='table-responsive'><table class='table table-bordered text-center align-middle'>";
        echo "<thead class='table-light'><tr><th>#</th><th>Date & Time</th><th>Match</th><th>Team 1</th><th>Team 2</th><th>Who Wins?</th></tr></thead><tbody>";
        $dt104 = ($date_map[104] ?? "") . " " . randHour();
        $t1_104 = $W[101] ?? "Winner Match 101";
        $t2_104 = $W[102] ?? "Winner Match 102";
        $sel104 = $saved_post['winner_104'] ?? '';
        echo "<tr>
                <td>104</td>
                <td>" . htmlspecialchars($dt104) . "</td>
                <td>Winner Match 101 vs Winner Match 102</td>
                <td>" . htmlspecialchars($t1_104) . "</td>
                <td>" . htmlspecialchars($t2_104) . "</td>
                <td>
                  <select name='winner_104' class='form-select' required>
                    <option value=''>Select...</option>
                    <option value='" . htmlspecialchars($t1_104) . "' " . ($sel104 === $t1_104 ? 'selected' : '') . ">" . htmlspecialchars($t1_104) . "</option>
                    <option value='" . htmlspecialchars($t2_104) . "' " . ($sel104 === $t2_104 ? 'selected' : '') . ">" . htmlspecialchars($t2_104) . "</option>
                  </select>
                </td>
              </tr>";
        echo "</tbody></table></div></div>";
    }
    ?>

    <!-- Save KO Predictions button (always at the bottom) -->
    <div class="text-center mt-5 mb-5">
      <button type="submit" name="save_ko" class="btn btn-primary px-4">Save KO Predictions</button>
    </div>

  </form> <!-- end form -->

</div> <!-- container -->

</body>
</html>
