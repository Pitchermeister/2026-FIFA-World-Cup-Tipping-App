<?php
session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username !== "" && $password !== "") {

        if (file_exists("users.txt")) {
            $users = file("users.txt", FILE_IGNORE_NEW_LINES);

            foreach ($users as $line) {
                $p = explode("|", $line);

                if ($p[0] === $username && password_verify($password, $p[1])) {

                    $_SESSION["user"] = $p[0];
                    $_SESSION["profile"] = $p[2] ?? "";
                    $_SESSION["role"] = $p[3] ?? "user";

                    header("Location: home.php");
                    exit;
                }
            }
        }

        $message = "Login failed!";
    } else {
        $message = "All fields must be filled!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<?php include "nav.php"; ?>

<h2>Login</h2>

<p style="color:red;"><?php echo $message; ?></p>

<form method="POST">
    <label>Username:</label><br>
    <input name="username"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>