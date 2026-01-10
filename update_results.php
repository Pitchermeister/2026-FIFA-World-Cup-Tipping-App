<?php
session_start();
require 'db.php';

// ✅ Only Admin allowed
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$message = "";

// === 1. Load Teams for Dropdown (Prevent Typos) ===
$allTeams = [];
try {
    $stmt = $pdo->query("SELECT name FROM teams ORDER BY name ASC");
    while ($row = $stmt->fetch()) {
        $allTeams[] = $row['name'];
    }
} catch (PDOException $e) {
    // Handle error or empty
}

// === 2. Handle Form Submission (Save Results) ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $count = 0;

    if (isset($_POST['result']) && is_array($_POST['result'])) {
        try {
            $pdo->beginTransaction();
            
            // Prepare statements for performance
            $stmtGroup = $pdo->prepare("UPDATE matches SET score_home = ?, score_away = ? WHERE id = ?");
            $stmtKo    = $pdo->prepare("UPDATE matches SET winner_ko = ? WHERE id = ?");

            foreach ($_POST['result'] as $id => $data) {
                $id = (int)$id;

                // KO Phase: Save Winner
                if ($id > 72) {
                    $winner = trim($data['winner'] ?? '');
                    // Only update if not empty, or allow clearing? Let's allow updating non-empty.
                    // To clear, admin can delete text.
                    $val = ($winner === '') ? null : $winner;
                    $stmtKo->execute([$val, $id]);
                    if ($val) $count++;
                } 
                // Group Phase: Save Scores
                else {
                    $h = $data['home'] ?? '';
                    $a = $data['away'] ?? '';
                    
                    if ($h !== '' && $a !== '') {
                        $stmtGroup->execute([(int)$h, (int)$a, $id]);
                        $count++;
                    } elseif ($h === '' && $a === '') {
                        // Optional: Clear score if both empty
                        $stmtGroup->execute([null, null, $id]);
                    }
                }
            }
            $pdo->commit();
            $message = "✅ Successfully updated matches.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Database Error: " . $e->getMessage();
        }
    }
}

// === 3. Load Matches for Display ===
$matches = [];
try {
    $stmt = $pdo->query("SELECT * FROM matches ORDER BY id ASC");
    $matches = $stmt->fetchAll();
} catch (PDOException $e) {
    $matches = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results – Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 900px; }
        .match-row { background: white; border-bottom: 1px solid #dee2e6; }
        .match-row:last-child { border-bottom: none; }
        .score-input { width: 60px; text-align: center; font-weight: bold; }
        .status-saved { color: #198754; font-weight: bold; font-size: 0.9rem; }
        .status-pending { color: #6c757d; font-size: 0.9rem; font-style: italic; }
        thead { position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Enter Results (Admin)</h2>
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>

    <?php include "nav.php"; ?>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($matches)): ?>
        <div class="alert alert-warning">No matches found in database. Please schedule matches first.</div>
    <?php else: ?>

    <form method="POST">
        <div class="card shadow-sm mt-3">
            <div class="table-responsive" style="max-height: 70vh;">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">ID</th>
                            <th>Group/Stage</th>
                            <th class="text-end">Home / Team 1</th>
                            <th class="text-center" style="width: 200px;">Result</th>
                            <th class="text-start">Away / Team 2</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matches as $m): ?>
                            <?php 
                                $id = $m['id'];
                                $isKo = ($id > 72);
                                
                                // Check if saved
                                $isSaved = false;
                                if ($isKo) {
                                    $isSaved = !empty($m['winner_ko']);
                                } else {
                                    $isSaved = ($m['score_home'] !== null && $m['score_away'] !== null);
                                }
                            ?>
                            <tr class="match-row">
                                <td class="text-center text-muted fw-bold"><?php echo $id; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($m['group_name']); ?></span></td>
                                <td class="text-end"><?php echo htmlspecialchars($m['team1']); ?></td>
                                
                                <td class="text-center">
                                    <?php if ($isKo): ?>
                                        <!-- KO Phase: Winner Selection List (Global Teams) -->
                                        <input class="form-control form-control-sm text-center fw-bold" 
                                               list="allTeamsList" 
                                               name="result[<?php echo $id; ?>][winner]" 
                                               value="<?php echo htmlspecialchars($m['winner_ko'] ?? ''); ?>" 
                                               placeholder="Select Winner...">
                                    <?php else: ?>
                                        <!-- Group Phase: Score Inputs -->
                                        <div class="input-group input-group-sm justify-content-center flex-nowrap">
                                            <input type="number" class="form-control score-input" name="result[<?php echo $id; ?>][home]" value="<?php echo $m['score_home']; ?>" min="0">
                                            <span class="input-group-text border-0 bg-transparent">-</span>
                                            <input type="number" class="form-control score-input" name="result[<?php echo $id; ?>][away]" value="<?php echo $m['score_away']; ?>" min="0">
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="text-start"><?php echo htmlspecialchars($m['team2']); ?></td>
                                <td class="text-center">
                                    <?php if ($isSaved): ?>
                                        <span class="status-saved">✔ Saved</span>
                                    <?php else: ?>
                                        <span class="status-pending">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3 text-center">
                <button class="btn btn-success px-5 fw-bold" type="submit">Save Results</button>
            </div>
        </div>
    </form>

    <!-- Global Datalist for Teams -->
    <datalist id="allTeamsList">
        <?php foreach ($allTeams as $team): ?>
            <option value="<?php echo htmlspecialchars($team); ?>">
        <?php endforeach; ?>
    </datalist>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>