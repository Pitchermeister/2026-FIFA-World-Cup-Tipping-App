<?php
session_start();
require 'db.php';

// ✅ Only Admin allowed
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$message = "";
$msgType = ""; 

$groupsList = range('A', 'L');

// === 1. Handle Form Submission ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $group  = $_POST['group'] ?? '';
    $team   = trim($_POST['team'] ?? '');

    // --- ADD TEAM ---
    if ($action === 'add') {
        if ($group && $team) {
            // Check count in group
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM teams WHERE group_name = ?");
            $stmt->execute([$group]);
            $count = $stmt->fetchColumn();

            if ($count >= 4) {
                $message = "Group $group is full! (Max 4 teams)";
                $msgType = "danger";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO teams (group_name, name) VALUES (?, ?)");
                    $stmt->execute([$group, $team]);
                    $message = "Success: '$team' added to Group $group.";
                    $msgType = "success";
                } catch (PDOException $e) {
                    $message = "Error: Team '$team' likely already exists.";
                    $msgType = "danger";
                }
            }
        }
    }
    // --- REMOVE TEAM ---
    elseif ($action === 'remove') {
        if ($team) {
            // Delete from DB
            $stmt = $pdo->prepare("DELETE FROM teams WHERE name = ?");
            $stmt->execute([$team]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Removed '$team'.";
                $msgType = "warning";
            } else {
                $message = "Team not found.";
                $msgType = "danger";
            }
        }
    }
}

// === 2. Load Data for View ===
$teamsData = array_fill_keys($groupsList, []);
$allTeamsList = [];

// Fetch from DB
try {
    $stmt = $pdo->query("SELECT * FROM teams ORDER BY group_name, name");
    while ($row = $stmt->fetch()) {
        $g = $row['group_name'];
        if (in_array($g, $groupsList)) {
            $teamsData[$g][] = $row['name'];
        }
        $allTeamsList[] = $row['name'];
    }
} catch (PDOException $e) {
    $message = "Database Error: " . $e->getMessage();
    $msgType = "danger";
}
sort($allTeamsList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Setup - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1000px; }
        .group-card { height: 100%; border: 1px solid #dee2e6; }
        .group-header { background-color: #e9ecef; font-weight: bold; text-align: center; padding: 10px; border-bottom: 1px solid #dee2e6; }
        .team-list { list-style: none; padding: 0; margin: 0; }
        .team-list li { padding: 5px 10px; border-bottom: 1px solid #f0f0f0; font-size: 0.9rem; text-align: center; }
        .team-list li:last-child { border-bottom: none; }
        .form-section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); border: 1px solid #dee2e6; margin-bottom: 0px; }
        .form-section.danger-zone { border-color: #f5c6cb; background-color: #fff8f8; }
        .form-section.danger-zone h4 { color: #721c24; }
    </style>
</head>
<body>

<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚙️ Team Setup (Admin)</h2>
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>

    <?php include "nav.php"; ?>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. List of Groups -->
    <h4 class="mt-4 mb-3">Current Groups</h4>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-5 justify-content-center">
        <?php foreach ($groupsList as $g): ?>
            <div class="col">
                <div class="card group-card shadow-sm">
                    <div class="group-header">Group <?php echo $g; ?></div>
                    <div class="card-body p-0">
                        <?php if (empty($teamsData[$g])): ?>
                            <div class="text-center text-muted py-3 small">- Empty -</div>
                        <?php else: ?>
                            <ul class="team-list">
                                <?php foreach ($teamsData[$g] as $t): ?>
                                    <li><?php echo htmlspecialchars($t); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row gy-4">
        <!-- 2. ADD Team Form -->
        <div class="col-12">
            <div class="form-section h-100">
                <h4 class="mb-3 text-success">Add Team</h4>
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="col-12">
                        <label class="form-label">Select Group</label>
                        <select class="form-select" name="group">
                            <?php foreach ($groupsList as $g): ?>
                                <option value="<?php echo $g; ?>">Group <?php echo $g; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Team Name</label>
                        <input type="text" class="form-control" name="team" placeholder="e.g. Argentina" required>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-success w-100">Add Team</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. REMOVE Team Form -->
        <div class="col-12">
            <div class="form-section danger-zone h-100">
                <h4 class="mb-3">Remove Team</h4>
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="remove">
                    
                    <div class="col-12">
                        <label class="form-label">Select Team to Remove</label>
                        <select class="form-select" name="team" required>
                            <option value="">Select Team...</option>
                            <?php foreach ($allTeamsList as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-danger w-100">Remove Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>