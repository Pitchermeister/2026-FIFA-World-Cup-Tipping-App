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
    <title>Home</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Willkommen, <?php echo $_SESSION["user"]; ?>!</h2>

</body>
</html>
