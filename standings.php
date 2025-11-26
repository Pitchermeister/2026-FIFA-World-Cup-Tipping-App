<?php
// Logic to handle Test/Clear buttons
$users = [];
$action = $_POST['action'] ?? 'clear';

// Generate data for 10 users
for ($i = 1; $i <= 10; $i++) {
    $user = ['name' => "user_{$i}"];
    
    if ($action === 'test') {
        // Randomly determine how many group matches (out of 72) they got "correct" in some way
        // Let's assume a realistic user gets between 20 and 55 matches right
        $total_group_correct = rand(20, 55);
        
        // Distribute these correct tips across the 3 categories mutually exclusively
        // (You can't get Exact Score AND Goal Diff for the same match)
        $exact = rand(0, 15);
        $diff  = rand(0, 20);
        
        // Remaining correct tips are just "Winner"
        $winner = $total_group_correct - $exact - $diff;
        if ($winner < 0) $winner = 0; // Safety cap

        $user['exact']  = $exact;
        $user['diff']   = $diff;
        $user['winner'] = $winner;
        
        // KO Phase Randoms (Max available matches per round)
        $user['r32']    = rand(0, 16); // Max 16
        $user['r16']    = rand(0, 8);  // Max 8
        $user['qf']     = rand(0, 4);  // Max 4
        $user['sf']     = rand(0, 2);  // Max 2
        $user['bronze'] = rand(0, 1);  // Max 1
        $user['champ']  = rand(0, 1);  // Max 1
        
        // Calculate Total Points based on multipliers in header
        $user['total'] = ($user['exact'] * 3) + 
                         ($user['diff'] * 2) + 
                         ($user['winner'] * 1) + 
                         ($user['r32'] * 2) + 
                         ($user['r16'] * 4) + 
                         ($user['qf'] * 8) + 
                         ($user['sf'] * 16) + 
                         ($user['bronze'] * 24) + 
                         ($user['champ'] * 32);
    } else {
        // Clear / Default state
        $user['exact'] = 0; $user['diff'] = 0; $user['winner'] = 0;
        $user['r32'] = 0; $user['r16'] = 0; $user['qf'] = 0;
        $user['sf'] = 0; $user['bronze'] = 0; $user['champ'] = 0;
        $user['total'] = 0;
    }
    
    $users[] = $user;
}

// Sort users by Total Points Descending
usort($users, function($a, $b) {
    return $b['total'] <=> $a['total'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Standings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    table { background-color: #fff; }
    th { text-align: center; vertical-align: middle; background-color: #e9ecef; font-size: 0.9rem; }
    td { text-align: center; vertical-align: middle; }
    
    /* Username is now the 2nd column, keep it bold/left-aligned */
    td:nth-child(2) { text-align: left; font-weight: bold; color: #333; }
    
    /* Place column (1st) */
    td:first-child { color: #6c757d; }

    /* Total Points column (Last) */
    td:last-child { font-weight: bold; background-color: #f8f9fa; color: #0d6efd; }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Standings</h2>
    <!-- Consistent Home button -->
    <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
  </div>

  <div class="table-responsive shadow-sm bg-white rounded">
    <table class="table table-bordered table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Username</th>
          <th>Exact Score<br><small class="text-muted">(3)</small></th>
          <th>Goal Diff.<br><small class="text-muted">(2)</small></th>
          <th>Winner<br><small class="text-muted">(1)</small></th>
          <th>R32<br><small class="text-muted">(2)</small></th>
          <th>R16<br><small class="text-muted">(4)</small></th>
          <th>QF<br><small class="text-muted">(8)</small></th>
          <th>SF<br><small class="text-muted">(16)</small></th>
          <th>Bronze<br><small class="text-muted">(24)</small></th>
          <th>Champion<br><small class="text-muted">(32)</small></th>
          <th>Total Points</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $rank = 1;
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>{$rank}</td>";
            echo "<td>{$u['name']}</td>";
            echo "<td>{$u['exact']}</td>";
            echo "<td>{$u['diff']}</td>";
            echo "<td>{$u['winner']}</td>";
            echo "<td>{$u['r32']}</td>";
            echo "<td>{$u['r16']}</td>";
            echo "<td>{$u['qf']}</td>";
            echo "<td>{$u['sf']}</td>";
            echo "<td>{$u['bronze']}</td>";
            echo "<td>{$u['champ']}</td>";
            echo "<td>{$u['total']}</td>";
            echo "</tr>";
            $rank++;
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Debug/Test Controls -->
  <div class="mt-4">
      <form method="post" class="d-flex gap-2">
          <button type="submit" name="action" value="test" class="btn btn-warning">Test (Random Data)</button>
          <button type="submit" name="action" value="clear" class="btn btn-secondary">Clear</button>
      </form>
  </div>

</div>

</body>
</html>