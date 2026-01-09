<?php
session_start();

// Destroy session
$_SESSION = array();
session_destroy();

// Delete remember cookie
setcookie('fifa_remember', '', time() - 3600, '/');

// Redirect to login
header("Location: login.php");
exit();
?>