<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Home - WM 2026 Tippspiel</title>
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
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div class="container">
    <h1>🏆 FIFA WM 2026 Tippspiel</h1>

    <?php if (isset($_SESSION["user"])): ?>
        <p>Willkommen zurück, <strong><?php echo htmlspecialchars($_SESSION["user"]); ?></strong>!</p>

        <a href="predictions.php" class="button">⚽ Tipps abgeben</a>
        <a href="mytips.php" class="button">📋 Meine Tipps</a>
        <a href="standings.php" class="button">🏆 Rangliste</a>

    <?php else: ?>
        <p>Tippe alle Spiele der WM 2026 und sammle Punkte!</p>
        <a href="login.php" class="button">🔐 Login</a>
        <a href="register.php" class="button">📝 Registrieren</a>
    <?php endif; ?>
</div>

</body>
</html>
