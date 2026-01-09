<?php
session_start();

// Check if logged in
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION["user"];
$role = $_SESSION["role"];
$is_admin = ($role === "admin");

// Get last login from cookie
$last_login = $_COOKIE['fifa_last_login'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3>FIFA WC 2026 Tipping Game</h3>
        <p>Welcome, <strong><?php echo htmlspecialchars($user); ?></strong>!</p>
        
        <?php if ($last_login) { ?>
            <p>Last login: <?php echo htmlspecialchars($last_login); ?></p>
        <?php } ?>
        
        <hr>
        
        <h4>Navigation</h4>
        <p>
            <a href="home.php">Home</a> |
            <a href="profile.php">Profile</a> |
            <a href="predictions.php">Make Predictions</a> |
            <a href="mytips.php">My Tips</a> |
            <a href="standings.php">Standings</a> |
            <?php if ($is_admin) { ?>
                <a href="teamsetup.php">Team Setup</a> |
                <a href="tournament_schedule.php">Tournament Schedule</a> |
                <a href="update_results.php">Update Results</a> |
            <?php } ?>
            <a href="logout.php">Logout</a>
        </p>
        
        <hr>
        
        <h4>About</h4>
        <p>Welcome to the FIFA World Cup 2026 Tipping Game!</p>
        <p>Make predictions for matches and compete with other users.</p>
        
        <h5>Points System:</h5>
        <ul>
            <li>3 points for exact score</li>
            <li>2 points for correct goal difference</li>
            <li>1 point for correct winner</li>
        </ul>
    </div>
</body>
</html>