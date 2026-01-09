<?php
session_start();
require_once 'db_config.php';

// Admin only
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit();
}

$message = "";

// Add match
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_match"])) {
    $date = $_POST["match_date"];
    $time = $_POST["match_time"];
    $team1_id = $_POST["team1_id"];
    $team2_id = $_POST["team2_id"];
    $group = $_POST["group"];
    
    $conn = get_db();
    $stmt = $conn->prepare("INSERT INTO matches (match_date, match_time, team1_id, team2_id, group_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $date, $time, $team1_id, $team2_id, $group);
    
    if ($stmt->execute()) {
        $message = "Match added!";
    } else {
        $message = "Error!";
    }
    
    $stmt->close();
    $conn->close();
}

// Delete match
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_match"])) {
    $match_id = $_POST["match_id"];
    
    $conn = get_db();
    $stmt = $conn->prepare("DELETE FROM matches WHERE id = ?");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    $message = "Match deleted!";
}

// Load teams
$conn = get_db();
$teams = [];
$result = $conn->query("SELECT * FROM teams ORDER BY group_name, team_name");
while ($row = $result->fetch_assoc()) {
    $teams[] = $row;
}

// Load matches
$matches_result = $conn->query("SELECT m.*, t1.team_name as team1, t2.team_name as team2 FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id ORDER BY m.match_date, m.match_time");
$matches = [];
while ($row = $matches_result->fetch_assoc()) {
    $matches[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Schedule - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>Tournament Schedule (Admin)</h1>
        
        <?php if ($message) { ?>
            <p class="text-success"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        
        <h3>Add New Match</h3>
        <form method="POST">
            <label>Date:</label>
            <input type="date" name="match_date" class="form-control" required>
            <br>
            <label>Time:</label>
            <input type="time" name="match_time" class="form-control" required>
            <br>
            <label>Team 1:</label>
            <select name="team1_id" class="form-control" required>
                <?php foreach ($teams as $team) { ?>
                    <option value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                <?php } ?>
            </select>
            <br>
            <label>Team 2:</label>
            <select name="team2_id" class="form-control" required>
                <?php foreach ($teams as $team) { ?>
                    <option value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                <?php } ?>
            </select>
            <br>
            <label>Group:</label>
            <input type="text" name="group" class="form-control" maxlength="1" required>
            <br>
            <button type="submit" name="add_match" class="btn btn-primary">Add Match</button>
        </form>
        
        <hr>
        
        <h3>Current Matches</h3>
        <table class="table">
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Match</th>
                <th>Group</th>
                <th>Action</th>
            </tr>
            <?php foreach ($matches as $match) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($match['match_date']); ?></td>
                    <td><?php echo htmlspecialchars($match['match_time']); ?></td>
                    <td><?php echo htmlspecialchars($match['team1'] . " vs " . $match['team2']); ?></td>
                    <td><?php echo htmlspecialchars($match['group_name']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="match_id" value="<?php echo $match['id']; ?>">
                            <button type="submit" name="delete_match" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>