<?php
session_start();

// Clear session data
session_unset();
session_destroy();

// Set logout confirmation message
session_start(); // Restart session to store message
$_SESSION['logout_message'] = "You have been logged out successfully.";

// Redirect to login.php
header("Location: login.php");
exit;
?>