<!-- Put this once, near the end of your admin layout (before </body>) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="d-flex min-vh-100">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style="width: 250px;">
        <a href="dashboard.php"
            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Admin Panel</span>
        </a>
        <hr>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link text-white">🏠 Dashboard</a>
            </li>

            <!-- Generate Certificate with Submenu -->
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center text-nowrap" data-bs-toggle="collapse"
                    href="#certMenu" role="button" aria-expanded="false" aria-controls="certMenu">
                    <span class="me-auto">📜 Generate Certificate</span>
                    <span class="caret ms-2">▾</span>
                </a>

                <div class="collapse ms-3 mt-1" id="certMenu">
                    <ul class="list-unstyled fw-normal pb-1 small">
                        <li>
                            <a href="certificate_professional.php" class="nav-link text-white ps-3">👨‍💼
                                Professionals</a>
                        </li>
                        <li>
                            <a href="certificate_student.php" class="nav-link text-white ps-3">🎓 Students</a>
                        </li>

                        <?php if ($_SESSION['role'] === 'superadmin'): ?>
                        <li>
                            <a href="certificate_committee.php" class="nav-link text-white ps-3">🧑‍🤝‍🧑 Committee</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>

            <!-- Tourism Menu -->
            <?php if ($_SESSION['role'] === 'superadmin'): ?>
            <li class="nav-item">
                <a href="tourism.php" class="nav-link text-white">🌍 Tourism</a>
            </li>
            <?php endif; ?>

            <!-- Only superadmin can see Create Admin -->
            <?php if ($_SESSION['role'] === 'superadmin'): ?>
            <li>
                <a href="create_admin.php" class="nav-link text-white">👤 Create Admin</a>
            </li>
            <?php endif; ?>

            <li>
                <a href="logout.php" class="nav-link text-white">🚪 Logout</a>
            </li>
        </ul>
    </div>
</div>

<!-- Caret rotation script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleLink = document.querySelector('[href="#certMenu"]');
    const caret = toggleLink.querySelector(".caret");
    const certMenu = document.getElementById("certMenu");

    certMenu.addEventListener("show.bs.collapse", () => {
        caret.style.transform = "rotate(180deg)";
    });

    certMenu.addEventListener("hide.bs.collapse", () => {
        caret.style.transform = "rotate(0deg)";
    });
});
</script>