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
        $exists = false;

        foreach ($users as $user) {
            $parts = explode("|", $user);
            if ($parts[0] === $username) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $message = "User existiert bereits!";
        } else {

            $profileImg = "";
            if (!empty($_FILES["profile"]["name"])) {
                $fileName = time() . "_" . basename($_FILES["profile"]["name"]);
                $target = "uploads/" . $fileName;
                move_uploaded_file($_FILES["profile"]["tmp_name"], $target);
                $profileImg = $fileName;
            }

            $file = fopen("users.txt", "a");
            fwrite($file, $username . "|" . password_hash($password, PASSWORD_DEFAULT) . "|" . $profileImg . "\n");
            fclose($file);

            header("Location: login_register.php?registered=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrierung</title>
</head>
<body>
<?php include "nav.php"; ?>

<h2>Registrieren</h2>

<p style="color:red;"><?php echo $message; ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Username:</label>
    <input name="username"><br><br>

    <label>Password:</label>
    <input type="password" name="password"><br><br>

    <label>Profilbild:</label>
    <input type="file" name="profile"><br><br>

    <button type="submit">Registrieren</button>
</form>

</body>
</html>
