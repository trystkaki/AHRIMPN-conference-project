<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// Fetch committee records with any existing certificates
$result = $conn->prepare("
    SELECT cm.*, c.certificate_path
    FROM committee cm
    LEFT JOIN certificates_committee c
        ON cm.id = c.committee_id
    ORDER BY cm.id ASC");
$result->execute();
$data = $result->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Committee Certificates</title>
    <?php include "includes/head.php"; ?>
</head>

<body>
    <div class="d-flex">
        <?php include "sidebar.php"; ?>

        <div class="container-fluid p-4">
            <h2 class="mb-4">Committee Certificates</h2>

            <!-- ✅ Add Committee Member -->
            <div class="form-section mb-5">
                <h5 class="mb-3">Add Committee Member</h5>
                <form action="process_committee.php" method="POST" class="row g-3">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Dr., Mrs., Comr."
                            required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter first name"
                            required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Position</label>
                        <input type="text" name="position" class="form-control" placeholder="e.g. National Treasurer"
                            required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Optional">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Hierarchy</label>
                        <input type="number" name="hierarchy" class="form-control" placeholder="e.g. 1, 2, 3" min="1"
                            required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary fw-bold w-100 py-2">+ Add</button>
                    </div>
                </form>
            </div>

            <!-- ✅ Committee Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $data->fetch_assoc()): ?>
                        <tr id="row-<?= $row['id']; ?>">
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['title'] . " " . $row['first_name'] . " " . $row['last_name']); ?>
                            </td>
                            <td><?= htmlspecialchars($row['position']); ?></td>
                            <td><?= htmlspecialchars($row['phone']); ?></td>

                            <td>
                                <span
                                    class="badge <?= $row['status'] === 'approved' ? 'bg-success' : ($row['status'] === 'unapproved' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?= ucfirst($row['status']); ?>
                                </span>
                            </td>

                            <!-- ✅ Action -->
                            <td class="action-cell text-nowrap">
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"
                                    data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <!--Delete Button -->
                                <button class="btn btn-sm btn-outline-danger me-1" data-bs-toggle="tooltip"
                                    title="Delete" onclick="confirmDeleteCommittee(<?= $row['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>

                                <!-- Generate / Regenerate -->
                                <?php if ($row['status'] === 'approved'): ?>
                                <?php if (in_array($_SESSION['role'], ['superadmin', 'approval'])): ?>
                                <?php if (!empty($row['certificate_path'])): ?>
                                <button class="btn btn-sm btn-success" title="Regenerate"
                                    onclick="generateCommitteeCertificate(<?= $row['id']; ?>)">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-success" title="Generate"
                                    onclick="generateCommitteeCertificate(<?= $row['id']; ?>)">
                                    <i class="bi bi-file-earmark-text"></i>
                                </button>
                                <?php endif; ?>
                                <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled title="Restricted">
                                    <i class="bi bi-lock"></i>
                                </button>
                                <?php endif; ?>
                                <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled title="Not Eligible">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                                <?php endif; ?>

                            </td>

                            <!-- ✅ Certificate -->
                            <td class="cert-cell">
                                <?php if (!empty($row['certificate_path']) && $row['status'] === 'approved'): ?>
                                <a href="../assets/certificates/<?= htmlspecialchars($row['certificate_path']); ?>"
                                    target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                <form method="post" action="process_certificate_committee.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <input type="hidden" name="action" value="download">
                                    <button type="submit" class="btn btn-sm btn-primary">Download</button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted">Not generated</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- ✅ Edit Modal -->
                        <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-top">
                                <div class="modal-content">
                                    <form method="post" action="process_committee.php" class="editForm">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Committee Member</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control"
                                                    value="<?= htmlspecialchars($row['title']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" name="first_name" class="form-control"
                                                    value="<?= htmlspecialchars($row['first_name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" name="last_name" class="form-control"
                                                    value="<?= htmlspecialchars($row['last_name']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Position</label>
                                                <input type="text" name="position" class="form-control"
                                                    value="<?= htmlspecialchars($row['position']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="phone" class="form-control"
                                                    value="<?= htmlspecialchars($row['phone']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Hierarchy</label>
                                                <input type="number" name="hierarchy" class="form-control"
                                                    value="<?= htmlspecialchars($row['hierarchy']); ?>" min="1"
                                                    required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="status<?= $row['id']; ?>" class="form-label">Select
                                                    Status</label>
                                                <select id="status<?= $row['id']; ?>" name="status" class="form-select">
                                                    <option value="pending"
                                                        <?= $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                                                    </option>
                                                    <option value="approved"
                                                        <?= $row['status'] === 'approved' ? 'selected' : ''; ?>>Approved
                                                    </option>
                                                    <option value="unapproved"
                                                        <?= $row['status'] === 'unapproved' ? 'selected' : ''; ?>>
                                                        Unapproved</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="includes/table.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get("success");

        if (success === "added") {
            Swal.fire({
                icon: "success",
                title: "Member Added",
                text: "Committee member added successfully!",
                timer: 2000,
                showConfirmButton: false
            });
        } else if (success === "updated") {
            Swal.fire({
                icon: "success",
                title: "Changes Saved",
                text: "Updated successfully!",
                timer: 2000,
                showConfirmButton: false
            });
        }

        if (success) {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    });
    </script>
</body>

</html>