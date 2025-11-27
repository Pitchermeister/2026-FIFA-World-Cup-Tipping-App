<?php
session_start();

// ✅ Only Admin allowed
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$message = "";
$msgType = ""; // success, danger, or warning

// ✅ Storage file
$file = "teams.txt";
if (!file_exists($file)) {
    file_put_contents($file, "");
}

// Define Groups A to L
$groupsList = range('A', 'L');

// ✅ Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $group  = $_POST['group'] ?? '';
    $team   = trim($_POST['team'] ?? '');

    // Load current file content
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // --- LOGIC: ADD TEAM ---
    if ($action === 'add') {
        if ($group !== "" && $team !== "") {
            $exists = false;
            $groupCount = 0;

            // Check duplicates and count teams in this group
            foreach ($lines as $line) {
                $parts = explode("|", $line);
                if (count($parts) >= 2) {
                    if ($parts[0] === $group) {
                        $groupCount++;
                        if (strcasecmp(trim($parts[1]), $team) === 0) {
                            $exists = true;
                        }
                    }
                }
            }

            if ($exists) {
                $message = "Team '$team' is already in Group $group!";
                $msgType = "danger";
            } elseif ($groupCount >= 4) {
                // 🛑 Check: Max 4 teams
                $message = "Group $group is full! (Max 4 teams allowed)";
                $msgType = "danger";
            } else {
                // Save to file
                file_put_contents($file, "$group|$team\n", FILE_APPEND);
                $message = "Success: '$team' added to Group $group.";
                $msgType = "success";
                $team = ""; // Clear input on success
            }
        } else {
            $message = "Please enter a team name.";
            $msgType = "warning";
        }
    }

    // --- LOGIC: REMOVE TEAM ---
    elseif ($action === 'remove') {
        if ($group !== "" && $team !== "") {
            $newLines = [];
            $found = false;

            foreach ($lines as $line) {
                $parts = explode("|", $line);
                if (count($parts) >= 2) {
                    // If this line matches the group AND team (case-insensitive), skip it (delete)
                    if ($parts[0] === $group && strcasecmp(trim($parts[1]), $team) === 0) {
                        $found = true;
                        continue; 
                    }
                }
                $newLines[] = $line;
            }

            if ($found) {
                file_put_contents($file, implode("\n", $newLines) . "\n");
                $message = "Removed '$team' from Group $group.";
                $msgType = "warning"; // Yellow alert for deletion
                $team = ""; // Clear input
            } else {
                $message = "Team '$team' not found in Group $group.";
                $msgType = "danger";
            }
        } else {
            $message = "Please select a team to remove.";
            $msgType = "warning";
        }
    }
}

// ✅ Load Data for Visualization
$teamsData = array_fill_keys($groupsList, []);
// Reload file in case changes were made above
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $parts = explode("|", $line);
    if (count($parts) >= 2) {
        $g = $parts[0];
        $t = $parts[1];
        if (isset($teamsData[$g])) {
            $teamsData[$g][] = $t;
        }
    }
}
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
        
        /* Danger zone styling */
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

    <!-- Success/Error Message -->
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
                                <option value="<?php echo $g; ?>" <?php if(isset($_POST['group']) && $_POST['group'] === $g) echo 'selected'; ?>>
                                    Group <?php echo $g; ?>
                                </option>
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
                        <label class="form-label">Select Group</label>
                        <select class="form-select" name="group" id="removeGroupSelect">
                            <?php foreach ($groupsList as $g): ?>
                                <option value="<?php echo $g; ?>" <?php if(isset($_POST['group']) && $_POST['group'] === $g) echo 'selected'; ?>>
                                    Group <?php echo $g; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Select Team to Remove</label>
                        <!-- Changed from text input to select -->
                        <select class="form-select" name="team" id="removeTeamSelect" required>
                            <option value="">Select Team...</option>
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

<script>
// Pass PHP data to JavaScript
const teamsData = <?php echo json_encode($teamsData); ?>;

const removeGroupSelect = document.getElementById('removeGroupSelect');
const removeTeamSelect = document.getElementById('removeTeamSelect');

function updateRemoveTeamOptions() {
    const selectedGroup = removeGroupSelect.value;
    const teams = teamsData[selectedGroup] || [];

    // Clear existing options
    removeTeamSelect.innerHTML = '<option value="">Select Team...</option>';

    if (teams.length === 0) {
        const option = document.createElement('option');
        option.text = "(No teams in this group)";
        option.disabled = true;
        removeTeamSelect.add(option);
    } else {
        teams.forEach(team => {
            const option = document.createElement('option');
            option.value = team;
            option.text = team;
            removeTeamSelect.add(option);
        });
    }
}

// Event Listener for changes
removeGroupSelect.addEventListener('change', updateRemoveTeamOptions);

// Initialize on page load (in case a group is already selected or default 'A')
document.addEventListener('DOMContentLoaded', updateRemoveTeamOptions);
</script>

</body>
</html>