<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "admin/includes/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Load PHPMailer
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

// Helper to send JSON and exit
function json_response($status, $message, $type = 'info')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message, 'type' => $type]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // === Sanitize inputs ===
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $designation = trim($_POST['designation'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($designation)) {
        json_response('error', 'All fields are required.', 'danger');
    }

    // === Validate registration type ===
    $stmt = $conn->prepare("SELECT amount FROM registration_fees WHERE CONCAT(category, ' - ', subcategory) = ? LIMIT 1");
    $stmt->bind_param("s", $designation);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        json_response('error', 'Invalid registration type selected.', 'danger');
    }

    $amount = $result->fetch_assoc()['amount'];

    // === Prevent duplicate email ===
    $stmt = $conn->prepare("SELECT id FROM registration WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        json_response('error', 'This email is already registered!', 'danger');
    }

    // === File upload ===
    $uploadDir = __DIR__ . "/assets/uploads/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    if (empty($_FILES['uploadedFile']['name'])) {
        json_response('error', 'Please upload your receipt.', 'danger');
    }

    $fileTmp  = $_FILES['uploadedFile']['tmp_name'];
    $fileName = time() . "_" . preg_replace("/[^A-Za-z0-9_.-]/", "_", $_FILES['uploadedFile']['name']);
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

    if (!in_array($fileExt, $allowed)) {
        json_response('error', 'Invalid file type! Only JPG, PNG, GIF, or PDF allowed.', 'danger');
    }

    if ($_FILES['uploadedFile']['size'] > 5 * 1024 * 1024) {
        json_response('error', 'File too large! Max 5MB allowed.', 'danger');
    }

    if (!move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
        json_response('error', 'Error saving uploaded file.', 'danger');
    }

    // === Insert record ===
    $stmt = $conn->prepare("INSERT INTO registration (first_name, last_name, email, designation, amount, receipt_path)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $first_name, $last_name, $email, $designation, $amount, $fileName);

    if ($stmt->execute()) {
        // === Send confirmation email ===
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($email, "$first_name $last_name");
            $mail->isHTML(true);
            $mail->Subject = 'Registration Received – AHRIMPN/HRORBN Annual Conference';
$mail->Body = "
    <p>Dear <strong>{$first_name} {$last_name}</strong>,</p>

    <p>Your registration for the <strong>AHRIMPN/HRORBN Annual Conference</strong> has been received.</p>

    <p><strong>Registration Type:</strong> {$designation}<br>
    <strong>Amount:</strong> ₦" . number_format($amount, 0) . "</p>

    <p>The administrative team will review your submitted receipt to verify payment. 
    Once confirmed, an offical invoice and confirmation of participation will be sent to your email.</p>

    <p>Thank you for your interest and commitment to the conference.</p>

    <br><p>Warm regards,<br><strong>AHRIMPN/HRORBN Team</strong></p>
";

            $mail->send();
            json_response('success', 'Registration successful! A confirmation email has been sent.', 'success');
        } catch (Exception $e) {
            json_response('warning', 'Registered, but email could not be sent. Error: ' . $mail->ErrorInfo, 'warning');
        }
    } else {
        json_response('error', 'Database error: ' . $conn->error, 'danger');
    }
}

$conn->close();