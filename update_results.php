<?php
session_start();

if ($_SESSION["role"] !== "admin") {
    echo "Admin only.";
    exit;
}

$matchFile = "matches.txt";
$resultFile = "results.txt";

$matches = file_exists($matchFile) ? file($matchFile, FILE_IGNORE_NEW_LINES) : [];
$results = file_exists($resultFile) ? file($resultFile, FILE_IGNORE_NEW_LINES) : [];

$resultData = [];

foreach ($results as $r) {
    list($id, $a, $b) = explode("|", $r);
    $resultData[$id] = [$a, $b];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST["match_id"];
    $a  = $_POST["score_a"];
    $b  = $_POST["score_b"];
    $resultData[$id] = [$a, $b];

    $out = "";
    foreach ($resultData as $mid => $scores) {
        $out .= "$mid|{$scores[0]}|{$scores[1]}\n";
    }
    file_put_contents($resultFile, $out);
}
?>
<!DOCTYPE html>
<html>
<head><title>Update Results</title></head>
<body>

<h1>Update Results (Admin)</h1>

<table border ="1" cellpadding="6">
<tr>
    <th>ID</th><th>Match</th><th>Score A</th><th>Score B</th><th>Save</th>
</tr>

<?php foreach ($matches as $m): ?>
    <?php list($id,$a,$b,$d,$t,$s) = explode("|",$m); ?>
    <tr>
        <form method="POST">
            <td><?= $id ?></td>
            <td><?= $a ?> vs <?= $b ?></td>
            <td><input type="text" name="score_a" value="<?= $resultData[$id][0] ?? '' ?>" size="3"></td>
            <td><input type="text" name="score_b" value="<?= $resultData[$id][1] ?? '' ?>" size="3"></td>
            <td>
                <input type="hidden" name="match_id" value="<?= $id ?>">
                <button>Save</button>
            </td>
        </form>
    </tr>
<?php endforeach; ?>
</table>

</body>
</html>
