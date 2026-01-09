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

// Save champion prediction
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["champion"])) {
    $champion = trim($_POST["champion"]);
    $_SESSION['champion_prediction'] = $champion;
    $message = "Champion prediction saved: " . htmlspecialchars($champion);
}

$current_champion = $_SESSION['champion_prediction'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K.O. Phase - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>K.O. Phase Predictions</h1>
        
        <?php if ($message) { ?>
            <p class="text-success"><?php echo $message; ?></p>
        <?php } ?>
        
        <h3>Predict World Champion</h3>
        <form method="POST">
            <label>Enter team name:</label>
            <input type="text" name="champion" class="form-control" 
                   value="<?php echo htmlspecialchars($current_champion); ?>" 
                   placeholder="e.g., Brazil, Germany, Argentina" required>
            <br>
            <button type="submit" class="btn btn-primary">Save Prediction</button>
        </form>
        
        <?php if ($current_champion) { ?>
            <hr>
            <h4>Your Current Prediction:</h4>
            <p><strong><?php echo htmlspecialchars($current_champion); ?></strong></p>
        <?php } ?>
        
        <hr>
        <p><em>Note: Full K.O. phase bracket can be added later if needed.</em></p>
    </div>
</body>
</html>