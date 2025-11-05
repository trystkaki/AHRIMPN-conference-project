<div id="alert-container"></div>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle datatable">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Designation</th>
                <th>Amount</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Action</th>
                <th>Certificate</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $data->fetch_assoc()): ?>
            <?php 
               $designationKey = strtolower($row['designation']);
if (str_contains($designationKey, 'professional')) {
    $designationKey = 'professional';
} elseif (str_contains($designationKey, 'student')) {
    $designationKey = 'student';
} else {
    $designationKey = 'unknown';
}
            ?>
            <tr id="row-<?= $row['id']; ?>" data-status="<?= htmlspecialchars($row['status']); ?>"
                data-locked="<?= $row['status'] === 'approved' ? '1' : '0'; ?>">
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['designation']); ?></td>
                <td>₦<?= number_format($row['amount']); ?></td>
                <td>
                    <a href="../assets/uploads/<?= htmlspecialchars($row['receipt_path']); ?>" target="_blank"
                        class="btn btn-sm btn-outline-secondary">View</a>
                </td>
                <td>
                    <span
                        class="badge 
                        <?= $row['status'] === 'approved' ? 'bg-success' : ($row['status'] === 'unapproved' ? 'bg-danger' : 'bg-warning'); ?>">
                        <?= ucfirst($row['status']); ?>
                    </span>
                </td>

                <!-- ✅ Action Column -->
                <td class="action-cell text-nowrap">
                    <?php 
        $isLocked = ($row['status'] === 'approved' && !empty($row['invoice_sent']) && $row['invoice_sent'] == 1);
    ?>

                    <!-- Edit -->
                    <button class="btn btn-sm btn-outline-secondary me-1 edit-btn"
                        title="<?= $isLocked ? 'Locked – Already Approved' : 'Edit'; ?>"
                        data-bs-toggle="<?= $isLocked ? '' : 'modal'; ?>"
                        data-bs-target="<?= $isLocked ? '' : '#editModal' . $row['id']; ?>" data-id="<?= $row['id']; ?>"
                        data-locked="<?= $isLocked ? '1' : '0'; ?>">
                        <i class="bi <?= $isLocked ? 'bi-lock-fill' : 'bi-pencil'; ?>"></i>
                    </button>


                    <!-- Generate / Regenerate -->
                    <?php if ($row['status'] === 'approved'): ?>
                    <?php if (in_array($_SESSION['role'], ['superadmin', 'approval'])): ?>
                    <?php if (!empty($row['certificate_path'])): ?>
                    <button class="btn btn-sm btn-success" title="Regenerate"
                        onclick="generateCertificate(<?= $row['id']; ?>, '<?= $designationKey; ?>')">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-success" title="Generate"
                        onclick="generateCertificate(<?= $row['id']; ?>, '<?= $designationKey; ?>')">
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


                <!-- ✅ Certificate Column -->
                <td class="cert-cell">
                    <?php if (!empty($row['certificate_path']) && $row['status'] === 'approved'): ?>
                    <a href="../assets/certificates/<?= htmlspecialchars($row['certificate_path']); ?>" target="_blank"
                        class="btn btn-sm btn-outline-secondary">View</a>

                    <form method="post" action="process_certificate_<?= $designationKey; ?>.php"
                        style="display:inline;">
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
                        <form method="post"
                            action="<?= htmlspecialchars('process_certificate_' . $designationKey . '.php'); ?>"
                            class="editForm">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">

                            <div class="modal-header">
                                <h5 class="modal-title">Edit Registration</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
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
                                    <label for="status<?= $row['id']; ?>" class="form-label">Select Status</label>
                                    <select id="status<?= $row['id']; ?>" name="status" class="form-select"
                                        <?= $isLocked ? 'disabled' : ''; ?>>
                                        <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="approved"
                                            <?= $row['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="unapproved"
                                            <?= $row['status'] === 'unapproved' ? 'selected' : ''; ?>>Unapproved
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

<script src="includes/table.js"></script>