// table.js — reusable JS for DataTables, certificates, status updates, and alerts
document.addEventListener("DOMContentLoaded", function () {

    // ------------------------------
    // Initialize all DataTables
    // ------------------------------
    const tables = document.querySelectorAll('.datatable');
    tables.forEach(table => {
        $(table).DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 20, 50],
            ordering: true,
            searching: true
        });
    });

    // ------------------------------
// Certificate generation
// ------------------------------
window.generateCertificate = function (id, designation) {
    let normalized = designation.toLowerCase().replace(/\s+/g, '').replace(/\(.*?\)/, '');

    fetch("process_certificate_" + normalized + ".php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id + "&action=generate"
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                const row = document.getElementById("row-" + id);
                if (!row) return;

                // ✅ Update certificate cell (View + Download)
                row.querySelector(".cert-cell").innerHTML = `
                    <a href="../${data.file}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                    <form method="post" action="process_certificate_${normalized}.php" style="display:inline;">
                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="action" value="download">
                        <button type="submit" class="btn btn-sm btn-primary">Download</button>
                    </form>
                `;

                // ✅ Preserve Edit + Regenerate icons (restore expected structure)
const actionCell = row.querySelector(".action-cell");
const isLocked = row.dataset.status === "approved" || row.dataset.locked === "1";

actionCell.innerHTML = `
    <!-- Edit -->
    <button 
        class="btn btn-sm btn-outline-secondary me-1 edit-btn"
        title="${isLocked ? 'Locked – Already Approved' : 'Edit'}"
        data-id="${id}"
        data-locked="${isLocked ? '1' : '0'}"
        data-bs-toggle="${isLocked ? '' : 'modal'}"
        data-bs-target="${isLocked ? '' : '#editModal' + id}">
        <i class="bi ${isLocked ? 'bi-lock-fill' : 'bi-pencil'}"></i>
    </button>

    <!-- Regenerate -->
    <button class="btn btn-sm btn-success" title="Regenerate"
        onclick="generateCertificate(${id}, '${normalized}')">
        <i class="bi bi-arrow-repeat"></i>
    </button>
`;
// ✅ Rebind lock warning handler (since we replaced innerHTML)
actionCell.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", e => {
        if (btn.dataset.locked === "1") {
            e.preventDefault();
            Swal.fire({
                icon: "warning",
                title: "Locked Record",
                text: "This record has already been approved and cannot be modified.",
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
});

                // ✅ Show success feedback
                Swal.fire({
                    icon: "success",
                    title: "Certificate Generated",
                    text: "Certificate generated successfully!",
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire("Error", data.message || "Failed to generate certificate.", "error");
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire("Error", "Server error.", "error");
        });
};


 // ------------------------------
// Committee Certificate Generation (Dynamic Update)
// ------------------------------
window.generateCommitteeCertificate = function (id) {
    fetch("process_certificate_committee.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id + "&action=generate"
    })
    .then(res => res.json())
    .then(data => {
        const row = document.getElementById("row-" + id);
        if (!row) return;

        if (data.status === "success") {
            // Update certificate cell
            const certCell = row.querySelector(".cert-cell");
            certCell.innerHTML = `
                <a href="../${data.file}" target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                <form method="post" action="process_certificate_committee.php" style="display:inline;">
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="action" value="download">
                    <button type="submit" class="btn btn-sm btn-primary">Download</button>
                </form>
            `;

            // Update action button to "Regenerate"
            const actionCell = row.querySelector(".action-cell");
            const genBtn = actionCell.querySelector("button[title='Generate'], button[title='Regenerate']");
            if (genBtn) {
                genBtn.outerHTML = `
                    <button class="btn btn-sm btn-success" onclick="generateCommitteeCertificate(${id})" title="Regenerate">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                `;
            }

            // Show SweetAlert confirmation
            Swal.fire({
                icon: "success",
                title: "Certificate Generated",
                text: "Committee certificate generated successfully!",
                timer: 2000,
                showConfirmButton: false
            });

        } else {
            Swal.fire("Error", data.message || "Failed to generate certificate.", "error");
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire("Error", "Server error.", "error");
    });
};

// ✅ Make delete function globally available
window.confirmDeleteCommittee = function (id) {
    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete the committee member.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("process_committee.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    action: "delete",
                    id: id
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "Committee member deleted successfully.",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: "Could not delete the record.",
                    });
                }
            });
        }
    });
};

   // ------------------------------
