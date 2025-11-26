<div style="margin-bottom:20px;">

    <?php if (!isset($_SESSION["user"])): ?>
        <a href="login_register.php"><button>Login</button></a>
        <a href="register.php"><button>Register</button></a>

    <?php else: ?>
        <a href="home.php"><button>Home</button></a>
        <a href="profile.php"><button>Profil</button></a>

        <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
            <a href="tournament_schedule.php"><button>Turnierplan</button></a>
            <a href="update_results.php"><button>Ergebnisse</button></a>
        <?php endif; ?>

        <a href="logout.php"><button>Logout</button></a>
    <?php endif; ?>

</div>
