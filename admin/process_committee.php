<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

$action = $_POST['action'] ?? '';

if ($action === "delete" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);
    $stmt = $conn->prepare("DELETE FROM committee WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo $stmt->execute() ? "success" : "error";
    $stmt->close();
    exit;
}

$title = trim($_POST['title'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$position = trim($_POST['position'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$hierarchy = intval($_POST['hierarchy'] ?? 0);

if (empty($title) || empty($first_name) || empty($last_name) || empty($position)) {
    exit("Required fields missing.");
}

if ($action === "add") {
    // Regular form submission (non-AJAX)
    $stmt = $conn->prepare("
        INSERT INTO committee (title, first_name, last_name, position, phone, hierarchy, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("sssssi", $title, $first_name, $last_name, $position, $phone, $hierarchy);
    $stmt->execute();
    $stmt->close();

    header("Location: certificate_committee.php?success=added");
    exit;

} elseif ($action === "edit") {
    // AJAX submission
    $id = intval($_POST['id']);
    $status = $_POST['status'] ?? 'pending';

    $stmt = $conn->prepare("
        UPDATE committee 
        SET title=?, first_name=?, last_name=?, position=?, phone=?, hierarchy=?, status=?, updated_at=NOW()
        WHERE id=?
    ");
    $stmt->bind_param("sssssssi", $title, $first_name, $last_name, $position, $phone, $hierarchy, $status, $id);
    $success = $stmt->execute();
    $stmt->close();

    echo $success ? "success" : "error";
    exit;
}

exit("Invalid action");
?>