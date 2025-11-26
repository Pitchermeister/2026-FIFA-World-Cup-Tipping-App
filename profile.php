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
    <title>Profil</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Profil von <?php echo $_SESSION["user"]; ?></h2>

<?php
if ($_SESSION["profile"] !== "") {
    echo '<img src="uploads/' . $_SESSION["profile"] . '" width="150">';
} else {
    echo "<p>Kein Profilbild vorhanden.</p>";
}
?>

</body>
</html>
