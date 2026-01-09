<?php
session_start();
require_once 'db_config.php';

$error = "";
$success = "";

// Process registration
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    if ($username === "" || $password === "") {
        $error = "Please fill in all fields!";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if username exists
        $conn = get_db();
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            // Create new user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt2 = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'user')");
            $stmt2->bind_param("ss", $username, $password_hash);
            
            if ($stmt2->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Registration failed!";
            }
            
            $stmt2->close();
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Register</h1>
        
        <?php if ($error) { ?>
            <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        
        <?php if ($success) { ?>
            <p class="text-success"><?php echo htmlspecialchars($success); ?></p>
        <?php } ?>
        
        <form method="POST" action="register.php">
            <label>Username:</label>
            <input type="text" name="username" class="form-control" required>
            <br>
            
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
            <br>
            
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-secondary">Back to Login</a>
        </form>
    </div>
</body>
</html>