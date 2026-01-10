<?php
session_start();
require 'db.php'; // Include database connection

$message = "";
$msgType = ""; // success or danger

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username !== "" && $password !== "") {

        // 1. Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $message = "User already exists!";
            $msgType = "danger";
        } else {
            // 2. Hash Password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // 3. Insert User
            // We use 'user' as the default role
            $insert = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");
            
            if ($insert->execute([$username, $hash])) {
                $message = "Registration successful! You can now login.";
                $msgType = "success";
            } else {
                $message = "Registration failed. Please try again.";
                $msgType = "danger";
            }
        }

    } else {
        $message = "All fields must be filled!";
        $msgType = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-card { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #dee2e6; }
    </style>
</head>
<body>

<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1>📝 Register</h1>
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>

    <?php include "nav.php"; ?>

    <div class="register-card mt-4">
        <h3 class="text-center mb-4">Create Account</h3>

        <?php if ($message): ?>
            <div class="alert alert-<?= $msgType ?> text-center" role="alert">
                <?= htmlspecialchars($message) ?>
                <?php if($msgType === 'success'): ?>
                    <br><a href="login.php" class="fw-bold">Go to Login</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($msgType !== 'success'): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold">Sign Up</button>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">Already have an account? <a href="login.php">Login here</a></small>
            </div>
        </form>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>