<?php
session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username !== "" && $password !== "") {

        if (!file_exists("users.txt")) {
            file_put_contents("users.txt", "");
        }

        $users = file("users.txt", FILE_IGNORE_NEW_LINES);

        foreach ($users as $line) {
            $p = explode("|", $line);
            if ($p[0] === $username) {
                $message = "Benutzer existiert bereits!";
            }
        }

        if ($message === "") {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            file_put_contents(
                "users.txt",
                $username . "|" . $hash . "||user\n",
                FILE_APPEND
            );

            header("Location: login.php");
            exit;
        }

    } else {
        $message = "Alle Felder müssen ausgefüllt sein!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrieren</title>
</head>
<body>

<?php include "nav.php"; ?>

<h2>Registrieren</h2>

<p style="color:red;"><?php echo $message; ?></p>

<form method="POST">
    <label>Username:</label><br>
    <input name="username"><br><br>

    <label>Passwort:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Account erstellen</button>
</form>

</body>
</html>
