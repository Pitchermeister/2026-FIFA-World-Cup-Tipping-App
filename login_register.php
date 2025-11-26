<?php
session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $message = "Alle Felder müssen ausgefüllt sein!";
    } else {

        $users = file("users.txt", FILE_IGNORE_NEW_LINES);

        foreach ($users as $user) {
            $parts = explode("|", $user);
            if ($parts[0] === $username && password_verify($password, $parts[1])) {
                $_SESSION["user"] = $username;
                $_SESSION["profile"] = $parts[2] ?? "";
                header("Location: home.php");
                exit;
            }
        }

        $message = "Falsche Login-Daten!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Login</h2>

<?php if (isset($_GET["registered"])) echo "<p style='color:green;'>Registrierung erfolgreich!</p>"; ?>
<p style="color:red;"><?php echo $message; ?></p>

<form method="POST">
    <label>Username:</label>
    <input name="username"><br><br>

    <label>Password:</label>
    <input type="password" name="password"><br><br>

    <button type="submit">Login</button>
</form>

<br>
<a href="register.php">Noch keinen Account? Hier registrieren</a>

</body>
</html>
