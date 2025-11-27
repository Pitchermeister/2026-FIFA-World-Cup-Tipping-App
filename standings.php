<?php
session_start();

// 1. Load Users from file
$users = [];
$loggedInUser = $_SESSION['user'] ?? '';

// Check if users.txt exists
if (file_exists("users.txt")) {
    $lines = file("users.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Format: username|hash|profile_path|role
        $parts = explode("|", $line);
        
        // Safety check for array length
        if (count($parts) < 4) continue;
        
        $username = trim($parts[0]);
        $profilePic = trim($parts[2]);
        $role = trim($parts[3]);
        
        // 2. Exclude Admins
        if ($role === 'admin') {
            continue;
        }
        
        // Add to list
        $users[] = [
            'name' => $username,
            'avatar' => $profilePic
        ];
    }
}

// 3. Logic to handle Test/Clear buttons (Simulate points for REAL users)
$action = $_POST['action'] ?? 'clear';

// Iterate through the real users we loaded and assign points
foreach ($users as &$user) {
    if ($action === 'test') {
        // Random Simulation Logic
        $total_group_correct = rand(20, 55);
        $exact = rand(0, 15);
        $diff  = rand(0, 20);
        $winner = max(0, $total_group_correct - $exact - $diff);

        $user['exact']  = $exact;
        $user['diff']   = $diff;
        $user['winner'] = $winner;
        
        $user['r32']    = rand(0, 16);
        $user['r16']    = rand(0, 8);
        $user['qf']     = rand(0, 4);
        $user['sf']     = rand(0, 2);
        $user['bronze'] = rand(0, 1);
        $user['champ']  = rand(0, 1);
        
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
}
unset($user); // Break reference

// 4. Sort users by Total Points Descending
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
    
    /* Place column */
    td:first-child { color: #6c757d; width: 50px; }

    /* User column */
    td:nth-child(2) { text-align: left; font-weight: bold; color: #333; min-width: 200px; }
    
    /* Total Points column */
    td:last-child { font-weight: bold; background-color: #f8f9fa; color: #0d6efd; }

    /* Avatar styling */
    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    .user-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #dee2e6;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 14px;
        vertical-align: middle;
    }
    
    /* Highlight logged-in user */
    .table-warning td:last-child { background-color: #fff3cd; } /* Match Bootstrap warning color */
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
          <th>User</th>
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
        <?php if (empty($users)): ?>
            <tr><td colspan="12" class="text-center py-4 text-muted">No users found.</td></tr>
        <?php else: ?>
            <?php
            $rank = 1;
            foreach ($users as $u) {
                // Highlight row if it matches logged-in user
                $rowClass = ($u['name'] === $loggedInUser) ? 'table-warning' : '';
                
                // Determine Avatar
                $avatarHtml = "";
                if (!empty($u['avatar']) && file_exists($u['avatar'])) {
                    $avatarHtml = '<img src="'.htmlspecialchars($u['avatar']).'" class="user-avatar" alt="Avatar">';
                } else {
                    // Placeholder with first letter
                    $initial = strtoupper(substr($u['name'], 0, 1));
                    $avatarHtml = '<div class="user-avatar-placeholder">'.$initial.'</div>';
                }

                echo "<tr class='{$rowClass}'>";
                echo "<td>{$rank}</td>";
                echo "<td>{$avatarHtml}" . htmlspecialchars($u['name']) . "</td>";
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
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Debug/Test Controls -->
  <div class="mt-4">
      <form method="post" class="d-flex gap-2">
          <button type="submit" name="action" value="test" class="btn btn-warning">Test (Simulate Points)</button>
          <button type="submit" name="action" value="clear" class="btn btn-secondary">Clear</button>
      </form>
  </div>

</div>

</body>
</html>