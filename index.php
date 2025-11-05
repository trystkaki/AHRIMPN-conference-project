<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "admin/includes/config.php";

// Fetch account details (assuming only 1 active record for now)
$account = $conn->query("SELECT * FROM account_details LIMIT 1")->fetch_assoc();

// Fetch tour registration amount dynamically from the tour_registration table
$tour_amount_query = $conn->query("SELECT amount FROM tour_registration ORDER BY id DESC LIMIT 1");
$tour_amount = $tour_amount_query && $tour_amount_query->num_rows > 0 
    ? $tour_amount_query->fetch_assoc()['amount'] 
    : 10000.00; // fallback if table empty

// Fetch all registration fees
$feesQuery = $conn->query("SELECT category, subcategory, amount FROM registration_fees ORDER BY category, subcategory");

$fees = [];
while ($row = $feesQuery->fetch_assoc()) {
    $fees[$row['category']][] = $row;  // group by category
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>AHRIMPN/HRORBN Annual Conference</title>
    <?php include 'header.php'; ?>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Carousel Start (IMPROVED) -->
    <div class="container-fluid p-0">
        <div id="header-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
            <div class="carousel-inner">

                <!-- First Slide -->
                <div class="carousel-item active" style="position:relative;">
                    <img class="w-100" src="assets/img/carousel-1.jpg" alt="Conference Banner">
                    <div class="carousel-overlay"></div>

                    <div class="carousel-caption custom-caption slide-one-caption">
                        <div class="caption-inner text-center">

                            <!-- Organizers (desktop) -->
                            <div class="organizers-full d-none d-md-block mb-3 text-center">
                                <div class="organizer">
                                    Association of Health Records and Information Management
                                    Practitioners of Nigeria (AHRIMPN)
                                </div>
                                <div class="collab">
                                    In Collaboration With
                                </div>
                                <div class="organizer">
                                    Health Records Officers Registration Board of Nigeria
                                    (HRORBN)
                                </div>
                            </div>

                            <!-- Organizers (mobile full names) -->
                            <div class="organizers-full-mobile d-block d-md-none mb-3 text-center">
                                <div class="organizer">
                                    Association of Health Records and Information Management Practitioners of Nigeria
                                    (AHRIMPN)
                                </div>
                                <div class="collab">In Collaboration With</div>
                                <div class="organizer">
                                    Health Records Officers Registration Board of Nigeria (HRORBN)
                                </div>
                            </div>

                            <!-- Presents -->
                            <div class="presents text-white fw-bold small mb-3">Presents
                                Her</div>

                            <!-- Main Event -->
                            <h1 class="event-title text-white fw-bold mb-2">
                                44th Annual General Meeting &amp; National Scientific
                                Conference
                            </h1>

                            <!-- Peace & Tourism -->
                            <div class="peace-tourism text-warning fw-bold mb-4">Abuja Renewal Conference 2025</div>

                            <!-- Theme -->
                            <div class="theme-full d-none d-md-block text-white mb-4">
                                Theme: <span class="fw-semibold">
                                    Strengthening the Nigeria’s Healthcare System through
                                    Innovation and Technology in Health Information Management.
                                    <br>
                                </span>
                            </div>

                            <div class="theme-abbr d-block d-md-none text-white mb-4">
                                Theme: <span class="fw-semibold">
                                    Strengthening the Nigeria’s Healthcare System through
                                    Innovation and Technology in Health Information Management.
                                    <br>
                                </span>
                            </div>

                            <!-- CTA -->
                            <div class="d-flex justify-content-center gap-2 cta-wrap">
                                <a href="register.php" class="btn btn-warning text-dark rounded-pill px-4 py-2">Register
                                    Now</a>
                                <a href="contact.php" class="btn btn-outline-light rounded-pill px-4 py-2">Contact
                                    Us</a>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Second Slide -->
                <div class="carousel-item" style="position:relative;">
                    <img class="w-100" src="assets/img/carousel-2.jpg" alt="Conference Highlight">
                    <div class="carousel-overlay"></div>

                    <div class="carousel-caption custom-caption slide-two-caption">
                        <div class="caption-inner text-center">

                            <!-- About Conference -->
                            <div class="about-conference-title">About the Conference</div>

                            <h2 class="about-conference-text">
                                Join professionals, policymakers, and researchers as we
                                explore
                                innovative pathways for sustainable health information
                                management in Nigeria.
                            </h2>

                            <div class="d-flex justify-content-center gap-2">
                                <a href="register.php" class="btn btn-warning text-dark rounded-pill px-4 py-2">Register
                                    Now</a>
                                <a href="contact.php" class="btn btn-outline-light rounded-pill px-4 py-2">Learn
                                    More</a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <!-- Carousel End -->

    <!-- Banner Start -->
    <div class="container-fluid banner mb-5">
        <div class="container">
            <div class="row gx-0">

                <!-- Box 1 -->
                <div class="col-lg-4 wow zoomIn" data-wow-delay="0.1s">
                    <div class="bg-primary d-flex flex-column p-5" style="height: 300px;">

                        <!-- Mobile Sub-themes heading -->
                        <h3 class="subthemes-heading text-white text-center d-block d-lg-none mb-3">Sub-themes</h3>

                        <ol start="1" class="banner-list text-white mt-4">
                            <li>Improving the quality of healthcare data for renewed hope in a changing disease
                                landscape: the role of HIM professionals.</li>
                            <li>Rebuilding trust in healthcare through improved data quality.</li>
                        </ol>
                    </div>
                </div>

                <!-- Box 2 (Center with Heading) -->
                <div class="col-lg-4 wow zoomIn" data-wow-delay="0.3s">
                    <div class="bg-dark d-flex flex-column p-5" style="height: 300px;">
                        <h3 class="subthemes-heading text-white text-center d-none d-lg-block">Sub-themes</h3>
                        <ol start="3" class="banner-list text-white mt-4">
                            <li>Strengthening the capacity of HIM professionals for a
                                digital healthcare.</li>
                        </ol>
                    </div>
                </div>

                <!-- Box 3 -->
                <div class="col-lg-4 wow zoomIn" data-wow-delay="0.6s">
                    <div class="bg-secondary d-flex flex-column p-5" style="height: 300px;">
                        <ol start="4" class="banner-list text-white mt-4">
                            <li>Leveraging technology for improved healthcare service
                                delivery in Nigeria.</li>
                            <li>Health data as a National asset: the role of HIM
                                professionals.</li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Banner End -->

    <!-- About Start -->
    <div class="container-fluid about-section py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-5">

                <!-- Text Block -->
                <div class="col-lg-7">
                    <div class="section-title mb-4">
                        <h5 class="position-relative d-inline-block text-uppercase">About
                            Us</h5>
                        <h1 class="display-6 mb-3">Advancing Health Records & Information
                            Management in Nigeria</h1>
                    </div>

                    <p class="lead mb-4">
                        The Association of Health Records and Information Management
                        Practitioners of Nigeria (AHRIMPN) is
                        dedicated to strengthening the nation’s health information system
                        through professionalism and innovation.
                    </p>

                    <p class="mb-4">
                        We uphold modern standards in health records management, ensuring
                        accuracy, accessibility, and
                        confidentiality for better healthcare delivery and national
                        development.
                    </p>

                    <!-- Highlights -->
                    <div class="row g-3 about-highlights">
                        <div class="col-sm-6">
                            <h6><i class="fa fa-check-circle text-primary me-2"></i>Excellence</h6>
                            <h6><i class="fa fa-check-circle text-primary me-2"></i>Innovation</h6>
                        </div>
                        <div class="col-sm-6">
                            <h6><i class="fa fa-check-circle text-primary me-2"></i>Ethics</h6>
                            <h6><i class="fa fa-check-circle text-primary me-2"></i>Impact</h6>
                        </div>
                    </div>

                    <!-- CTA -->
                    <a href="contact.php" class="btn btn-primary about-btn py-2 px-4 mt-4">
                        Learn More
                    </a>
                </div>

                <!-- Image Block -->
                <div class="col-lg-5" style="min-height: 500px;">
                    <div class="position-relative h-100">
                        <img class="position-absolute w-100 h-100 rounded wow zoomIn" data-wow-delay="0.9s"
                            src="assets/img/carousel-20.jpg" style="object-fit: cover;" alt="About Us Image">
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Registration Steps Start -->
    <div class="container-fluid py-5 bg-appointment">
        <div class="container">
            <div class="offer-text text-center">
                <h1 class="call-heading mb-4">How to Register</h1>
                <p class="mb-4">Follow these simple steps to complete your registration:</p>

                <ol class="banner-list text-start mx-auto" style="max-width: 750px;">
                    <li>
                        <strong>Visit the Registration Page:</strong>
                        Click here to <a href="register.php" class="text-highlight fw-bold">Register</a>.
                    </li>

                    <li>
                        <strong>Check Registration Fees:</strong><br>
                        <div class="fee-box mt-3">
                            <?php foreach ($fees as $category => $items): ?>
                            <h5 class="mt-3"><?= htmlspecialchars($category); ?></h5>
                            <ul class="ms-3">
                                <?php foreach ($items as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item['subcategory']); ?> :
                                    <span class="fee-amount">₦<?= number_format($item['amount'], 0); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endforeach; ?>
                        </div>
                    </li>

                    <li>
                        <strong>Make Payment:</strong>
                        Pay the applicable registration fee to the official account below: <br><br>

                        <div class="account-box ">
                            <h2 class="mb-3">Account Details</h2>
                            <p class="account-name"><?= htmlspecialchars($account['account_name']); ?></p>
                            <p class="account-bank">
                                <?= htmlspecialchars($account['bank_name']); ?> –
                                <span class="account-number"><?= htmlspecialchars($account['account_number']); ?></span>
                            </p>
                        </div>
                    </li>

                    <li>
                        <strong>Upload Receipt:</strong>
                        On the registration page, upload your payment receipt and complete the form.
                    </li>
                    <li>
                        <strong>Confirmation:</strong>
                        Once approved, you’ll receive a confirmation email with event details.
                    </li>
                </ol>

                <div class="cta-wrap mt-4">
                    <a href="register.php" class="btn btn-light btn-lg rounded-pill px-4 py-2">
                        Proceed to Register
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Registration Steps End -->


    <!-- Notable dates -->
    <div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container important-dates">
            <div class="row g-5">
                <div class="col-lg-5">
                    <div class="section-title mb-4">
                        <h5 class="position-relative d-inline-block text-uppercase">Notable
                            Dates</h5>
                        <h1 class="mb-0">23rd November – 28th November, 2025</h1>
                    </div>

                    <p class="mb-4 d-flex align-items-start">
                        <i class="fas fa-map-marker-alt me-2 mt-1"></i>
                        <span> POWA National Secretariat, No. 6 Dame Patience Jonathan Way, opposite DIA
                            Junior Officers’ Mess, Mambila Barracks, Asokoro, Abuja.</span>
                    </p>

                    <h5 class="text-uppercase wow fadeInUp d-flex align-items-center" data-wow-delay="0.3s">
                        For more Information
                    </h5>

                    <h1 class="wow fadeInUp" data-wow-delay="0.6s">
                        <span class="contact-number">
                            <i class="fas fa-phone-alt"></i> +2348035614940
                        </span>
                    </h1>
                </div>

                <div class="col-lg-7">
                    <div class="owl-carousel price-carousel wow zoomIn" data-wow-delay="0.9s">

                        <!-- Day 1 -->
                        <div class="price-item pb-4">
                            <div class="position-relative">
                                <img class="img-fluid rounded-top" src="assets/img/price-1.jpg" alt>
                            </div>
                            <div class="position-relative text-center bg-light border-bottom border-primary py-5 p-4">
                                <h4>23rd November</h4>
                                <hr class="text-primary w-50 mx-auto mt-0">
                                <div class="event-icon mb-3">
                                    <i class="fas fa-plane-arrival fa-2x text-primary"></i>
                                </div>
                                <div><span>Arrival of NEC Members</span></div>
                                <div><span>NEC Meeting</span></div>
                            </div>
                        </div>

                        <!-- Day 2 -->
                        <div class="price-item pb-4">
                            <div class="position-relative">
                                <img class="img-fluid rounded-top" src="assets/img/price-2.jpg" alt>
                            </div>
                            <div class="position-relative text-center bg-light border-bottom border-primary py-5 p-4">
                                <h4>24th November</h4>
                                <hr class="text-primary w-50 mx-auto mt-0">
                                <div class="event-icon mb-3">
                                    <i class="fas fa-plane-arrival fa-2x text-primary"></i>
                                </div>
                                <div><span>Arrival of Participants</span></div>
                                <div><span>Registration</span></div>
                            </div>
                        </div>

                        <!-- Day 3 -->
                        <div class="price-item pb-4">
                            <div class="position-relative">
                                <img class="img-fluid rounded-top" src="assets/img/price-3.jpg" alt>
                            </div>
                            <div class="position-relative text-center bg-light border-bottom border-primary py-5 p-4">
                                <h4>25th November</h4>
                                <hr class="text-primary w-50 mx-auto mt-0">
                                <div class="event-icon mb-3">
                                    <i class="fas fa-door-open fa-2x text-primary"></i>
                                </div>
                                <div><span>Opening Ceremony</span></div>
                                <div><span>Scientific Session I</span></div>
                            </div>
                        </div>

                        <!-- Day 4 -->
                        <div class="price-item pb-4">
                            <div class="position-relative">
                                <img class="img-fluid rounded-top" src="assets/img/price-4.jpg" alt>
                            </div>
                            <div class="position-relative text-center bg-light border-bottom border-primary py-5 p-4">
                                <h4>26th November</h4>
                                <hr class="text-primary w-50 mx-auto mt-0">
                                <div class="event-icon mb-3">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-primary"></i>
                                </div>
                                <div><span>Scientific Session II, Constitutional Review & Tourism</span></div>
                            </div>
                        </div>

                        <!-- Day 2 -->
                        <div class="price-item pb-4">
                            <div class="position-relative">
                                <img class="img-fluid rounded-top" src="assets/img/price-2.jpg" alt>
                            </div>
                            <div class="position-relative text-center bg-light border-bottom border-primary py-5 p-4">
                                <h4>27th November</h4>
                                <hr class="text-primary w-50 mx-auto mt-0">
                                <div class="event-icon mb-3">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <div><span>AGM, Election & Swearing-in Ceremony</span></div>
                            </div>
                        </div>

                        <!-- Day 5 -->
                        <div class="price-item pb-4">
                            <div class="position-relative">
                                <img class="img-fluid rounded-top" src="assets/img/price-5.jpg" alt>
                            </div>
                            <div class="position-relative text-center bg-light border-bottom border-primary py-5 p-4">
                                <h4>28th November</h4>
                                <hr class="text-primary w-50 mx-auto mt-0">
                                <div class="event-icon mb-3">
                                    <i class="fas fa-plane-departure fa-2x text-primary"></i>
                                </div>
                                <div><span>Departure</span></div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- End Notable dates -->

    <!-- Tour registration -->
    <div id="tour-registration" class="container-fluid bg-primary bg-appointment1 my-5 wow fadeInUp"
        data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="text-center mb-5 wow fadeInDown" data-wow-delay="0.2s">
                <h1 class="fw-bold call-heading mb-3">For an unforgettable tour experience</h1>
                <p class="lead fw-bold text-muted">Register below</p>
            </div>

            <div class="row justify-content-center align-items-center g-5">
                <div class="col-lg-6 py-5 text-white">
                    <div class="py-5 registration-details">
                        <h1 class="display-5 text-gradient mb-4">Tourism Registration</h1>

                        <p>
                            Tour Package Fee: <span class="fw-bold">₦<?= number_format($tour_amount, 0); ?></span>
                        </p>
                        <p>Includes guided experience, travel souvenirs & certificates of participation.</p>

                        <br /><br />

                        <h1 class="account-heading1 text-gradient mb-3">Account Details:</h1>
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

                <!-- Right Column -->
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="0.5s">
                    <form action="tourism_register.php" method="POST" enctype="multipart/form-data"
                        class="glass-form shadow-lg p-5 rounded-4">

                        <h3 class="fw-bold mb-4 text-center form-header">Tour Registration Form</h3>

                        <?php if (isset($_SESSION['tour_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show text-center fw-semibold"
                            role="alert">
                            <?= $_SESSION['tour_success']; unset($_SESSION['tour_success']); ?>

                        </div>
                        <?php elseif (isset($_SESSION['tour_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show text-center fw-semibold"
                            role="alert">
                            <?= $_SESSION['tour_error']; unset($_SESSION['tour_error']); ?>

                        </div>
                        <?php endif; ?>

                        <?php $old = $_SESSION['old'] ?? []; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="first_name" class="form-control form-control-lg rounded-3"
                                    placeholder="First Name" required
                                    value="<?= htmlspecialchars($old['first_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="last_name" class="form-control form-control-lg rounded-3"
                                    placeholder="Last Name" required
                                    value="<?= htmlspecialchars($old['last_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control form-control-lg rounded-3"
                                    placeholder="Email Address" required
                                    value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="tel" name="phone" class="form-control form-control-lg rounded-3"
                                    placeholder="Phone Number" required
                                    value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                            </div>

                            <div class="col-12 text-center">
                                <label class="fw-semibold mt-2 d-block mb-2 text-dark">Upload Payment Receipt</label>
                                <input type="file" name="receipt" class="form-control form-control-lg rounded-3"
                                    accept="image/*,.pdf" required>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-gradient w-100 py-3 fs-5 rounded-3 mt-2">
                                    Confirm Registration
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php unset($_SESSION['old']); ?>
                </div>
            </div>

        </div>
    </div>
    <!-- Tour registration end -->

    <!-- Abstraction -->
    <div class="container-fluid bg-offer my-5 py-5 wow fadeInUp" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-7 wow zoomIn" data-wow-delay="0.6s">
                    <div class="offer-text text-center rounded p-5">
                        <h1 class="call-heading">Call for Abstract Submission</h1>
                        <p class="submission-email">Send abstracts to:
                            <span>ijhrim.nigeria@yahoo.com</span>
                        </p>
                        <!--h5 class="deadline-label">Submission Deadline:</h5>
                        <h1 class="deadline-date">14th October, 2025</h1>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Abstraction End  -->

    <!-- Team Start -->
    <div class="container-fluid py-5 team-section">
        <div class="container">

            <!-- Section Title -->
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h5 class="text-primary text-uppercase mb-2">Guests</h5>
                    <h1 class="display-6 fw-bold">Our Distinguished Guests</h1>
                    <div class="title-underline mx-auto"></div>
                </div>
            </div>

            <!-- Guests Grid -->
            <div class="row g-5">
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Special Guest of Honour</p>
                            <h4 class="mb-2">Dr. Iziaq Adekunle Salako</h4>
                            <p class="text-muted mb-0">Hon. State Minister for Health</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Host</p>
                            <h4 class="mb-2">Alh. Babagana Mustapha</h4>
                            <p class="text-muted mb-0">Registrar/CEO - HRORBN</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Guest of Honour</p>
                            <h4 class="mb-2">Dr. Pokop Wushipba Bupwatda</h4>
                            <p class="text-muted mb-0">Chief Medical Director, JUTH</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Royal Father</p>
                            <h4 class="mb-2">Dr. Mai Sallau Musa</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Chief Host</p>
                            <h4 class="mb-2">Comr. Michael Luka Mallo</h4>
                            <p class="text-muted mb-0">National President, AHRIMPN</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Father of the Day</p>
                            <h4 class="mb-2">Elder Daniel O. Akanji</h4>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Chairman of the Occasion</p>
                            <h4 class="mb-2">Dr. Vincent Olatunji</h4>
                            <p class="text-muted mb-0">Hon. Commissioner Nigeria Data Protection Commission</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Keynote Speaker</p>
                            <h4 class="mb-2">Dr. AB Garba</h4>
                            <p class="text-muted mb-0">Director Planning, NPHCDA</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="team-item shadow-sm">
                        <div class="team-text bg-light text-center rounded p-4">
                            <p class="text-primary mb-1">Guest Speaker</p>
                            <h4 class="mb-2">Prof. Yahaya Zayyana Ibrahim</h4>
                            <p class="text-muted mb-0">Director Academic Planning AAU University</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- Team End -->

    <?php include 'footer.php'; ?>
</body>

</html>