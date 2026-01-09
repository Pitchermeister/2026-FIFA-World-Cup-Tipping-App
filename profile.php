<?php
session_start();
require_once 'db_config.php';

// Check if logged in
if (!isset($_SESSION["user"]) || !isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_pic"])) {
    $file = $_FILES["profile_pic"];
    
    if ($file["error"] === 0) {
        $allowed = ["jpg", "jpeg", "png"];
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $file["size"] <= 5000000) {
            // Create uploads directory if not exists
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            
            $filename = "profile_" . $user_id . "." . $ext;
            $filepath = "uploads/" . $filename;
            
            if (move_uploaded_file($file["tmp_name"], $filepath)) {
                // Update database
                $conn = get_db();
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $filepath, $user_id);
                $stmt->execute();
                $stmt->close();
                $conn->close();
                
                $_SESSION["profile_pic"] = $filepath;
                $message = "Profile picture uploaded!";
            } else {
                $message = "Upload failed!";
            }
        } else {
            $message = "Invalid file type or size!";
        }
    }
}

// Handle delete picture
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_pic"])) {
    $conn = get_db();
    $stmt = $conn->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    $_SESSION["profile_pic"] = null;
    $message = "Profile picture deleted!";
}

// Get user info
$conn = get_db();
$stmt = $conn->prepare("SELECT username, profile_pic, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h3><a href="home.php">← Back to Home</a></h3>
        <hr>
        
        <h1>My Profile</h1>
        
        <?php if ($message) { ?>
            <p class="text-success"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
        
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($user_data['role']); ?></p>
        <p><strong>Member since:</strong> <?php echo htmlspecialchars($user_data['created_at']); ?></p>
        
        <h3>Profile Picture</h3>
        
        <?php if ($user_data['profile_pic'] && file_exists($user_data['profile_pic'])) { ?>
            <img src="<?php echo htmlspecialchars($user_data['profile_pic']); ?>" width="150" alt="Profile">
            <form method="POST" style="display:inline;">
                <button type="submit" name="delete_pic" class="btn btn-danger btn-sm">Delete Picture</button>
            </form>
        <?php } else { ?>
            <p>No profile picture uploaded.</p>
        <?php } ?>
        
        <h4>Upload New Picture</h4>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_pic" accept=".jpg,.jpeg,.png" required>
            <br><br>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        
        <p class="text-muted">Accepted: JPG, JPEG, PNG. Max 5MB.</p>
    </div>
</body>
</html>