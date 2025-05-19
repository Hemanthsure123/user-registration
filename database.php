<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "login_db";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if (!$conn) {
    error_log("Database connection error: " . mysqli_connect_error());
    die("Error connecting to the database. Please try again later.");
}
?>