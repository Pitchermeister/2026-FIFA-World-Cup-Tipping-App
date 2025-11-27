<?php
session_start();

// ✅ Nur Admin erlaubt
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$message = "";

// ✅ matches laden (leere Zeilen entfernen)
$matches = [];
if (file_exists("matches.txt")) {
    $raw = file("matches.txt", FILE_IGNORE_NEW_LINES);
    foreach ($raw as $line) {
        if (trim($line) !== "") {
            $matches[] = $line;
        }
    }
}

// ✅ results laden (falls nicht existiert → erstellen)
if (!file_exists("results.txt")) {
    file_put_contents("results.txt", "");
}
$results = file("results.txt", FILE_IGNORE_NEW_LINES);

// ✅ Ergebnisse speichern
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $output = [];
    foreach ($matches as $i => $m) {
        $home = trim($_POST["result"][$i]["home"] ?? "");
        $away = trim($_POST["result"][$i]["away"] ?? "");

        $output[] = ($home !== "" && $away !== "")
            ? $home . "|" . $away
            : "";
    }

    file_put_contents("results.txt", implode("\n", $output) . "\n");

    $message = "Results saved!";
    $results = $output;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Results – Admin</title>
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
        .button {
            background:#1565c0; color:white; padding:10px 20px;
            border:none; border-radius:5px; margin-top:15px; cursor:pointer;
        }
        .button:hover { background:#0d47a1; }
        .msg { color:green; margin-bottom:15px; }
        .done { color:#2e7d32; font-weight:bold; }
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div class="container">
    <h1>📊 Enter Results (Admin)</h1>

    <?php if ($message): ?>
        <p class="msg"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (empty($matches)): ?>
        <p>No matches available yet.</p>
    <?php else: ?>

    <form method="POST">
        <table>
            <tr>
                <th>Match</th>
                <th>Final Score (H - A)</th>
                <th>Status</th>
            </tr>

            <?php foreach ($matches as $i => $line): ?>
                <?php $m = explode("|", $line); ?>

                <?php
                    $done = isset($results[$i]) && trim($results[$i]) !== "";
                    $existing = $done ? explode("|", $results[$i]) : ["", ""];
                ?>

                <tr>
                    <td><?php echo $m[2] . " vs " . $m[3]; ?></td>

                    <td>
                        <input name="result[<?php echo $i; ?>][home]"
                               value="<?php echo $existing[0]; ?>"
                               style="width:40px;">
                        <strong>-</strong>
                        <input name="result[<?php echo $i; ?>][away]"
                               value="<?php echo $existing[1]; ?>"
                               style="width:40px;">
                    </td>

                    <td class="<?php echo $done ? 'done' : ''; ?>">
                        <?php echo $done ? "✅ saved" : "—"; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <button class="button" type="submit">Save</button>
    </form>

    <?php endif; ?>
</div>

</body>
</html>