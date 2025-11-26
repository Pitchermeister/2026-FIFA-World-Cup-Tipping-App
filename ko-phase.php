<?php
session_start();

// Recover predictions from session, or initialize empty
$saved_post = $_SESSION['saved_post'] ?? [];

// 1. Check for Final Submission (Redirect to mytips.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['winner_104']) && $_POST['winner_104'] !== "") {
    $_SESSION['saved_post'] = $_POST;
    header("Location: mytips.php");
    exit;
}

// 2. Handle Intermediate Saves (Unlocking phases & Jumping)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['saved_post'] = $_POST;
    $saved_post = $_POST;
    
    // --- Logic to Calculate Jump Anchor ---
    // Count filled items specifically in POST
    $W_temp = [];
    for ($i = 73; $i <= 104; $i++) { $W_temp[$i] = $saved_post["winner_$i"] ?? ""; }

    // Count fills per round
    $c32 = 0; for($i=73; $i<=88; $i++) { if(!empty($W_temp[$i])) $c32++; }
    $c16 = 0; for($i=89; $i<=96; $i++) { if(!empty($W_temp[$i])) $c16++; }
    $cQF = 0; for($i=97; $i<=100; $i++) { if(!empty($W_temp[$i])) $cQF++; }
    $cSF = 0; for($i=101; $i<=102; $i++) { if(!empty($W_temp[$i])) $cSF++; }

    // Determine the furthest "Active" round to jump to
    // Default is just reload, but if we unlocked something, append #anchor
    $anchor = "";
    
    // If R32 is done, we might want to be at R16
    if ($c32 >= 16) {
        $anchor = "r16"; 
        // If R16 is also done, maybe we want QF
        if ($c16 >= 8) {
            $anchor = "qf";
            // If QF is done, SF
            if ($cQF >= 4) {
                $anchor = "sf";
                // If SF is done, Final
                if ($cSF >= 2) {
                    $anchor = "final";
                }
            }
        }
    }
    
    // Perform the redirect to self + anchor
    header("Location: ko-phase.php#" . $anchor);
    exit;
}

// Load winners from Group Stage
$group_winners = $_SESSION['group_winners'] ?? [];
$group_runners = $_SESSION['group_runners'] ?? [];
$group_third   = $_SESSION['group_third'] ?? [];

function randHour() {
  return str_pad(rand(12,22), 2, "0", STR_PAD_LEFT) . ":00";
}

// --- Data Definitions ---
$r32 = [
    73 => ["date"=>"2026-06-28", "t1"=>$group_runners["A"], "t2"=>$group_runners["B"], "label1"=>"A2", "label2"=>"B2"],
    74 => ["date"=>"2026-06-29", "t1"=>$group_winners["E"], "t2"=>$group_third["A"]."/".$group_third["B"]."/".$group_third["C"]."/".$group_third["D"]."/".$group_third["F"], "label1"=>"E1", "label2"=>"3rd Group"],
    75 => ["date"=>"2026-06-29", "t1"=>$group_winners["F"], "t2"=>$group_runners["C"], "label1"=>"F1", "label2"=>"C2"],
    76 => ["date"=>"2026-06-29", "t1"=>$group_winners["C"], "t2"=>$group_runners["F"], "label1"=>"C1", "label2"=>"F2"],
    77 => ["date"=>"2026-06-30", "t1"=>$group_winners["I"], "t2"=>$group_third["C"]."/".$group_third["D"]."/".$group_third["F"]."/".$group_third["G"]."/".$group_third["H"], "label1"=>"I1", "label2"=>"3rd Group"],
    78 => ["date"=>"2026-06-30", "t1"=>$group_runners["E"], "t2"=>$group_runners["I"], "label1"=>"E2", "label2"=>"I2"],
    79 => ["date"=>"2026-06-30", "t1"=>$group_winners["A"], "t2"=>$group_third["C"]."/".$group_third["E"]."/".$group_third["F"]."/".$group_third["H"]."/".$group_third["I"], "label1"=>"A1", "label2"=>"3rd Group"],
    80 => ["date"=>"2026-07-01", "t1"=>$group_winners["L"], "t2"=>$group_third["E"]."/".$group_third["H"]."/".$group_third["I"]."/".$group_third["J"]."/".$group_third["K"], "label1"=>"L1", "label2"=>"3rd Group"],
    81 => ["date"=>"2026-07-01", "t1"=>$group_winners["D"], "t2"=>$group_third["B"]."/".$group_third["E"]."/".$group_third["F"]."/".$group_third["I"]."/".$group_third["J"], "label1"=>"D1", "label2"=>"3rd Group"],
    82 => ["date"=>"2026-07-01", "t1"=>$group_winners["G"], "t2"=>$group_third["A"]."/".$group_third["E"]."/".$group_third["H"]."/".$group_third["I"]."/".$group_third["J"], "label1"=>"G1", "label2"=>"3rd Group"],
    83 => ["date"=>"2026-07-02", "t1"=>$group_runners["K"], "t2"=>$group_runners["L"], "label1"=>"K2", "label2"=>"L2"],
    84 => ["date"=>"2026-07-02", "t1"=>$group_winners["H"], "t2"=>$group_runners["J"], "label1"=>"H1", "label2"=>"J2"],
    85 => ["date"=>"2026-07-02", "t1"=>$group_winners["B"], "t2"=>$group_third["E"]."/".$group_third["F"]."/".$group_third["G"]."/".$group_third["I"]."/".$group_third["J"], "label1"=>"B1", "label2"=>"3rd Group"],
    86 => ["date"=>"2026-07-03", "t1"=>$group_winners["J"], "t2"=>$group_runners["H"], "label1"=>"J1", "label2"=>"H2"],
    87 => ["date"=>"2026-07-03", "t1"=>$group_winners["K"], "t2"=>$group_third["D"]."/".$group_third["E"]."/".$group_third["I"]."/".$group_third["J"]."/".$group_third["L"], "label1"=>"K1", "label2"=>"3rd Group"],
    88 => ["date"=>"2026-07-03", "t1"=>$group_runners["D"], "t2"=>$group_runners["G"], "label1"=>"D2", "label2"=>"G2"],
];

