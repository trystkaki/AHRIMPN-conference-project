<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// Only allow superadmin or approval roles
if (!in_array($_SESSION['role'], ['superadmin', 'approval'])) {
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => "Access denied."
    ]);
    exit;
}

require_once __DIR__ . "/../lib/fpdf.php";

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'], $_POST['action'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request."
    ]);
    exit;
}

$id = intval($_POST['id']);
$action = $_POST['action'];

// Fetch committee member
$stmt = $conn->prepare("SELECT id, first_name, last_name, status FROM committee WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    exit(json_encode(["status" => "error", "message" => "DB error."]));
}
$stmt->bind_param("i", $id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Validate member
if (!$member || $member['status'] !== 'approved') {
    echo json_encode([
        "status" => "error",
        "message" => "Committee member not eligible for certificate."
    ]);
    exit;
}

// File paths
$fullName = trim($member['first_name'] . " " . $member['last_name']);
$safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($fullName));
$outputDir = __DIR__ . "/../assets/certificates/";

if (!file_exists($outputDir) && !mkdir($outputDir, 0777, true)) {
    error_log("Failed to create certificates directory: $outputDir");
    echo json_encode([
        "status" => "error",
        "message" => "Failed to create certificates directory."
    ]);
    exit;
}

$fileName = "certificate_committee_" . $safeName . ".pdf";
$filePath = $outputDir . $fileName;

try {
    if ($action === "generate") {
        // Generate PDF
        $pdf = new FPDF("L", "mm", "A4");
        $pdf->AddPage();

        $navy = [10, 35, 65];
        $gold = [212, 175, 55];
        $gray = [70, 70, 70];
        $ivory = [255, 253, 240];

        $pdf->SetFillColor(...$ivory);
        $pdf->Rect(0, 0, 297, 210, "F");

        $pdf->SetDrawColor(...$gold);
        $pdf->SetLineWidth(1.6);
        $pdf->Rect(10, 10, 277, 190, "D");

        // Logos
        $leftLogo = __DIR__ . "/../assets/img/logo1.png";
        $rightLogo = __DIR__ . "/../assets/img/logo22.png";
        if (file_exists($leftLogo)) $pdf->Image($leftLogo, 25, 22, 28);
        if (file_exists($rightLogo)) $pdf->Image($rightLogo, 244, 22, 28);

        // Header
        $pdf->Ln(20);
        $pdf->SetFont("Times", "B", 16);
        $pdf->SetTextColor(...$navy);
        $pdf->SetXY(0, 25);
        $pdf->Cell(297, 8, "Association of Health Records and Information Management Practitioners", 0, 1, "C");
        $pdf->Cell(285, 8, "of Nigeria (AHRIMPN)", 0, 1, "C");

        $pdf->SetFont("Times", "I", 13);
        $pdf->SetTextColor(...$gray);
        $pdf->Cell(285, 8, "In Collaboration With", 0, 1, "C");

        $pdf->SetFont("Times", "B", 16);
        $pdf->SetTextColor(...$navy);
        $pdf->Cell(285, 8, "Health Records Officers Registration Board of Nigeria (HRORBN)", 0, 1, "C");

        $pdf->Ln(12);
        $pdf->SetFont("Times", "B", 32);
        $pdf->SetTextColor(...$gold);
        $pdf->Cell(285, 15, "Certificate of Participation", 0, 1, "C");

        $pdf->Ln(10);
        $pdf->SetFont("Arial", "I", 15);
        $pdf->SetTextColor(...$gray);
        $pdf->Cell(285, 10, "This is proudly presented to", 0, 1, "C");

        $pdf->Ln(4);
        $pdf->SetFont("Times", "BI", 40);
        $pdf->SetTextColor(...$navy);
        $pdf->Cell(288, 15, $fullName, 0, 1, "C");

        $pdf->SetDrawColor(...$gold);
        $pdf->SetLineWidth(1);
        $pdf->Line(78, $pdf->GetY() + 2, 210, $pdf->GetY() + 2);

        $pdf->Ln(8);
        $pdf->SetFont("Arial", "I", 15);
        $pdf->SetTextColor(...$gray);
        $pdf->Cell(278, 8, "Committee Member", 0, 1, "C");

        $pdf->Ln(6);
        $pdf->SetFont("Times", "", 13);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(278, 8, "Awarded on " . date("F j, Y"), 0, 1, "C");

        $pdf->SetXY(40, 160);
        $pdf->Cell(80, 8, "________________________", 0, 0, "C");
        $pdf->SetXY(40, 169);
        $pdf->Cell(80, 8, "National President", 0, 0, "C");

        $pdf->SetXY(180, 160);
        $pdf->Cell(80, 8, "________________________", 0, 0, "C");
        $pdf->SetXY(180, 169);
        $pdf->Cell(80, 8, "National Secretary", 0, 0, "C");

        $pdf->Output("F", $filePath);

        // Insert or update certificate in the new table
        $check = $conn->prepare("SELECT id FROM certificates_committee WHERE committee_id = ? LIMIT 1");
    $check->bind_param("i", $id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing) {
        $update = $conn->prepare("UPDATE certificates_committee SET certificate_path = ?, created_at = NOW() WHERE id = ?");
        $update->bind_param("si", $fileName, $existing['id']);
        $update->execute();
        $update->close();
        $message = "Certificate regenerated successfully.";
    } else {
        $insert = $conn->prepare("INSERT INTO certificates_committee (committee_id, certificate_path, created_at) VALUES (?, ?, NOW())");
        $insert->bind_param("is", $id, $fileName);
        $insert->execute();
        $insert->close();
        $message = "Certificate generated successfully.";
    }

    echo json_encode([
        "status" => "success",
        "message" => $message,
        "file" => "assets/certificates/" . $fileName . "?t=" . time()
    ]);
    exit;

    } elseif ($action === "download") {
        if (file_exists($filePath)) {
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=" . basename($filePath));
            readfile($filePath);
            exit;
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Certificate not found."
            ]);
            exit;
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid action."
        ]);
        exit;
    }

} catch (Exception $e) {
    error_log("Committee certificate generation error: " . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => "Error generating certificate: " . $e->getMessage()
    ]);
    exit;
}