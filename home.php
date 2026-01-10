<?php
session_start();
require_once 'db.php'; // Include DB connection for checking completion status
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Home - FIFA WC 2026 Tipping Game</title>
    <style>
        body { font-family: Arial; background-color: #f0f0f0; margin: 0; }
        .container {
            max-width: 800px; margin: 20px auto; background: white;
            padding: 30px; border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2e7d32; }
        a.button {
            background: #2e7d32; color: white; padding: 10px 20px;
            text-decoration: none; border-radius: 5px; display: inline-block;
            margin-top: 10px;
        }
        a.button:hover { background: #1b5e20; }
        
        /* Blue buttons for Admin to distinguish them */
        a.button.admin { background: #1565c0; }
        a.button.admin:hover { background: #0d47a1; }
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div class="container">
    <h1>🏆 FIFA WC 2026 Tipping Game</h1>

    <!-- 1. Check if ANY user is logged in -->
    <?php if (isset($_SESSION["user"])): ?>
        
        <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION["user"]); ?></strong>!</p>

        <!-- 2. CHECK ROLE: Is this user an Admin? -->
        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
            
            <hr>
            <h3>Admin Dashboard</h3>
            <p>You have administrative privileges.</p>
            <a href="update_results.php" class="button admin">📊 Update Results</a>
            <a href="tournament_schedule.php" class="button admin">🏟️ Manage Schedule</a>
            <a href="teamsetup.php" class="button admin">⚙️ Setup Matches</a>

        <!-- 3. If logged in but NOT admin (Regular User) -->
        <?php else: ?>
            
            <?php 
                // Check if user has FINISHED predictions (Winner of Match 104 is set in DB)
                $isFinished = false;
                if (isset($_SESSION['user_id'])) {
                    try {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tips WHERE user_id = ? AND match_id = 104");
                        $stmt->execute([$_SESSION['user_id']]);
                        if ($stmt->fetchColumn() > 0) {
                            $isFinished = true;
                        }
                    } catch (Exception $e) {
                        // In case of error, default to false (show submit button)
                    }
                }
            ?>

            <?php if ($isFinished): ?>
                <!-- Finished -> Show My Tips -->
                <a href="mytips.php" class="button">📋 My Predictions</a>
            <?php else: ?>
                <!-- Not finished (or not started) -> Show Submit/Resume Button -->
                <a href="predictions.php" class="button">⚽ Submit Predictions</a>
            <?php endif; ?>
            
            <a href="standings.php" class="button">🏆 Standings</a>
            
        <?php endif; ?>

    <!-- 4. Not logged in at all (Guest) -->
    <?php else: ?>
        <p>Predict all WC 2026 matches and collect points!</p>
        <a href="login.php" class="button">🔐 Login</a>
        <a href="register.php" class="button">📝 Register</a>
        <a href="standings.php" class="button">🏆 Standings</a>
    <?php endif; ?>
</div>

</body>
</html>