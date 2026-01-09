<?php
session_start();
require_once 'db_config.php';

// User only
if (!isset($_SESSION["user"]) || !isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Save predictions
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_predictions"])) {
    $conn = get_db();
    
    foreach ($_POST["predictions"] as $match_id => $scores) {
        $home_pred = intval($scores["home"] ?? 0);
        $away_pred = intval($scores["away"] ?? 0);
        
        if ($home_pred >= 0 && $away_pred >= 0) {
            // Check if prediction exists
            $stmt = $conn->prepare("SELECT id FROM group_predictions WHERE user_id = ? AND match_id = ?");
            $stmt->bind_param("ii", $user_id, $match_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update
                $stmt2 = $conn->prepare("UPDATE group_predictions SET home_prediction = ?, away_prediction = ? WHERE user_id = ? AND match_id = ?");
                $stmt2->bind_param("iiii", $home_pred, $away_pred, $user_id, $match_id);
                $stmt2->execute();
                $stmt2->close();
            } else {
                // Insert
                $stmt2 = $conn->prepare("INSERT INTO group_predictions (user_id, match_id, home_prediction, away_prediction) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("iiii", $user_id, $match_id, $home_pred, $away_pred);
                $stmt2->execute();
                $stmt2->close();
            }
            
            $stmt->close();
        }
    }
    
    $conn->close();
    $message = "Predictions saved!";
}

// Load matches with user predictions
$conn = get_db();
$query = "SELECT m.id, m.match_date, m.match_time, m.group_name, t1.team_name as team1, t2.team_name as team2, gp.home_prediction, gp.away_prediction 
          FROM matches m 
          JOIN teams t1 ON m.team1_id = t1.id 
          JOIN teams t2 ON m.team2_id = t2.id 
          LEFT JOIN group_predictions gp ON m.id = gp.match_id AND gp.user_id = $user_id 
          WHERE m.group_name IS NOT NULL 
          ORDER BY m.match_date, m.match_time";
$result = $conn->query($query);
$matches = [];
while ($row = $result->fetch_assoc()) {
    $matches[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predictions - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>Make Predictions</h1>
        
        <?php if ($message) { ?>
            <p class="text-success"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        
        <form method="POST">
            <table class="table">
                <tr>
                    <th>Date</th>
                    <th>Match</th>
                    <th>Group</th>
                    <th>Your Prediction</th>
                </tr>
                <?php foreach ($matches as $match) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($match['match_date']); ?></td>
                        <td><?php echo htmlspecialchars($match['team1'] . " vs " . $match['team2']); ?></td>
                        <td><?php echo htmlspecialchars($match['group_name']); ?></td>
                        <td>
                            <input type="number" name="predictions[<?php echo $match['id']; ?>][home]" 
                                   value="<?php echo $match['home_prediction'] ?? 0; ?>" 
                                   min="0" max="20" style="width:50px;">
                            :
                            <input type="number" name="predictions[<?php echo $match['id']; ?>][away]" 
                                   value="<?php echo $match['away_prediction'] ?? 0; ?>" 
                                   min="0" max="20" style="width:50px;">
                        </td>
                    </tr>
                <?php } ?>
            </table>
            
            <button type="submit" name="save_predictions" class="btn btn-primary">Save All Predictions</button>
        </form>
    </div>
</body>
</html>