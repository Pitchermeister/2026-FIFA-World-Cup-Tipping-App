<?php
session_start();
require 'db.php';

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["user"];
$message = "";
$msgType = ""; // success or danger

// 1. Load User Data from DB
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$currentProfile = $user['profile_picture'] ?? "";

// 2. Handle Image Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile"])) {
    $file = $_FILES["profile"];

    if ($file["error"] === 0) {
        $allowed = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file["type"], $allowed)) {
            $message = "Only JPG, PNG, or GIF allowed!";
            $msgType = "danger";
        } elseif ($file["size"] > $maxSize) {
            $message = "File too large (max 5MB)!";
            $msgType = "danger";
        } else {
            // Prepare folder
            if (!is_dir("uploads")) mkdir("uploads");

            // Generate Filename: user_id.ext (e.g. 5.jpg)
            $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
            $newName = "uploads/" . $user_id . "." . $ext;

            // Delete old file if exists (and different extension)
            if ($currentProfile && file_exists($currentProfile) && $currentProfile !== $newName) {
                unlink($currentProfile);
            }

            if (move_uploaded_file($file["tmp_name"], $newName)) {
                // Update DB
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$newName, $user_id]);

                // Update Session & Local Var
                $_SESSION["profile"] = $newName;
                $currentProfile = $newName;

                $message = "Profile picture updated successfully!";
                $msgType = "success";
            } else {
                $message = "Error saving file.";
                $msgType = "danger";
            }
        }
    } else {
        $message = "Upload error code: " . $file["error"];
        $msgType = "danger";
    }
}

// 3. Handle Delete
if (isset($_POST["delete"])) {
    if ($currentProfile && file_exists($currentProfile)) {
        unlink($currentProfile);
    }

    // Update DB
    $stmt = $pdo->prepare("UPDATE users SET profile_picture = '' WHERE id = ?");
    $stmt->execute([$user_id]);

    // Update Session & Local Var
    $_SESSION["profile"] = "";
    $currentProfile = "";
    
    $message = "Profile picture removed.";
    $msgType = "warning";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .profile-card { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #dee2e6; }
        .profile-img-preview { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #e9ecef; margin-bottom: 20px; }
        .profile-placeholder { width: 150px; height: 150px; border-radius: 50%; background-color: #e9ecef; color: #adb5bd; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 20px auto; border: 4px solid #dee2e6; }
    </style>
</head>
<body>

<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1>👤 My Profile</h1>
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>

    <?php include "nav.php"; ?>

    <div class="profile-card mt-4 text-center">
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h4 class="mb-3"><?= htmlspecialchars($username) ?></h4>

        <!-- Profile Image Display -->
        <?php if ($currentProfile && file_exists($currentProfile)): ?>
            <img src="<?= htmlspecialchars($currentProfile) ?>?t=<?= time() ?>" alt="Profile" class="profile-img-preview">
            <form method="POST" class="mb-4">
                <button type="submit" name="delete" class="btn btn-outline-danger btn-sm">Remove Picture</button>
            </form>
        <?php else: ?>
            <div class="profile-placeholder">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <p class="text-muted mb-4">No profile picture uploaded</p>
        <?php endif; ?>

        <hr>

        <form method="POST" enctype="multipart/form-data" class="text-start">
            <div class="mb-3">
                <label class="form-label fw-bold">Upload New Picture</label>
                <input type="file" name="profile" class="form-control" required accept="image/*">
                <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 5MB.</div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success fw-bold">Save Changes</button>
            </div>
        </form>

    </div>
</div>
</body>
</html>