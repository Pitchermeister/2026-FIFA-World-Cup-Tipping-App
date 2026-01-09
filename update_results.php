<?php
session_start();
require_once 'db_config.php';

// Admin only
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit();
}

$message = "";

// Update result
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_result"])) {
    $match_id = $_POST["match_id"];
    $home_score = $_POST["home_score"];
    $away_score = $_POST["away_score"];
    
    $conn = get_db();
    
    // Check if result exists
    $stmt = $conn->prepare("SELECT id FROM results WHERE match_id = ?");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update
        $stmt2 = $conn->prepare("UPDATE results SET home_score = ?, away_score = ? WHERE match_id = ?");
        $stmt2->bind_param("iii", $home_score, $away_score, $match_id);
        $stmt2->execute();
        $stmt2->close();
    } else {
        // Insert
        $stmt2 = $conn->prepare("INSERT INTO results (match_id, home_score, away_score) VALUES (?, ?, ?)");
        $stmt2->bind_param("iii", $match_id, $home_score, $away_score);
        $stmt2->execute();
        $stmt2->close();
    }
    
    $stmt->close();
    $conn->close();
    $message = "Result updated!";
}

// Load matches with results
$conn = get_db();
$query = "SELECT m.id, m.match_date, m.match_time, t1.team_name as team1, t2.team_name as team2, r.home_score, r.away_score 
          FROM matches m 
          JOIN teams t1 ON m.team1_id = t1.id 
          JOIN teams t2 ON m.team2_id = t2.id 
          LEFT JOIN results r ON m.id = r.match_id 
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
    <title>Update Results - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>Update Match Results (Admin)</h1>
        
        <?php if ($message) { ?>
            <p class="text-success"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Match</th>
                <th>Result</th>
                <th>Action</th>
            </tr>
            <?php foreach ($matches as $match) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($match['match_date']); ?></td>
                    <td><?php echo htmlspecialchars($match['team1'] . " vs " . $match['team2']); ?></td>
                    <td>
                        <?php if ($match['home_score'] !== null) { ?>
                            <?php echo $match['home_score'] . ":" . $match['away_score']; ?>
                        <?php } else { ?>
                            <em>Not set</em>
                        <?php } ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="match_id" value="<?php echo $match['id']; ?>">
                            <input type="number" name="home_score" value="<?php echo $match['home_score'] ?? 0; ?>" min="0" max="20" style="width:50px;">
                            :
                            <input type="number" name="away_score" value="<?php echo $match['away_score'] ?? 0; ?>" min="0" max="20" style="width:50px;">
                            <button type="submit" name="update_result" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>