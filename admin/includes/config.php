<?php
// === DATABASE CONFIGURATION ===
$host = "localhost";
$user = "root";
$pass = ".bones";
$dbname = "ahrimpn_hrorbn";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// === EMAIL CONFIGURATION ===
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'atsemewayusuf@gmail.com');
define('MAIL_PASSWORD', 'taerrvvkdtjfkale');  // 👈 Replace with App Password
define('MAIL_FROM', 'atsemewayusuf@gmail.com');
define('MAIL_FROM_NAME', 'Conference Registration');
?>