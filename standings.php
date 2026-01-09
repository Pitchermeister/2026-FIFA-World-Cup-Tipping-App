<?php
session_start();
require_once 'db_config.php';

// Calculate points for all users
$conn = get_db();

$query = "SELECT u.id, u.username, u.profile_pic,
          (SELECT COUNT(*) * 3 
           FROM group_predictions gp 
           JOIN matches m ON gp.match_id = m.id 
           JOIN results r ON m.id = r.match_id 
           WHERE gp.user_id = u.id 
           AND gp.home_prediction = r.home_score 
           AND gp.away_prediction = r.away_score) as exact_points,
          
          (SELECT COUNT(*) * 2 
           FROM group_predictions gp 
           JOIN matches m ON gp.match_id = m.id 
           JOIN results r ON m.id = r.match_id 
           WHERE gp.user_id = u.id 
           AND (gp.home_prediction - gp.away_prediction) = (r.home_score - r.away_score)
           AND NOT (gp.home_prediction = r.home_score AND gp.away_prediction = r.away_score)) as diff_points,
          
          (SELECT COUNT(*) 
           FROM group_predictions gp 
           JOIN matches m ON gp.match_id = m.id 
           JOIN results r ON m.id = r.match_id 
           WHERE gp.user_id = u.id 
           AND SIGN(gp.home_prediction - gp.away_prediction) = SIGN(r.home_score - r.away_score)
           AND (gp.home_prediction - gp.away_prediction) != (r.home_score - r.away_score)) as winner_points
          
          FROM users u 
          WHERE u.role = 'user' 
          ORDER BY (exact_points + diff_points + winner_points) DESC";

$result = $conn->query($query);
$users = [];
while ($row = $result->fetch_assoc()) {
    $row['total'] = $row['exact_points'] + $row['diff_points'] + $row['winner_points'];
    $users[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standings - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <?php if (isset($_SESSION["user"])) { ?>
            <h3><a href="home.php">← Back to Home</a></h3>
        <?php } else { ?>
            <h3><a href="login.php">Login</a> | <a href="register.php">Register</a></h3>
        <?php } ?>
        <hr>
        
        <h1>Standings</h1>
        
        <table class="table">
            <tr>
                <th>Rank</th>
                <th>User</th>
                <th>Total Points</th>
            </tr>
            <?php 
            $rank = 1;
            foreach ($users as $user) { 
            ?>
                <tr>
                    <td><?php echo $rank++; ?></td>
                    <td>
                        <?php if ($user['profile_pic'] && file_exists($user['profile_pic'])) { ?>
                            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" width="30" height="30" style="border-radius:50%;">
                        <?php } ?>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </td>
                    <td><?php echo $user['total']; ?></td>
                </tr>
            <?php } ?>
        </table>
        
        <p><em>Points: 3 for exact score, 2 for correct difference, 1 for correct winner</em></p>
    </div>
</body>
</html>