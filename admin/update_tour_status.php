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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"], $_POST["status"])) {
    $id = intval($_POST["id"]);
    $status = $_POST["status"];

    $allowed = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed)) {
        echo "invalid";
        exit;
    }

    $stmt = $conn->prepare("UPDATE tour_registration SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    echo $stmt->execute() ? "success" : "error";
    $stmt->close();
} else {
    echo "invalid";
}
?>