<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "admin/includes/config.php";;
$message = "";
$alertType = "";

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $alertType = $_SESSION['flash_type'];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

// Fetch registration fees
$fees = [];
$feesQuery = $conn->query("SELECT category, subcategory, amount FROM registration_fees ORDER BY id ASC");
while ($row = $feesQuery->fetch_assoc()) {
    $fees[] = $row;
}

// Fetch account details (assuming only one record is active)
$account = $conn->query("SELECT * FROM account_details LIMIT 1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>AHRIMPN/HRORBN Annual Conference</title>
    <?php include 'header.php'; ?>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Hero Start -->
    <div class="container-fluid bg-primary py-5 hero-header mb-5">
        <div class="row py-3">
            <div class="col-12 text-center">
                <h1 class="display-3 text-white animated zoomIn">Register</h1>
                <a href="index.php" class="h4 text-white">Home</a>
                <i class="far fa-circle text-white px-2"></i>
                <a href="register.php" class="h4 text-white">Register</a>
            </div>
        </div>
    </div>
    <!-- Hero End -->

    <!-- Registration Start -->
    <div class="container-fluid bg-primary bg-appointment my-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="row gx-5 align-items-center">
                <!-- Left Column: Registration & Account Details -->
                <div class="col-lg-6 py-5">
                    <div class="py-5 text-white registration-details">
                        <h1 class="display-5 mb-4">Registration Details</h1>

                        <?php
        // Group fees by category
        $groupedFees = [];
        foreach ($fees as $fee) {
            $groupedFees[$fee['category']][] = $fee;
        }
        ?>

                        <?php foreach ($groupedFees as $category => $items): ?>
                        <h3 class="mt-3"><?= htmlspecialchars($category); ?></h3>
                        <ul class="list-unstyled ms-3">
                            <?php foreach ($items as $item): ?>
                            <li>
                                <?= htmlspecialchars($item['subcategory']); ?>:
                                <span class="fw-bold">₦<?= number_format($item['amount'], 0); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endforeach; ?>

                        <br /><br />

                        <h1 class="account-heading1 mb-3">Account Details:</h1>
                        <?php if ($account): ?>
                        <p class="account-info mb-2">
                            <?= htmlspecialchars($account['account_name']); ?>
                        </p>
                        <p class="account-info mb-4">
                            <?= htmlspecialchars($account['bank_name']); ?>:
                            <strong><?= htmlspecialchars($account['account_number']); ?></strong>
                        </p>
                        <?php else: ?>
                        <p class="text-warning">No account details available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Form -->
                <div class="col-lg-6">
                    <div class="appointment-form h-100 d-flex flex-column justify-content-center text-center p-5 wow zoomIn"
                        data-wow-delay="0.6s">
                        <h1 class="account-heading mb-5">Registration Form</h1>

                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show mt-3"
                            role="alert">
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>

                        <form action="save.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="redirect" value="register.php">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <input type="text" name="first_name" class="form-control bg-light border-0"
                                        placeholder="First Name" required />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <input type="text" name="last_name" class="form-control bg-light border-0"
                                        placeholder="Last Name" required />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <input type="email" name="email" class="form-control bg-light border-0"
                                        placeholder="Email" required />
                                </div>
                                <div class="col-12 col-sm-6">
                                    <select id="designation" name="designation" class="form-control bg-light border-0"
                                        required>
                                        <option value="">-- Registration Type --</option>
                                        <?php foreach ($fees as $fee): ?>
                                        <?php $label = $fee['category'] . ' - ' . $fee['subcategory']; ?>
                                        <option value="<?= htmlspecialchars($label); ?>"
                                            data-amount="<?= (int)$fee['amount']; ?>">
                                            <?= htmlspecialchars($fee['category']); ?>
                                            (<?= htmlspecialchars($fee['subcategory']); ?>) -
                                            ₦<?= number_format($fee['amount'], 0); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>

                                <!-- Hidden field for amount -->
                                <input type="hidden" id="amount" name="amount" />

                                <div class="col-12">
                                    <label for="fileInput" class="form-label text-white fw-semibold mb-2">
                                        Upload Receipt:
                                    </label>
                                    <input type="file" id="fileInput" name="uploadedFile"
                                        class="form-control bg-light border-0" accept="image/*, .pdf" required />
                                </div>

                                <div class="col-12">
                                    <button class="btn btn-dark w-100 py-3 mt-3" type="submit">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Registration End -->
    <script>
    window.registrationFees = <?php echo json_encode($fees); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.querySelector('form[action="save.php"]').addEventListener("submit", async function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector("button[type='submit']");
        const originalText = submitBtn.innerHTML;

        // Disable button + show spinner
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Processing...`;

        // Show immediate popup
        Swal.fire({
            title: "Registration in Progress...",
            text: "Please wait while your registration is being processed. Do not close this page.",
            icon: "info",
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(form);

        try {
            const response = await fetch("save.php", {
                method: "POST",
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                } // 👈 key for PHP detection
            });

            const result = await response.json();

            // Show final result
            Swal.fire({
                icon: result.status,
                title: result.title,
                text: result.message,
                showConfirmButton: false,
                timer: 8000
            }).then(() => {
                window.location.href = "register.php";
            });

        } catch (err) {
            Swal.fire("Error", "Something went wrong. Please try again.", "error");
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
    </script>

    <?php include 'footer.php'; ?>
</body>

</html>