<?php
require_once "includes/session_manager.php";
require_once "includes/config.php"; 
checkAdminAuth();

// Check logged-in admin's role
$currentUserRole = $_SESSION['role'] ?? 'viewer';

$result = $conn->query("SELECT * FROM registration ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <?php include "includes/head.php"; ?>
</head>

<body>
    <div class="d-flex">
        <?php include "sidebar.php"; ?>

        <div class="container-fluid p-4">
            <h2>Registrations</h2><br>
            <div class="table-responsive">
                <table class="table table-striped table-bordered mt-3 datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['designation']); ?></td>
                            <td>₦<?= number_format($row['amount']); ?></td>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <span
                                    class="badge 
                                    <?= $row['status'] === 'approved' ? 'bg-success' : ($row['status'] === 'unapproved' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?= ucfirst($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="includes/table.js"></script>
</body>

</html>