<!-- Spinner Start -->
<div id="spinner"
    class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-grow text-primary m-1" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <div class="spinner-grow text-dark m-1" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <div class="spinner-grow text-secondary m-1" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<!-- Spinner End -->

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light shadow-sm px-5 py-3 py-lg-0">
    <!-- Left Logo -->
    <a href="#" class="navbar-brand p-0 d-flex align-items-center me-3">
        <img src="assets/img/logo1.png" alt="Association 1 Logo" class="img-fluid" style="height:50px;">
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Menu -->
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0 align-items-center">

            <!-- Logo 2 (outside collapse, but styled as a nav item) -->
            <a href="#" class="nav-item nav-link p-0 me-3 nav-logo-outside d-flex align-items-center">
                <img src="assets/img/logo2.png" alt="Association 2 Logo" class="img-fluid" style="height:50px;">
            </a>

            <a href="index.php" class="nav-item nav-link">Home</a>
            <a href="about.php" class="nav-item nav-link">About</a>
            <a href="register.php" class="nav-item nav-link">Register</a>
            <a href="contact.php" class="nav-item nav-link">Contact</a>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">More</a>
                <div class="dropdown-menu m-0">
                    <a href="team.php" class="dropdown-item">Team</a>
                    <a href="important_dates.php" class="dropdown-item">Notable
                        Dates</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- Navbar End -->