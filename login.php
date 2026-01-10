<?php
session_start();
require 'db.php'; // Include database connection

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username !== "" && $password !== "") {
        
        // 1. Prepare Query (Secure against SQL Injection)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // 2. Verify User & Password
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // 3. Set Session Variables
            $_SESSION["user_id"] = $user['id']; // Critical for saving tips later
            $_SESSION["user"]    = $user['username'];
            $_SESSION["role"]    = $user['role'];
            $_SESSION["profile"] = $user['profile_picture'];

            header("Location: home.php");
            exit;
        } else {
            $message = "Invalid username or password!";
        }

    } else {
        $message = "All fields must be filled!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-card { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #dee2e6; }
    </style>
</head>
<body>

<div class="container my-5">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1>🔐 Login</h1>
        <a href="home.php" class="btn btn-primary btn-sm px-3">Home</a>
    </div>

    <?php include "nav.php"; ?>

    <div class="login-card mt-4">
        <h3 class="text-center mb-4">Welcome Back</h3>

        <?php if ($message): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success fw-bold">Login</button>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">Don't have an account? <a href="register.php">Register here</a></small>
            </div>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>