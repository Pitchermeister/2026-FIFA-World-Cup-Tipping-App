<?php
session_start();

// ✅ Nur Admin erlaubt
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$message = "";

// ✅ matches laden (falls Datei fehlt → erstellen)
if (!file_exists("matches.txt")) {
    file_put_contents("matches.txt", "");
}

$matches = [];
$raw = file("matches.txt", FILE_IGNORE_NEW_LINES);
foreach ($raw as $line) {
    if (trim($line) !== "") {
        $matches[] = $line;
    }
}

// ✅ Änderungen speichern
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $output = [];

    foreach ($_POST["match"] as $index => $m) {
        $date   = trim($m["date"]);
        $time   = trim($m["time"]);
        $team1  = trim($m["team1"]);
        $team2  = trim($m["team2"]);
        $group  = trim($m["group"]);

        if ($date !== "" && $time !== "" && $team1 !== "" && $team2 !== "") {
            $output[] = "$date|$time|$team1|$team2|$group";
        }
    }

    file_put_contents("matches.txt", implode("\n", $output) . "\n");

    $matches = $output;
    $message = "✅ Spielplan gespeichert!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Turnierplan – Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f0f0f0; margin:0; }
        .container {
            max-width: 900px; margin:20px auto; background:white;
            padding:30px; border-radius:8px;
            box-shadow:0 2px 4px rgba(0,0,0,0.1);
        }
        table { width:100%; border-collapse:collapse; margin-top:15px; }
        th, td { padding:10px; border-bottom:1px solid #ddd; }
        th { background:#e3f2fd; text-align:left; }
        input { width:120px; padding:5px; }
        .button {
            background:#2e7d32; color:white; padding:10px 20px;
            border:none; border-radius:5px; margin-top:15px; cursor:pointer;
        }
        .button:hover { background:#1b5e20; }
        .msg { color:green; margin-bottom:15px; font-weight:bold; }
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div class="container">
    <h1>🏟️ Turnierplan (Admin)</h1>

    <?php if ($message): ?>
        <p class="msg"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (empty($matches)): ?>
        <p>Noch keine Spiele eingetragen.</p>
    <?php else: ?>

    <form method="POST">
        <table>
            <tr>
                <th>Datum</th>
                <th>Uhrzeit</th>
                <th>Team 1</th>
                <th>Team 2</th>
                <th>Gruppe</th>
            </tr>

            <?php foreach ($matches as $i => $line): ?>
                <?php $m = explode("|", $line); ?>

                <tr>
                    <td><input name="match[<?php echo $i; ?>][date]" value="<?php echo $m[0]; ?>"></td>
                    <td><input name="match[<?php echo $i; ?>][time]" value="<?php echo $m[1]; ?>"></td>
                    <td><input name="match[<?php echo $i; ?>][team1]" value="<?php echo $m[2]; ?>"></td>
                    <td><input name="match[<?php echo $i; ?>][team2]" value="<?php echo $m[3]; ?>"></td>
                    <td><input name="match[<?php echo $i; ?>][group]" value="<?php echo $m[4] ?? ""; ?>"></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <button class="button" type="submit">Speichern</button>
    </form>

    <?php endif; ?>
</div>

</body>
</html>
