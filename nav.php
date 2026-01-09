<?php
@session_start();

$logged_in = isset($_SESSION["user"]);
$is_admin = $logged_in && isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">
            <strong>🏆 FIFA WC 2026</strong>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                
                <?php if (!$logged_in) { ?>
                    <!-- Navigation für Gäste -->
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php } ?>

                <?php if ($logged_in) { ?>
                    <!-- Navigation für eingeloggte User -->
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>

                    <?php if ($is_admin) { ?>
                        <!-- Extra Navigation für Admins -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="teamsetup.php">Team Setup</a></li>
                                <li><a class="dropdown-item" href="tournament_schedule.php">Tournament Schedule</a></li>
                                <li><a class="dropdown-item" href="update_results.php">Update Results</a></li>
                            </ul>
                        </li>
                    <?php } ?>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php } ?>
                
            </ul>
        </div>
    </div>
</nav>