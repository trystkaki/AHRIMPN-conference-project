<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>AHRIMPN/HRORBN Annual Conference</title>
    <?php include 'header.php'; ?>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <?php include "admin/includes/config.php"; ?>

    <!-- Hero Start -->
    <div class="container-fluid bg-primary py-5 hero-header mb-5">
        <div class="row py-3">
            <div class="col-12 text-center">
                <h1 class="display-3 text-white animated zoomIn">Team</h1>
                <a href="#" class="h4 text-white">Home</a>
                <i class="far fa-circle text-white px-2"></i>
                <a href="#" class="h4 text-white">Team</a>
            </div>
        </div>
    </div>
    <!-- Hero End -->

    <!-- Team Start -->
    <div class="container-fluid py-5 team-section">
        <div class="container">

            <!-- Divider -->
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h5 class="text-primary text-uppercase mb-2">National Executives</h5>
                    <h1 class="display-6 fw-bold">Our Dedicated Organizers</h1>
                    <div class="title-underline mx-auto"></div>
                </div>
            </div>

            <!-- Organizing Committee Grid -->
            <div class="row g-5">
                <?php
                $result = $conn->query("SELECT * FROM committee where status='approved' ORDER BY hierarchy ASC");
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        $fullName = trim($row['title'] . ' ' . $row['first_name'] . ' ' . $row['last_name']);
                ?>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1"><?= htmlspecialchars($row['position']) ?></p>
                            <h4 class="mb-2"><?= htmlspecialchars($fullName) ?></h4>
                            <p class="text-muted mb-0"><?= htmlspecialchars($row['phone']) ?></p>
                        </div>
                    </div>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                <p class="text-center">No committee members found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Team End -->

    <?php include 'footer.php'; ?>
</body>

</html>