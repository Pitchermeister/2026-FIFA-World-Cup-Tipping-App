<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$username = $_SESSION["user"];
$currentProfile = $_SESSION["profile"] ?? "";

// users.txt laden
$users = file("users.txt", FILE_IGNORE_NEW_LINES);

// Profilbild ändern
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile"])) {

    $file = $_FILES["profile"];

    if ($file["error"] === 0) {

        $allowed = ["image/jpeg", "image/png", "image/jpg"];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file["type"], $allowed)) {
            $message = "Nur JPG oder PNG erlaubt!";
        }
        elseif ($file["size"] > $maxSize) {
            $message = "Datei darf max. 5MB groß sein!";
        } else {
            $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
            $newName = "uploads/" . $username . "." . $ext;

            if (!is_dir("uploads")) {
                mkdir("uploads");
            }

            move_uploaded_file($file["tmp_name"], $newName);

            // users.txt aktualisieren
            foreach ($users as $i => $line) {
                $p = explode("|", $line);
                if ($p[0] === $username) {
                    $p[2] = $newName;
                    $users[$i] = implode("|", $p);
                    break;
                }
            }

            file_put_contents("users.txt", implode("\n", $users) . "\n");

            $_SESSION["profile"] = $newName;
            $currentProfile = $newName;

            $message = "Profilbild aktualisiert!";
        }
    }
}

// Profilbild löschen
if (isset($_POST["delete"])) {

    if ($currentProfile && file_exists($currentProfile)) {
        unlink($currentProfile);
    }

    foreach ($users as $i => $line) {
        $p = explode("|", $line);
        if ($p[0] === $username) {
            $p[2] = "";
            $users[$i] = implode("|", $p);
            break;
        }
    }

    file_put_contents("users.txt", implode("\n", $users) . "\n");

    $_SESSION["profile"] = "";
    $currentProfile = "";
    $message = "Profilbild entfernt!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2e7d32;
        }
        .message {
            color: red;
            margin-bottom: 15px;
        }
        img {
            max-width: 200px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .button {
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background: #1b5e20;
        }
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div class="container">
    <h1>👤 Profil</h1>

    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <p><strong>Benutzername:</strong> <?php echo htmlspecialchars($username); ?></p>

    <h3>Profilbild:</h3>

    <?php if ($currentProfile): ?>
        <img src="<?php echo $currentProfile; ?>" alt="Profilbild"><br>

        <form method="POST">
            <button class="button" name="delete">Profilbild entfernen</button>
        </form>

    <?php else: ?>
        <p>Kein Bild hochgeladen.</p>
    <?php endif; ?>

    <hr>

    <form method="POST" enctype="multipart/form-data">
        <label>Neues Profilbild hochladen:</label><br><br>
        <input type="file" name="profile" required><br><br>
        <button type="submit" class="button">Speichern</button>
    </form>

</div>

</body>
</html>
