<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// Check logged-in admin's role
$currentUserRole = $_SESSION['role'] ?? 'viewer';

// Fetch tourism registrations
$result = $conn->query("SELECT * FROM tour_registration ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tourism Registrations - Admin</title>
    <?php include "includes/head.php"; ?>
</head>

<body>
    <div class="d-flex">
        <?php include "sidebar.php"; ?>

        <div class="container-fluid p-4">
            <h2>Tourism Registrations</h2><br>

            <div class="table-responsive">
                <table class="table table-striped table-bordered mt-3 datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Receipt</th>
                            <?php if ($currentUserRole === 'approval' || $currentUserRole === 'superadmin'): ?>
                            <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="row-<?= $row['id']; ?>">
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['phone']); ?></td>
                            <td>₦<?= number_format($row['amount']); ?></td>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <span class="badge 
                                    <?= $row['status'] === 'approved' ? 'bg-success' : 
                                        ($row['status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?= ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['receipt_path'])): ?>
                                <a href="../assets/tour/<?= htmlspecialchars($row['receipt_path']); ?>" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">View</a>
                                <?php else: ?>
                                <span class="text-muted">No file</span>
                                <?php endif; ?>
                            </td>

                            <?php if ($currentUserRole === 'approval' || $currentUserRole === 'superadmin'): ?>
                            <td class="action-cell">
                                <!-- Edit Button -->
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#statusModal<?= $row['id']; ?>">
                                    Edit
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="statusModal<?= $row['id']; ?>" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable modal-top">
                                        <div class="modal-content">
                                            <form method="post" action="update_tour_status.php" class="tourStatusForm">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div id="statusAlert"></div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                    <label for="status<?= $row['id']; ?>" class="form-label">
                                                        Select Status
                                                    </label>
                                                    <select id="status<?= $row['id']; ?>" name="status"
                                                        class="form-select">
                                                        <option value="pending"
                                                            <?= $row['status'] === 'pending' ? 'selected' : ''; ?>>
                                                            Pending
                                                        </option>
                                                        <option value="approved"
                                                            <?= $row['status'] === 'approved' ? 'selected' : ''; ?>>
                                                            Approved
                                                        </option>
                                                        <option value="rejected"
                                                            <?= $row['status'] === 'rejected' ? 'selected' : ''; ?>>
                                                            Rejected
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <?php endif; ?>
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