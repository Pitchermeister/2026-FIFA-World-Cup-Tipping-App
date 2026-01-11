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

// === 1. Handle Form Submission (Save Only) ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id     = (int)($_POST['id'] ?? 0);
    $date   = $_POST['date'] ?? '';
    $time   = $_POST['time'] ?? '';
    $group  = $_POST['group'] ?? ''; 
    $team1  = trim($_POST['team1'] ?? '');
    $team2  = trim($_POST['team2'] ?? '');

    // Validation
    if ($id > 0 && $id <= 104 && $date && $time && $group && $team1 && $team2) {
        if ($team1 === $team2) {
            $message = "Error: A team cannot play against itself!";
            $msgType = "danger";
        } else {
            try {
                // Upsert Logic (Update if ID exists, Insert if new)
                $sql = "INSERT INTO matches (id, date, time, group_name, team1, team2) 
                        VALUES (?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        date = VALUES(date), 
                        time = VALUES(time), 
                        group_name = VALUES(group_name), 
                        team1 = VALUES(team1), 
                        team2 = VALUES(team2)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id, $date, $time, $group, $team1, $team2]);
                
                $message = "Match #$id saved successfully: $team1 vs $team2";
                $msgType = "success";
            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
                $msgType = "danger";
            }
        }
    } else {
        $message = "Please fill in all fields correctly.";
        $msgType = "warning";
    }
}

// === 2. Load ALL Teams for Dropdown (No Filtering) ===
$allTeams = [];
try {
    $stmt = $pdo->query("SELECT name FROM teams ORDER BY name ASC");
    while ($row = $stmt->fetch()) {
        $allTeams[] = $row['name'];
    }
} catch (PDOException $e) {
    // Fallback
}

$groupsList = range('A', 'L'); 
$koStages = ['Round of 32', 'Round of 16', 'Quarter Finals', 'Semi Finals', 'Third Place', 'Final'];

// === 3. Load Existing Matches for Display ===
$matches = [];
try {
    $stmt = $pdo->query("SELECT * FROM matches ORDER BY id ASC");
    // Changed to while loop + fetch() as requested
    while ($row = $stmt->fetch()) {
        $matches[] = $row;
    }
} catch (PDOException $e) {
    $matches = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tournament Schedule - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1200px; } 
        .form-section { background: white; padding: 25px; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .table-container { max-height: 600px; overflow-y: auto; background: white; border-radius: 8px; border: 1px solid #dee2e6; }
        thead { position: sticky; top: 0; z-index: 1; }
    </style>
</head>
<body>

<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🏟️ Tournament Schedule (Admin)</h2>
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>

    <?php include "nav.php"; ?>

    <!-- Message Alert -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show mb-4" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Add/Edit Match Form -->
    <div class="form-section mb-5">
        <h4 class="mb-4 text-success">Schedule Match</h4>
        <form method="POST">
            <div class="row g-2 align-items-end">
                
                <!-- Match ID -->
                <div class="col-md-1">
                    <label class="form-label fw-bold">ID</label>
                    <!-- Removed auto-fill value -->
                    <input type="number" name="id" class="form-control" min="1" max="104" placeholder="#" required>
                </div>

                <!-- Date -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>

                <!-- Time -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Time</label>
                    <input type="time" name="time" class="form-control" required>
                </div>

                <!-- Group / Stage -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Group/Stage</label>
                    <select class="form-select" name="group" required>
                        <option value="">Select...</option>
                        <optgroup label="Group Stage">
                            <?php foreach ($groupsList as $g): ?>
                                <option value="<?php echo $g; ?>">Group <?php echo $g; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Knockout Phase">
                            <?php foreach ($koStages as $stage): ?>
                                <option value="<?php echo $stage; ?>"><?php echo $stage; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <!-- Team 1 -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Team 1</label>
                    <input list="allTeamsList" name="team1" class="form-control" placeholder="Select/Type" required>
                </div>

                <!-- Team 2 -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Team 2</label>
                    <input list="allTeamsList" name="team2" class="form-control" placeholder="Select/Type" required>
                </div>

                <!-- Submit -->
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100 fw-bold">Save</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Global Datalist -->
    <datalist id="allTeamsList">
        <?php foreach ($allTeams as $t): ?>
            <option value="<?php echo htmlspecialchars($t); ?>">
        <?php endforeach; ?>
    </datalist>

    <!-- 2. Existing Matches List -->
    <h4 class="mb-3">Scheduled Matches List</h4>
    <div class="table-container shadow-sm">
        <table class="table table-hover mb-0 align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 80px;">Time</th>
                    <th style="width: 100px;">Group</th>
                    <th class="text-end">Team 1</th>
                    <th class="text-center" style="width: 40px;">vs</th>
                    <th class="text-start">Team 2</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No matches scheduled yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($matches as $mid => $m): ?>
                        <tr>
                            <td class="fw-bold text-center"><?php echo $m['id']; ?></td>
                            <td><?php echo htmlspecialchars($m['date']); ?></td>
                            <td><?php echo htmlspecialchars($m['time']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($m['group_name']); ?></span></td>
                            <td class="text-end"><?php echo htmlspecialchars($m['team1']); ?></td>
                            <td class="text-center text-muted small">-</td>
                            <td class="text-start"><?php echo htmlspecialchars($m['team2']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>