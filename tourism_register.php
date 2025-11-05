<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require "admin/includes/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Collect and sanitize inputs
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$amount     = 10000.00; // default/fixed amount
$status     = 'pending';

// Redirect helper
function redirectToIndex($withAnchor = false) {
    $location = "index.php" . ($withAnchor ? "#tour-registration" : "");
    header("Location: $location");
    exit;
}

// === Validation ===
if ($first_name === '' || $last_name === '' || $email === '' || $phone === '') {
    $_SESSION['tour_error'] = "All fields are required.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['tour_error'] = "Invalid email address.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}

// === Check duplicates ===
$checkEmail = $conn->prepare("SELECT id FROM tour_registration WHERE email = ? LIMIT 1");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
if ($checkEmail->get_result()->num_rows > 0) {
    $_SESSION['tour_error'] = "This email has already been registered.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}
$checkEmail->close();

$checkPhone = $conn->prepare("SELECT id FROM tour_registration WHERE phone = ? LIMIT 1");
$checkPhone->bind_param("s", $phone);
$checkPhone->execute();
if ($checkPhone->get_result()->num_rows > 0) {
    $_SESSION['tour_error'] = "This phone number has already been registered.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}
$checkPhone->close();

// === File upload ===
if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['tour_error'] = "Please upload your payment receipt.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}

$file = $_FILES['receipt'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExt, $allowedExtensions)) {
    $_SESSION['tour_error'] = "Invalid file type! Only JPG, PNG, or PDF allowed.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}

if ($file['size'] > 5 * 1024 * 1024) {
    $_SESSION['tour_error'] = "File size too large! Max 5MB allowed.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}

$uploadDir = __DIR__ . "/assets/tour/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$receipt_filename = time() . "_" . basename($file['name']);
$destination = $uploadDir . $receipt_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['tour_error'] = "Error uploading file.";
    $_SESSION['old'] = $_POST;
    redirectToIndex(true);
}

// === Insert record ===
$stmt = $conn->prepare("INSERT INTO tour_registration 
    (first_name, last_name, email, phone, amount, receipt_path, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssiss", $first_name, $last_name, $email, $phone, $amount, $receipt_filename, $status);

if ($stmt->execute()) {
    $_SESSION['tour_success'] = "Thank you, {$first_name}! Your tour registration has been received.";
    unset($_SESSION['old']);
} else {
    $_SESSION['tour_error'] = "Database error: " . $stmt->error;
    $_SESSION['old'] = $_POST;
}

$stmt->close();
redirectToIndex(true);
?>