$r16_map = [ 89=>[74,77], 90=>[73,75], 91=>[76,78], 92=>[79,80], 93=>[83,84], 94=>[81,82], 95=>[86,88], 96=>[85,87] ];
$qf_map  = [ 97=>[89,90], 98=>[93,94], 99=>[91,92], 100=>[95,96] ];
$sf_map  = [ 101=>[97,98], 102=>[99,100] ];
$date_map = [
  89=>"2026-07-04", 90=>"2026-07-04", 91=>"2026-07-05", 92=>"2026-07-05",
  93=>"2026-07-06", 94=>"2026-07-06", 95=>"2026-07-07", 96=>"2026-07-07",
  97=>"2026-07-09", 98=>"2026-07-10", 99=>"2026-07-11", 100=>"2026-07-11",
  101=>"2026-07-14", 102=>"2026-07-15", 103=>"2026-07-18", 104=>"2026-07-19",
];

// Helper: Build winners mapping from saved_post
$W = [];
for ($i = 73; $i <= 104; $i++) {
    $W[$i] = $saved_post["winner_$i"] ?? "";
}

// --- Determine which Phases are Unlocked ---
$r32_filled = 0; for($i=73; $i<=88; $i++) { if(!empty($W[$i])) $r32_filled++; }
$r16_filled = 0; for($i=89; $i<=96; $i++) { if(!empty($W[$i])) $r16_filled++; }
$qf_filled = 0; for($i=97; $i<=100; $i++) { if(!empty($W[$i])) $qf_filled++; }
$sf_filled = 0; for($i=101; $i<=102; $i++) { if(!empty($W[$i])) $sf_filled++; }

$show_r32 = true;
$show_r16 = ($r32_filled >= 16);
$show_qf  = ($r16_filled >= 8);
$show_sf  = ($qf_filled >= 4);
$show_final = ($sf_filled >= 2);

