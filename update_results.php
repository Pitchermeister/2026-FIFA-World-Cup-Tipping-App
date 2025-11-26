<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$results = file_exists("results.txt")
    ? file("results.txt", FILE_IGNORE_NEW_LINES)
    : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ergebnisse aktualisieren</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Ergebnisse aktualisieren (Admin)</h2>

<ul>
<?php foreach ($results as $r): ?>
    <li><?php echo $r; ?></li>
<?php endforeach; ?>
</ul>

</body>
</html>
