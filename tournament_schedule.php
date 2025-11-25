<?php
session_start();

if ($_SESSION["role"] !== "admin") {
    echo "Admin only.";
    exit;
}

$file = "matches.txt";
$matches = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES) : [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = count($matches) + 1;
    $teamA = $_POST["team_a"];
    $teamB = $_POST["team_b"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $stage = $_POST["stage"];

    $line = "$id|$teamA|$teamB|$date|$time|$stage\n";
    file_put_contents($file, $line, FILE_APPEND);
}
?>
<!DOCTYPE html>
<html>
<head><title>Tournament Schedule</title></head>
<body>

<h1>Tournament Schedule (Admin)</h1>

<table border="1" cellpadding="6">
<tr>
    <th>ID</th><th>Team A</th><th>Team B</th><th>Date</th><th>Time</th><th>Stage</th>
</tr>

<?php foreach ($matches as $m): ?>
    <?php list($id,$a,$b,$d,$t,$s) = explode("|",$m); ?>
    <tr>
        <td><?= $id ?></td>
        <td><?= $a ?></td>
        <td><?= $b ?></td>
        <td><?= $d ?></td>
        <td><?= $t ?></td>
        <td><?= $s ?></td>
    </tr>
<?php endforeach; ?>
</table>

<br><br>

<h3>Add Match</h3>

<form method="POST">
    Team A:<br><input type="text" name="team_a"><br><br>
    Team B:<br><input type="text" name="team_b"><br><br>
    Date:<br><input type="text" name="date"><br><br>
    Time:<br><input type="text" name="time"><br><br>
    Stage:<br><input type="text" name="stage"><br><br>

    <button>Add</button>
</form>

</body>
</html>