function draw_round($title, $map, $date_map, $W, $saved_post, $id_anchor) {
    echo "<div id='$id_anchor' class='mt-5'><h2 class='text-center mb-3'>" . htmlspecialchars($title) . "</h2>";
    echo "<div class='table-responsive'><table class='table table-bordered text-center align-middle'>";
    echo "<thead class='table-light'><tr><th>#</th><th>Date</th><th>Team 1</th><th>Team 2</th><th>Who Advances?</th></tr></thead><tbody>";
    
    foreach ($map as $num => $pair) {
        $a = $pair[0]; $b = $pair[1];
        $t1 = $W[$a] ?? ""; 
        $t2 = $W[$b] ?? "";
        
        if ($t1 == "") $t1 = "Winner Match $a";
        if ($t2 == "") $t2 = "Winner Match $b";

        $date = $date_map[$num] ?? "";
        $selected = $saved_post["winner_$num"] ?? '';
        
        echo "<tr>
                <td>$num</td>
                <td>$date</td>
                <td>$t1</td>
                <td>$t2</td>
                <td>
                  <select name='winner_$num' class='form-select' required>
                    <option value=''>Select...</option>
                    <option value='".htmlspecialchars($t1)."' ".($selected==$t1?'selected':'').">$t1</option>
                    <option value='".htmlspecialchars($t2)."' ".($selected==$t2?'selected':'').">$t2</option>
                  </select>
                </td>
              </tr>";
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
  <h1 class="mb-4 text-center">Knockout Phase</h1>

  <form method="post">
    
    <div id="r32">
      <h2 class="text-center mb-3">Round of 32</h2>
      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light"><tr><th>#</th><th>Date</th><th>Match</th><th>Team 1</th><th>Team 2</th><th>Who Advances?</th></tr></thead>
          <tbody>
            <?php foreach ($r32 as $num => $row): 
                $sel = $saved_post["winner_$num"] ?? '';
            ?>
            <tr>
              <td><?= $num ?></td>
              <td><?= $row['date'] ?></td>
              <td><?= $row['label1'] ?> vs <?= $row['label2'] ?></td>
              <td><?= $row['t1'] ?></td>
              <td><?= $row['t2'] ?></td>
              <td>
                <select name="winner_<?= $num ?>" class="form-select" required>
                    <option value="">Select...</option>
                    <option value="<?= htmlspecialchars($row['t1']) ?>" <?= ($sel==$row['t1']?'selected':'') ?>><?= $row['t1'] ?></option>
                    <option value="<?= htmlspecialchars($row['t2']) ?>" <?= ($sel==$row['t2']?'selected':'') ?>><?= $row['t2'] ?></option>
                </select>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php 
    if ($show_r16) draw_round("Round of 16", $r16_map, $date_map, $W, $saved_post, "r16");
    if ($show_qf)  draw_round("Quarterfinals", $qf_map, $date_map, $W, $saved_post, "qf");
    if ($show_sf)  draw_round("Semifinals", $sf_map, $date_map, $W, $saved_post, "sf");
    
    if ($show_final) {
        // Calculate losers for 3rd place
        $t1_sf = $W[97] ?? ""; $t2_sf = $W[98] ?? "";
        $l1 = ($W[101] == $t1_sf) ? $t2_sf : $t1_sf;

        $t3_sf = $W[99] ?? ""; $t4_sf = $W[100] ?? "";
        $l2 = ($W[102] == $t3_sf) ? $t4_sf : $t3_sf;
        
        // --- FIX: Use Null Coalescing (??) to prevent Warning ---
        $sel103 = $saved_post['winner_103'] ?? ''; 
        $sel104 = $saved_post['winner_104'] ?? ''; 

        // 3rd Place
        echo "<div id='final' class='mt-5'><h2 class='text-center'>Third Place</h2>";
        echo "<table class='table table-bordered text-center align-middle bg-white'><tbody><tr>";
        echo "<td>103</td><td>2026-07-18</td><td>$l1</td><td>$l2</td><td><select name='winner_103' class='form-select' required><option value=''>Select...</option><option value='$l1' ".($sel103==$l1?'selected':'').">$l1</option><option value='$l2' ".($sel103==$l2?'selected':'').">$l2</option></select></td>";
        echo "</tr></tbody></table></div>";

        // Final
        echo "<div class='mt-5'><h2 class='text-center'>Final</h2>";
        $f1 = $W[101]; $f2 = $W[102];
        echo "<table class='table table-bordered text-center align-middle bg-white'><tbody><tr>";
        echo "<td>104</td><td>2026-07-19</td><td>$f1</td><td>$f2</td><td><select name='winner_104' class='form-select' required><option value=''>Select...</option><option value='$f1' ".($sel104==$f1?'selected':'').">$f1</option><option value='$f2' ".($sel104==$f2?'selected':'').">$f2</option></select></td>";
        echo "</tr></tbody></table></div>";
    }
    ?>

    <div class="text-center mt-5 mb-5">
      <button type="submit" class="btn btn-primary px-4">
        <?php if ($show_final) echo "Save Final & See Tips"; else echo "Save & Unlock Next Round"; ?>
      </button>
    </div>

  </form>
</div>
</body>
</html>