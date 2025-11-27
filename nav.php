<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div style="margin: 20px;">

<?php if (!isset($_SESSION["user"])): ?>

    <a href="home.php"><button>Home</button></a>
    <a href="login.php"><button>Login</button></a>
    <a href="register.php"><button>Register</button></a>

<?php else: ?>

    <a href="home.php"><button>Home</button></a>
    <a href="profile.php"><button>Profile</button></a>

    <?php if ($_SESSION["role"] === "admin"): ?>
        <a href="tournament_schedule.php"><button>Tournament Schedule</button></a>
        <a href="update_results.php"><button>Results</button></a>
    <?php endif; ?>

    <a href="logout.php"><button>Logout</button></a>

<?php endif; ?>
</div>