// Edit form handler – detects page type (committee vs others)
// ------------------------------
document.querySelectorAll(".editForm").forEach(form => {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const actionUrl = this.getAttribute("action");
        const modal = this.closest(".modal");
        const modalBody = modal ? modal.querySelector(".modal-body") : null;

        if (!actionUrl || actionUrl.includes("object")) {
            console.error("❌ Invalid form action:", actionUrl);
            if (modalBody) showAlert(modalBody, "⚠️ Form action invalid or missing.", "danger");
            return;
        }

        // 🟡 Show a processing SweetAlert before making the request
        Swal.fire({
            title: "Processing...",
            text: "Saving your changes, please wait.",
            icon: "info",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(actionUrl, { method: "POST", body: formData })
            .then(res => res.text())
            .then(data => {
                Swal.close(); // 🔵 Close loading popup first

                let json = {};
                try {
                    json = JSON.parse(data);
                } catch (e) {
                    json = { status: data.trim() };
                }

                // ✅ Committee type — plain "success" text
                if (actionUrl.includes("process_committee.php")) {
                    if (json.status === "success" || data.trim() === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Changes Saved",
                            text: "Committee member updated successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire("Error", "Failed to update record.", "error");
                    }
                }

                // ✅ Professional / Student — JSON response
                else {
                    if (json.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: json.title || "Update Successful",
                            text: json.message || "Record updated successfully!",
                            timer: 1200,
                            showConfirmButton: false
                        });

                        if (modalBody) {
                            setTimeout(() => {
                                showAlert(modalBody, "✅ Record updated successfully!", "success");

                                // Close modal and reload after short delay
                                setTimeout(() => {
                                    const modalInstance = bootstrap.Modal.getInstance(modal);
                                    if (modalInstance) modalInstance.hide();
                                    location.reload();
                                }, 1000);
                            }, 1000);
                        }
                    } else {
                        Swal.close();
                        if (modalBody)
                            showAlert(modalBody, "⚠️ Failed to update record.", "danger");
                    }
                }
            })
            .catch(err => {
                Swal.close();
                console.error(err);
                if (actionUrl.includes("process_committee.php")) {
                    Swal.fire("Error", "Server error.", "error");
                } else if (modalBody) {
                    showAlert(modalBody, "⚠️ Server error.", "danger");
                }
            });
    });
});

// ------------------------------
// Tourism Status Update (Bootstrap Alert + Reload)
// ------------------------------
document.querySelectorAll(".tourStatusForm").forEach(form => {
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const modal = this.closest(".modal");
        const modalBody = modal ? modal.querySelector(".modal-body") : document.body;

        fetch("update_tour_status.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(response => {

            if (response.trim() === "success") {

                // ✅ Show success alert (Bootstrap)
                modalBody.insertAdjacentHTML("afterbegin", `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Status updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);

                // ✅ Close modal and reload after a short delay
                setTimeout(() => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                    location.reload();
                }, 800);

            } else {
                modalBody.insertAdjacentHTML("afterbegin", `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Failed to update status.
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }
        })
        .catch(() => {
            modalBody.insertAdjacentHTML("afterbegin", `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Server error. Try again later.
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
        });
    });
});

    // ------------------------------
    // Utility: Show alert with fade out
    // ------------------------------
    function showAlert(container, message, type = "success", duration = 3000) {
    // 🧠 Use modal body if provided; fallback only if none given
    const alertContainer = container.closest(".modal-body")
        ? container.closest(".modal-body")
        : document.getElementById("alert-container") || container;

    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mb-3`;
    alertDiv.role = "alert";
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Place alert at top of chosen container
    alertContainer.prepend(alertDiv);

    // Auto fade out
    setTimeout(() => {
        alertDiv.classList.remove("show");
        alertDiv.addEventListener("transitionend", () => {
            if (alertDiv && alertDiv.parentNode) alertDiv.remove();
        });
    }, duration);
}

    // ------------------------------
    // Auto-hide any pre-rendered alerts (like create_admin.php)
    // ------------------------------
    document.querySelectorAll(".alert").forEach(alert => {
        setTimeout(() => {
            alert.classList.remove("show");
            alert.addEventListener("transitionend", () => {
                if (alert && alert.parentNode) alert.parentNode.removeChild(alert);
            });
        }, 3000);
    });

});

// ------------------------------
// Password visibility toggle (global)
// ------------------------------
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("toggle-password")) {
        const icon = e.target;
        const wrapper = icon.closest(".position-relative");
        const input = wrapper ? wrapper.querySelector(".password-field") : null;

        if (!input) return;

        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        }
    }
});

// 🔒 Prevent modal + show alert if record is locked
document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", e => {
        if (btn.dataset.locked === "1") {
            e.preventDefault();

            Swal.fire({
                icon: "warning",
                title: "Locked Record",
                text: "This record has already been approved and cannot be modified.",
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
});
