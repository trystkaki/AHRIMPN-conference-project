<?php
require "admin/includes/config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

// Collect arguments from CLI
$email       = $argv[1] ?? null;
$first_name  = $argv[2] ?? '';
$last_name   = $argv[3] ?? '';
$designation = $argv[4] ?? '';
$amount      = $argv[5] ?? '';

if (!$email) exit;

$mail = new PHPMailer(true);
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
    $mail->Subject = 'Registration Confirmation';
    $mail->Body = "
        <p>Dear <strong>$first_name $last_name</strong>,</p>
        <p>Thank you for registering for the <strong>AHRIMPN/HRORBN Annual Conference</strong>.</p>
        <p><strong>Registration Type:</strong> {$designation}</p>
        <p><strong>Amount Paid:</strong> ₦" . number_format((float)$amount, 0) . "</p>
        <p>Your receipt has been successfully received.</p>
        <br><p>Warm regards,<br><strong>AHRIMPN/HRORBN Team</strong></p>
    ";

    $mail->send();
} catch (Exception $e) {
    error_log("Email send failed for $email: " . $mail->ErrorInfo);
}

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

$invPdf->Ln(6);

// === Invoice Title ===
$invPdf->SetFont("Times", "B", 25);
$invPdf->SetTextColor(184, 134, 11);
$invPdf->Cell(190, 15, "INVOICE", 0, 1, "C");
$invPdf->Ln(5);

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

// === Table Header (flush with blue border) ===
$invPdf->SetFillColor(0, 51, 102);
$invPdf->SetTextColor(255, 255, 255);
$invPdf->SetFont("Arial", "B", 12);

$tableStartX = 8;   // exact border start
$tableWidth = 194;  // full width inside blue border
$tableWidthDesc = 130;
$tableWidthAmt  = $tableWidth - $tableWidthDesc;

// ✅ Correct Naira symbol using a safe fallback (Latin-1 compatible)
$naira = 'N'; // fallback character
$nairaSymbol = iconv('UTF-8', 'windows-1252', '₦');
if ($nairaSymbol !== false) {
    $naira = $nairaSymbol;
}

// === Header Row ===
$invPdf->SetX($tableStartX);
$invPdf->Cell($tableWidthDesc, 10, "Description", 1, 0, "C", true);
$invPdf->Cell($tableWidthAmt, 10, "Amount ($naira)", 1, 1, "C", true);

// === Table Row ===
$invPdf->SetTextColor(0, 0, 0);
$invPdf->SetFont("Arial", "", 12);
$invPdf->SetX($tableStartX);
$invPdf->Cell($tableWidthDesc, 10, "Conference Registration Fee", 1, 0, "L");

// move amount text slightly inward from right edge
$rightMarginShift = -2; // small inward shift for breathing space
$invPdf->Cell($tableWidthAmt + $rightMarginShift, 10, $naira . " " . number_format($reg['amount'], 2), 1, 1, "R");

// === Total Row ===
$invPdf->SetFont("Arial", "B", 12);
$invPdf->SetFillColor(245, 245, 245);
$invPdf->SetX($tableStartX);
$invPdf->Cell($tableWidthDesc, 10, "TOTAL", 1, 0, "R", true);
$invPdf->Cell($tableWidthAmt + $rightMarginShift, 10, $naira . " " . number_format($reg['amount'], 2), 1, 1, "R", true);
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