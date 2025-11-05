<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// Only allow approval role or superadmin
if (!in_array($_SESSION['role'], ['approval','superadmin'])) {
    http_response_code(403);
    echo "error";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id"] ?? 0);
    $status = $_POST["status"] ?? '';

    $allowed = ['pending', 'approved', 'unapproved'];
    if ($id && in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE registration SET status=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "error";
    }
    exit;
}

header("Location: dashboard.php");
exit;