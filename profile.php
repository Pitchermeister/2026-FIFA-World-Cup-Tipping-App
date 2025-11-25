<?php
session_start();

if (!isset($_SESSION["username"])) {
    echo "Please login first.";
    exit;
}

$user = $_SESSION["username"];
$file = "profiles/" . $user . ".txt";

if (!file_exists("profiles")) {
    mkdir("profiles");
}

$email = "";
$team = "";

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        if (str_starts_with($line, "email=")) $email = substr($line, 6);
        if (str_starts_with($line, "team=")) $team = substr($line, 5);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $team = $_POST["team"] ?? "";

    $data = "email=$email\nteam=$team\n";
    file_put_contents($file, $data);
}
?>
<!DOCTYPE html>
<html>
<head><title>Profile</title></head>
<body>

<h1>My Profile</h1>

<form method="POST">
    Email:<br>
    <input type="text" name="email" value="<?php echo $email; ?>"><br><br>

    Favourite Team:<br>
    <input type="text" name="team" value="<?php echo $team; ?>"><br><br>

    <button type="submit">Save</button>
</form>

</body>
</html>
