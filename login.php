<?php
session_start();
require_once 'db_config.php';

$error = "";
$remembered_user = $_COOKIE['fifa_remember'] ?? '';

// Process login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $remember = isset($_POST["remember"]);
    
    if ($username === "" || $password === "") {
        $error = "Please fill in all fields!";
    } else {
        // Get user from database
        $conn = get_db();
        $stmt = $conn->prepare("SELECT id, username, password_hash, role, profile_pic FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION["user"] = $user['username'];
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["role"] = $user['role'];
                $_SESSION["profile_pic"] = $user['profile_pic'];
                
                // Update last login
                $stmt2 = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt2->bind_param("i", $user['id']);
                $stmt2->execute();
                
                // Remember me cookie
                if ($remember) {
                    setcookie('fifa_remember', $username, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                } else {
                    setcookie('fifa_remember', '', time() - 3600, '/');
                }
                
                // Last login cookie
                setcookie('fifa_last_login', date('Y-m-d H:i:s'), time() + (365 * 24 * 60 * 60), '/', '', false, true);
                
                header("Location: home.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
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
    <title>Login - FIFA WC 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Login</h1>
        
        <?php if ($error) { ?>
            <p class="text-danger"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
        
        <form method="POST" action="login.php">
            <label>Username:</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($remembered_user); ?>" required>
            <br>
            
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
            <br>
            
            <label>
                <input type="checkbox" name="remember"> Remember me
            </label>
            <br><br>
            
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </form>
    </div>
</body>
</html>