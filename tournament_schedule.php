<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$matches = file_exists("matches.txt")
    ? file("matches.txt", FILE_IGNORE_NEW_LINES)
    : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Turnierplan</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Turnierplan (Admin)</h2>

<ul>
<?php foreach ($matches as $m): ?>
    <li><?php echo $m; ?></li>
<?php endforeach; ?>
</ul>

</body>
</html>
