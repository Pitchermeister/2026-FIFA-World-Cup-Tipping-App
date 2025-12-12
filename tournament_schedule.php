<?php
session_start();

// ✅ Only Admin allowed
if (!isset($_SESSION["user"]) || $_SESSION["role"] !== "admin") {
    header("Location: home.php");
    exit;
}

$message = "";
$msgType = "";

// ✅ Handle Form Submission (Save Match)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date   = $_POST['date'] ?? '';
    $time   = $_POST['time'] ?? '';
    $group  = $_POST['group'] ?? '';
    $team1  = $_POST['team1'] ?? '';
    $team2  = $_POST['team2'] ?? '';

    if ($date && $time && $group && $team1 && $team2) {
        if ($team1 === $team2) {
            $message = "Error: A team cannot play against itself!";
            $msgType = "danger";
        } else {
            // Format: Date|Time|Team1|Team2|Group
            $line = "$date|$time|$team1|$team2|$group\n";
            
            // Append to matches.txt
            file_put_contents("matches.txt", $line, FILE_APPEND);
            
            $message = "Match added successfully: $team1 vs $team2";
            $msgType = "success";
        }
    } else {
        $message = "Please fill in all fields.";
        $msgType = "warning";
    }
}

// ✅ Load Teams for Dropdowns
$teamsByGroup = [];
if (file_exists("teams.txt")) {
    $lines = file("teams.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode("|", $line);
        if (count($parts) >= 2) {
            $g = $parts[0];
            $t = $parts[1];
            $teamsByGroup[$g][] = $t;
        }
    }
}
$groupsList = array_keys($teamsByGroup);
sort($groupsList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tournament Schedule - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1000px; }
        .form-section { background: white; padding: 25px; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
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

    <!-- 1. Add Match Form (Single Row) -->
    <div class="form-section mb-5">
        <h4 class="mb-4 text-success">Add New Match</h4>
        <form method="POST">
            <div class="row g-3 align-items-end">
                
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

                <!-- Group -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Group</label>
                    <select class="form-select" name="group" id="groupSelect" required>
                        <option value="">Select...</option>
                        <?php foreach ($groupsList as $g): ?>
                            <option value="<?php echo $g; ?>">Group <?php echo $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Team 1 -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Team 1</label>
                    <select class="form-select team-select" name="team1" required disabled>
                        <option value="">Select Group First</option>
                    </select>
                </div>

                <!-- Team 2 -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Team 2</label>
                    <select class="form-select team-select" name="team2" required disabled>
                        <option value="">Select Group First</option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100 fw-bold">Add Match</button>
                </div>
            </div>
        </form>
    </div>

</div>

<!-- JavaScript to filter teams based on Group selection -->
<script>
    const teamsByGroup = <?php echo json_encode($teamsByGroup); ?>;
    const groupSelect = document.getElementById('groupSelect');
    const teamSelects = document.querySelectorAll('.team-select');

    groupSelect.addEventListener('change', function() {
        const group = this.value;
        const teams = teamsByGroup[group] || [];

        teamSelects.forEach(select => {
            // Reset options
            select.innerHTML = '<option value="">Select Team...</option>';
            
            if (group) {
                select.disabled = false;
                teams.forEach(team => {
                    const option = document.createElement('option');
                    option.value = team;
                    option.textContent = team;
                    select.appendChild(option);
                });
            } else {
                select.disabled = true;
                select.innerHTML = '<option value="">Select Group First</option>';
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>