<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login_register.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["remove"])) {
        $_SESSION["profile"] = "";
        $lines = file("users.txt", FILE_IGNORE_NEW_LINES);
        $out = [];

        foreach ($lines as $line) {
            $p = explode("|", $line);
            if ($p[0] === $_SESSION["user"]) {
                $out[] = $p[0] . "|" . $p[1] . "||" . $p[3];
            } else {
                $out[] = $line;
            }
        }

        file_put_contents("users.txt", implode("\n", $out) . "\n");
    }

    if (isset($_FILES["profile"]["name"]) && $_FILES["profile"]["name"] !== "") {

        $allowed = ["image/jpeg", "image/png"];
        $type = mime_content_type($_FILES["profile"]["tmp_name"]);
        $size = $_FILES["profile"]["size"];

        if (!in_array($type, $allowed)) {
            $message = "Nur JPG und PNG erlaubt.";
        } elseif ($size > 5 * 1024 * 1024) {
            $message = "Datei zu groß (max 5MB).";
        } else {
            $fileName = time() . "_" . basename($_FILES["profile"]["name"]);
            move_uploaded_file($_FILES["profile"]["tmp_name"], "uploads/" . $fileName);
            $_SESSION["profile"] = $fileName;

            $lines = file("users.txt", FILE_IGNORE_NEW_LINES);
            $out = [];

            foreach ($lines as $line) {
                $p = explode("|", $line);
                if ($p[0] === $_SESSION["user"]) {
                    $out[] = $p[0] . "|" . $p[1] . "|" . $fileName . "|" . $p[3];
                } else {
                    $out[] = $line;
                }
            }

            file_put_contents("users.txt", implode("\n", $out) . "\n");
        }
    }
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

<?php if ($message !== "") echo "<p style='color:red;'>$message</p>"; ?>

<?php
if ($_SESSION["profile"] !== "") {
    echo '<img src="uploads/' . $_SESSION["profile"] . '" width="150"><br><br>';
}
?>

<form method="POST" enctype="multipart/form-data">
    <label>Neues Profilbild:</label>
    <input type="file" name="profile"><br><br>
    <button type="submit">Upload</button>
</form>

<br>

<form method="POST">
    <button type="submit" name="remove">Profilbild entfernen</button>
</form>

</body>
</html>
