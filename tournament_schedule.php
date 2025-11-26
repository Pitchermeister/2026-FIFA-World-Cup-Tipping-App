<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login_register.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Turnierplan</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Turnierplan</h2>

</body>
</html>
