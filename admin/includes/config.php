<?php
// === DATABASE CONFIGURATION ===
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// === EMAIL CONFIGURATION ===
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', '');
define('MAIL_PASSWORD', '');  //  Replace with App Password
define('MAIL_FROM', '');
define('MAIL_FROM_NAME', 'Conference Registration');

?>

