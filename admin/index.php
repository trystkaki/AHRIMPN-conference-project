<?php
require_once "includes/session_manager.php"; // start session first
require_once "includes/config.php";

if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$message = "";
$type = "danger"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($adminId, $hashedPassword, $role);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            
            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_username"] = $username;
            $_SESSION["admin_id"] = $adminId;
            $_SESSION["role"] = $role;

            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Invalid username or password.";
    }
    $stmt->close();
     // Redirect to self to prevent form resubmission on refresh
    if (!empty($message)) {
        $_SESSION["flash_message"] = $message;
        $_SESSION["flash_type"] = $type;
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}
// Show flash message if set
if (isset($_SESSION["flash_message"])) {
    $message = $_SESSION["flash_message"];
    $type = $_SESSION["flash_type"];
    unset($_SESSION["flash_message"], $_SESSION["flash_type"]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <?php include "includes/head.php"; ?>
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <!-- Global alert container -->
    <div id="alert-container" class="position-fixed top-0 start-50 translate-middle-x mt-3"
        style="z-index: 2000; width: 100%; max-width: 400px;"></div>

    <div class="card shadow-lg p-4" style="width: 100%; max-width: 400px; margin-top:-150px;">
        <h3 class="text-center mb-3">Admin Login</h3>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show mb-3" role="alert">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3 position-relative">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control password-field pe-5" name="password" required>
                <i class="bi bi-eye toggle-password"></i>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
    <script src="includes/table.js"></script>
</body>

</html>