<?php
session_start();
require_once 'db_config.php';

// Admin only
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit();
}

$message = "";

// Add team
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_team"])) {
    $group = strtoupper(trim($_POST["group"] ?? ""));
    $team_name = trim($_POST["team_name"] ?? "");
    
    if ($group !== "" && $team_name !== "") {
        $conn = get_db();
        $stmt = $conn->prepare("INSERT INTO teams (group_name, team_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $group, $team_name);
        
        if ($stmt->execute()) {
            $message = "Team added!";
        } else {
            $message = "Error adding team!";
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Delete team
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_team"])) {
    $team_id = $_POST["team_id"];
    
    $conn = get_db();
    $stmt = $conn->prepare("DELETE FROM teams WHERE id = ?");
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    $message = "Team deleted!";
}

// Load all teams
$conn = get_db();
$result = $conn->query("SELECT * FROM teams ORDER BY group_name, team_name");
$teams = [];
while ($row = $result->fetch_assoc()) {
    $teams[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Setup - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>Team Setup (Admin)</h1>
        
        <?php if ($message) { ?>
            <p class="text-success"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        
        <h3>Add New Team</h3>
        <form method="POST">
            <label>Group (A-L):</label>
            <input type="text" name="group" class="form-control" maxlength="1" required>
            <br>
            <label>Team Name:</label>
            <input type="text" name="team_name" class="form-control" required>
            <br>
            <button type="submit" name="add_team" class="btn btn-primary">Add Team</button>
        </form>
        
        <hr>
        
        <h3>Current Teams</h3>
        <table class="table">
            <tr>
                <th>Group</th>
                <th>Team Name</th>
                <th>Action</th>
            </tr>
            <?php foreach ($teams as $team) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($team['group_name']); ?></td>
                    <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                            <button type="submit" name="delete_team" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>