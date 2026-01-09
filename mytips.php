<?php
session_start();
require_once 'db_config.php';

// User only
if (!isset($_SESSION["user"]) || !isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Calculate points
function calculate_points($home_pred, $away_pred, $home_result, $away_result) {
    if ($home_result === null || $away_result === null) {
        return null;
    }
    
    // Exact score: 3 points
    if ($home_pred == $home_result && $away_pred == $away_result) {
        return 3;
    }
    
    // Goal difference: 2 points
    if (($home_pred - $away_pred) == ($home_result - $away_result)) {
        return 2;
    }
    
    // Winner: 1 point
    $pred_sign = $home_pred <=> $away_pred;
    $result_sign = $home_result <=> $away_result;
    
    if ($pred_sign == $result_sign) {
        return 1;
    }
    
    return 0;
}

// Load user predictions with results
$conn = get_db();
$query = "SELECT m.match_date, t1.team_name as team1, t2.team_name as team2, 
          gp.home_prediction, gp.away_prediction, r.home_score, r.away_score 
          FROM group_predictions gp 
          JOIN matches m ON gp.match_id = m.id 
          JOIN teams t1 ON m.team1_id = t1.id 
          JOIN teams t2 ON m.team2_id = t2.id 
          LEFT JOIN results r ON m.id = r.match_id 
          WHERE gp.user_id = $user_id 
          ORDER BY m.match_date";
$result = $conn->query($query);
$predictions = [];
$total_points = 0;

while ($row = $result->fetch_assoc()) {
    $points = calculate_points($row['home_prediction'], $row['away_prediction'], $row['home_score'], $row['away_score']);
    $row['points'] = $points;
    if ($points !== null) {
        $total_points += $points;
    }
    $predictions[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tips - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>My Tips</h1>
        
        <p><strong>Total Points: <?php echo $total_points; ?></strong></p>
        
        <?php if (empty($predictions)) { ?>
            <p>No predictions yet. <a href="predictions.php">Make predictions</a></p>
        <?php } else { ?>
            <table class="table">
                <tr>
                    <th>Date</th>
                    <th>Match</th>
                    <th>Your Tip</th>
                    <th>Result</th>
                    <th>Points</th>
                </tr>
                <?php foreach ($predictions as $pred) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pred['match_date']); ?></td>
                        <td><?php echo htmlspecialchars($pred['team1'] . " vs " . $pred['team2']); ?></td>
                        <td><?php echo $pred['home_prediction'] . ":" . $pred['away_prediction']; ?></td>
                        <td>
                            <?php if ($pred['home_score'] !== null) { ?>
                                <?php echo $pred['home_score'] . ":" . $pred['away_score']; ?>
                            <?php } else { ?>
                                <em>Pending</em>
                            <?php } ?>
                        </td>
                        <td>
                            <?php 
                            if ($pred['points'] !== null) {
                                echo $pred['points'];
                            } else {
                                echo "—";
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </div>
</body>
</html>