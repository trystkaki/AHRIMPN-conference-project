<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// Fetch only student leaders
$result = $conn->prepare("
    SELECT r.*, c.certificate_path 
    FROM registration r 
    LEFT JOIN certificates c ON r.id = c.registration_id
    WHERE r.designation LIKE ?
    ORDER BY r.id ASC");
    
$designation = "%Student%";
$result->bind_param("s", $designation);
$result->execute();
$data = $result->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Certificates</title>
    <?php include "includes/head.php"; ?>
</head>

<body>
    <div class="d-flex">
        <?php include "sidebar.php"; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4">Student Certificates</h2>
            <?php include "certificate_table.php"; ?>
        </div>
    </div>
</body>

</html>