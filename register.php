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
                $message = "User already exists!";
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
        $message = "All fields must be filled!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>

<?php include "nav.php"; ?>

<h2>Register</h2>

<p style="color:red;"><?php echo $message; ?></p>

<form method="POST">
    <label>Username:</label><br>
    <input name="username"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Create Account</button>
</form>

</body>
</html>