<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// Restrict certificate actions to only superadmin or approval
if (!in_array($_SESSION['role'], ['superadmin', 'approval'])) {
    header("HTTP/1.1 403 Forbidden");
    die("Access denied. You are not authorized to generate or download certificates.");
}

// Load FPDF and PHPMailer
require_once __DIR__ . "/../lib/fpdf.php";
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    $stmt = $conn->prepare("SELECT * FROM registration WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        die("Registration not found.");
    }

    $fullName = trim($row['first_name'] . " " . $row['last_name']);
    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($fullName));
    $designation = $row['designation'];
    $designation = preg_replace(['/[^a-z]/i', '/(physical|virtual)/i'], ['', ''], $designation);

     // Check if certificate exists (using new table column)
    $check = $conn->prepare("SELECT * FROM certificates WHERE registration_id = ? LIMIT 1");
    if (!$check) die("Prepare failed: " . $conn->error);
    $check->bind_param("i", $id);
    $check->execute();
    $certResult = $check->get_result();
    $certRow = $certResult->fetch_assoc();
    $check->close();
    
    $outputDir = __DIR__ . "/../assets/certificates/";
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $fileName = "certificate_student_" . $safeName . ".pdf";
    $filePath = $outputDir . $fileName;

    if ($action === "generate") {
        $pdf = new FPDF("L", "mm", "A4");
        $pdf->AddPage();

        // --- Background ---
        $pdf->SetFillColor(255, 255, 255); // white background
        $pdf->Rect(0, 0, 297, 210, "F");

        // Decorative double border
        $pdf->SetDrawColor(34, 85, 136); // deep blue
        $pdf->SetLineWidth(2.5);
        $pdf->Rect(8, 8, 281, 194, "D");
        $pdf->SetDrawColor(200, 170, 50); // gold
        $pdf->SetLineWidth(1);
        $pdf->Rect(10, 10, 277, 190, "D");

        // --- Logos ---
        $leftLogo = __DIR__ . "/../assets/img/logo1.png";
        $rightLogo = __DIR__ . "/../assets/img/logo22.png";
        if (file_exists($leftLogo)) {
            $pdf->Image($leftLogo, 25, 22, 28);
        }
        if (file_exists($rightLogo)) {
            $pdf->Image($rightLogo, 244, 22, 28);
        }

        // --- Organizer heading ---
        $pdf->Ln(20);
        $pdf->SetFont("Times", "B", 16);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->SetXY(0, 25);
        $pdf->Cell(297, 8, "Association of Health Records and Information Management Practitioners", 0, 1, "C");
        $pdf->Cell(285, 8, "of Nigeria (AHRIMPN)", 0, 1, "C");

        // Collaboration line
        $pdf->SetFont("Times", "I", 13);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(285, 8, "In Collaboration With", 0, 1, "C");

        // Second organizer
        $pdf->SetFont("Times", "B", 16);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->Cell(285, 8, "Health Records Officers Registration Board of Nigeria (HRORBN)", 0, 1, "C");

        $pdf->Ln(8);

        // --- Certificate Title ---
        $pdf->SetFont("Times", "B", 34);
        $pdf->SetTextColor(200, 170, 50);
        $pdf->Cell(285, 20, "Certificate of Participation", 0, 1, "C");

        // Subtitle
        $pdf->SetFont("Times", "I", 16);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Ln(6);
        $pdf->Cell(285, 10, "This certificate is proudly presented to", 0, 1, "C");

        // Recipient Name
        $pdf->SetFont("Times", "B", 38);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(4);
        $pdf->Cell(285, 20, $fullName, 0, 1, "C");

        // Designation
        $pdf->SetFont("Times", "I", 20);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->Cell(278, 12, $designation, 0, 1, "C");

        // Awarded Date
        $pdf->SetFont("Times", "", 14);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(6);
        $pdf->Cell(280, 10, "Awarded on " . date("F j, Y"), 0, 1, "C");

        // --- Signatures ---
        $pdf->SetFont("Times", "B", 14);
        $pdf->SetXY(40, 160);
        $pdf->Cell(80, 8, "________________________", 0, 0, "C");
        $pdf->SetXY(40, 169);
        $pdf->Cell(80, 8, "National President", 0, 0, "C");

        $pdf->SetXY(180, 160);
        $pdf->Cell(80, 8, "________________________", 0, 0, "C");
        $pdf->SetXY(180, 169);
        $pdf->Cell(80, 8, "National Secretary", 0, 0, "C");

        // Save PDF
        $pdf->Output("F", $filePath);

        // Save or update DB record
        if ($certRow) {
            $stmt = $conn->prepare("UPDATE certificates SET certificate_path=?, created_at=NOW() WHERE id=?");
            $stmt->bind_param("si", $fileName, $certRow['id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO certificates (registration_id, certificate_path, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $id, $fileName);
        }
        $stmt->execute();
        $stmt->close();

        header("Content-Type: application/json");
        echo json_encode([
            "status" => "success",
            "file" => "assets/certificates/" . $fileName . "?t=" . time()
        ]);
        exit;
    } 
        // =====================================================
    // ACTION: DOWNLOAD CERTIFICATE
    // =====================================================
    elseif ($action === "download") {
        if ($certRow) {
            $filePath = $outputDir . $certRow['certificate_path'];
            if (file_exists($filePath)) {
                header("Content-Type: application/pdf");
                header("Content-Disposition: attachment; filename=" . basename($filePath));
                readfile($filePath);
                exit;
            } else {
                die("Certificate file missing.");
            }
        } else {
            die("No certificate found. Please generate first.");
        }
    }

     // =====================================================
    // ACTION: EDIT REGISTRATION + EMAIL HANDLING
    // =====================================================
    elseif ($action === "edit") {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $status     = trim($_POST['status'] ?? 'pending');

        // 🔒 Prevent editing locked approved records
        $checkLock = $conn->prepare("SELECT status, invoice_sent FROM registration WHERE id = ?");
        $checkLock->bind_param("i", $id);
        $checkLock->execute();
        $lockRes = $checkLock->get_result()->fetch_assoc();
        $checkLock->close();

        if ($lockRes && $lockRes['status'] === 'approved' && intval($lockRes['invoice_sent']) === 1) {
            header("Content-Type: application/json");
            echo json_encode([
                "status" => "locked",
                "title" => "Locked Record",
                "message" => "This record cannot be edited because approval and invoice have already been sent."
            ]);
            exit;
        }

        $originalStatus = $row['status'] ?? null;

        // ✅ Update registration
        $stmt = $conn->prepare("
            UPDATE registration
            SET first_name = ?, last_name = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("sssi", $first_name, $last_name, $status, $id);
        $success = $stmt->execute();
        $stmt->close();

        header("Content-Type: application/json");
        if (!$success) {
            echo json_encode([
                "status" => "error",
                "title" => "Failed to Update",
                "message" => "Could not update record."
            ]);
            exit;
        }

        echo json_encode([
            "status" => "success",
            "title" => "Update Successful",
            "message" => "Record updated successfully."
        ]);

        // --- Email handling
        $regStmt = $conn->prepare("SELECT email, designation, amount, invoice_sent, unapproved_sent FROM registration WHERE id = ?");
        $regStmt->bind_param("i", $id);
        $regStmt->execute();
        $reg = $regStmt->get_result()->fetch_assoc();
        $regStmt->close();

        if ($reg && !empty($reg['email'])) {
            $recipientEmail = $reg['email'];
            $recipientName  = $first_name . ' ' . $last_name;

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
                $mail->addAddress($recipientEmail, $recipientName);
                $mail->isHTML(true);

                // ✅ APPROVED EMAIL
                if ($status === 'approved' && intval($reg['invoice_sent']) === 0 && $originalStatus !== 'approved') {
                    $invoiceDir = __DIR__ . '/../assets/invoices/';
if (!file_exists($invoiceDir)) mkdir($invoiceDir, 0777, true);

$invSafe = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', $first_name . '_' . $last_name . '_' . $id));
$invoiceName = "invoice_{$invSafe}.pdf";
$invoicePath = $invoiceDir . $invoiceName;

$invPdf = new FPDF("P", "mm", "A4");
$invPdf->AddPage();

// === Background ===
$invPdf->SetFillColor(255, 255, 255);
$invPdf->Rect(0, 0, 210, 297, "F");

// === Border (perfectly even margins) ===
$invPdf->SetDrawColor(0, 51, 102); // navy blue
$invPdf->SetLineWidth(1.3);
$invPdf->Rect(8, 8, 194, 281, "D"); // balanced left & right space

// === Logos ===
$leftLogo  = __DIR__ . "/../assets/img/logo1.png";
$rightLogo = __DIR__ . "/../assets/img/logo22.png";
if (file_exists($leftLogo))  $invPdf->Image($leftLogo, 16, 18, 22);
if (file_exists($rightLogo)) $invPdf->Image($rightLogo, 172, 18, 22);

// === Header ===
$invPdf->SetFont("Times", "B", 14);
$invPdf->SetTextColor(0, 51, 102);
$invPdf->SetXY(0, 21);
$invPdf->Cell(210, 8, "Association of Health Records and Information Management", 0, 1, "C");
$invPdf->Cell(210, 8, "Practitioners of Nigeria (AHRIMPN)", 0, 1, "C");

$invPdf->SetFont("Times", "I", 11);
$invPdf->SetTextColor(90, 90, 90);
$invPdf->Cell(208, 8, "In Collaboration With", 0, 1, "C");

$invPdf->SetFont("Times", "B", 14);
$invPdf->SetTextColor(0, 51, 102);
$invPdf->Cell(203, 8, "Health Records Officers Registration Board of Nigeria (HRORBN)", 0, 1, "C");

$invPdf->Ln(8);

// === Invoice Title ===
$invPdf->SetFont("Times", "B", 25);
$invPdf->SetTextColor(184, 134, 11);
$invPdf->Cell(190, 15, "INVOICE", 0, 1, "C");
$invPdf->Ln(7);

// === Invoice Details ===
$invPdf->SetFont("Arial", "", 12);
$invPdf->SetTextColor(0, 0, 0);
$leftMargin = 20;

$invPdf->SetX($leftMargin);
$invPdf->Cell(40, 8, "Invoice No:", 0, 0);
$invPdf->Cell(80, 8, strtoupper("INV-" . date("Y") . "-" . str_pad($id, 4, "0", STR_PAD_LEFT)), 0, 1);

$invPdf->SetX($leftMargin);
$invPdf->Cell(40, 8, "Date Issued:", 0, 0);
$invPdf->Cell(80, 8, date("F j, Y"), 0, 1);

$invPdf->SetX($leftMargin);
$invPdf->Cell(40, 8, "Registrant:", 0, 0);
$invPdf->Cell(120, 8, $first_name . " " . $last_name, 0, 1);

$invPdf->SetX($leftMargin);
$invPdf->Cell(40, 8, "Registration Type:", 0, 0);
$invPdf->Cell(120, 8, $reg['designation'], 0, 1);

$invPdf->Ln(10);

// === Table Header (fills perfectly inside border) ===
$invPdf->SetFillColor(0, 51, 102);
$invPdf->SetTextColor(255, 255, 255);
$invPdf->SetFont("Arial", "B", 12);

$tableStartX = 8;   // align perfectly with left border
$tableWidth = 194;  // full inside border width
$tableWidthDesc = 130;
$tableWidthAmt  = $tableWidth - $tableWidthDesc;

// Proper Naira symbol (works in FPDF)
$naira = "N"; 

// === Header Row ===
$invPdf->SetX($tableStartX);
$invPdf->Cell($tableWidthDesc, 10, "Description", 1, 0, "C", true);
$invPdf->Cell($tableWidthAmt, 10, "Amount ($naira)", 1, 1, "C", true);

// === Table Row ===
$invPdf->SetTextColor(0, 0, 0);
$invPdf->SetFont("Arial", "", 12);

// Text indent *inside* cell (not shifting table)
$descText = "          Conference Registration Fee";

$invPdf->SetX($tableStartX);
$invPdf->Cell($tableWidthDesc, 10, $descText, 1, 0, "L");
$invPdf->Cell($tableWidthAmt, 10, $naira . number_format($reg['amount'], 2), 1, 1, "R");

// === Total Row ===
$invPdf->SetFont("Arial", "B", 12);
$invPdf->SetFillColor(245, 245, 245);
$invPdf->SetX($tableStartX);
$invPdf->Cell($tableWidthDesc, 10, "TOTAL", 1, 0, "R", true);
$invPdf->Cell($tableWidthAmt, 10, $naira . number_format($reg['amount'], 2), 1, 1, "R", true);

$invPdf->Ln(15);

// === Notes ===
$invPdf->SetFont("Arial", "I", 11);
$invPdf->SetTextColor(70, 70, 70);
$invPdf->SetX($leftMargin);
$invPdf->MultiCell(170, 7, "Thank you for your registration. This invoice confirms your registration has been approved for the AHRIMPN/HRORBN Annual Conference. Please retain this invoice for your records.", 0, "L");

$invPdf->Ln(28);

// === Footer / Signatures ===
$invPdf->SetFont("Arial", "B", 12);
$invPdf->SetTextColor(0, 51, 102);
$invPdf->Cell(95, 10, "________________________", 0, 0, "C");
$invPdf->Cell(95, 10, "________________________", 0, 1, "C");
$invPdf->SetFont("Arial", "", 11);
$invPdf->Cell(95, 8, "Finance Officer", 0, 0, "C");
$invPdf->Cell(95, 8, "Conference Secretariat", 0, 1, "C");

// === Output ===
$invPdf->Output("F", $invoicePath);


                $mail->Subject = 'Registration Approved & Invoice – AHRIMPN/HRORBN Annual Conference';
                $mail->Body = "
                    <p>Dear <strong>{$first_name} {$last_name}</strong>,</p>
                    <p>Your registration for the <strong>AHRIMPN/HRORBN Annual Conference</strong> has been approved.</p>
                    <p>Find official invoice attached. Amount: <strong>₦" . number_format($reg['amount'], 2) . "</strong></p>
                    <p>If you require further assistance, kindly contact the secretariat.</p>
                    <br>
                    <p>Warm regards,<br><strong>AHRIMPN/HRORBN Team</strong></p>
                ";
                $mail->addAttachment($invoicePath);
                $mail->send();

                $upd = $conn->prepare("UPDATE registration SET invoice_sent = 1 WHERE id = ?");
                $upd->bind_param("i", $id);
                $upd->execute();
                $upd->close();
            }

            // ✅ UNAPPROVED EMAIL
            elseif ($status === 'unapproved' && intval($reg['unapproved_sent']) === 0 && $originalStatus !== 'unapproved') {
                $mail->Subject = 'Registration Unapproved – AHRIMPN/HRORBN Annual Conference';
                $mail->Body = "
                    <p>Dear <strong>{$first_name} {$last_name}</strong>,</p>
                    <p>We regret to inform you that your registration could not be approved due to issues with payment verification.</p>
                    <p>If you believe this is an error or you have additional proof of payment, please contact the registration committee at <strong>08035614940, 08025164554 or 08059600415</strong> for assistance.</p>
                    <br>
                    <p>Warm regards,<br><strong>AHRIMPN/HRORBN Team</strong></p>
                ";
                $mail->send();

                    $upd = $conn->prepare("UPDATE registration SET unapproved_sent = 1 WHERE id = ?");
                    $upd->bind_param("i", $id);
                    $upd->execute();
                    $upd->close();
                }

            } catch (Exception $e) {
                error_log("Mail error for registration {$id}: " . ($mail->ErrorInfo ?? $e->getMessage()));
            }
        }

        exit;
    }
}
?>