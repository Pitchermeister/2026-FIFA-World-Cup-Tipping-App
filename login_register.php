<?php
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $users = file("users.txt", FILE_IGNORE_NEW_LINES);

    foreach ($users as $user) {
        list($u, $p, $role) = explode("|", $user);

        if ($u === $username && $p === $password) {
            $_SESSION["username"] = $u;
            $_SESSION["role"] = $role;
            $message = "Login successful.";
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>

<h1>Login</h1>

<?php echo $message; ?><br><br>

<form method="POST">
    Username:<br>
    <input type="text" name="username"><br><br>

    Password:<br>
    <input type="password" name="password"><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>
