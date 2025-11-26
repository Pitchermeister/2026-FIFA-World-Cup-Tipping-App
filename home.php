<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
<?php include "nav.php"; ?>

<h1>2026 FIFA World Cup Tipping App</h1>

<p>Die App ermöglicht dir, Tipps für alle Spiele der WM 2026 abzugeben und Punkte zu sammeln.</p>

<h3>Punktesystem:</h3>
<ul>
    <li>3 Punkte – richtiges Ergebnis</li>
    <li>2 Punkte – richtige Tordifferenz</li>
    <li>1 Punkt – richtige Tendenz</li>
</ul>

<h3>WM 2026 Infos:</h3>
<ul>
    <li>Ausrichter: USA, Kanada, Mexiko</li>
    <li>104 Spiele</li>
    <li>48 Teams</li>
</ul>

<?php if (!isset($_SESSION["user"])): ?>

    <h3>Bitte einloggen:</h3>
    <a href="login_register.php">Login</a><br>
    <a href="register.php">Account erstellen</a>

<?php else: ?>

    <h3>Schnellzugriff:</h3>
    <a href="predictions.php">Predictions</a><br>
    <a href="my_tips.php">My Tips</a><br>
    <a href="standings.php">Standings</a><br>

<?php endif; ?>

</body>
</html>
