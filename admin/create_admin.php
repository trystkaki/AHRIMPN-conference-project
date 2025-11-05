<?php
require_once "includes/session_manager.php";
require_once "includes/config.php";
checkAdminAuth();

// ✅ Restrict create_admin to superadmin only
if ($_SESSION['role'] !== 'superadmin') {
    header("HTTP/1.1 403 Forbidden");
    die("Access denied. Only Super Admin can create new admins.");
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role     = $_POST["role"];

    if (!empty($username) && !empty($password) && !empty($role)) {
        // ✅ Check if username already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $_SESSION["flash_message"] = "⚠️ Username already exists. Choose another.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hash, $role);

            if ($stmt->execute()) {
                $_SESSION["flash_message"] = "✅ Admin created successfully!";
            } else {
                $_SESSION["flash_message"] = "⚠️ Database error: " . $conn->error;
            }
            $stmt->close();
        }

        $check->close();
    } else {
        $_SESSION["flash_message"] = "⚠️ All fields are required.";
    }

    // Redirect to avoid resubmission
    header("Location: create_admin.php");
    exit;
}

// Retrieve message if set
if (!empty($_SESSION["flash_message"])) {
    $message = $_SESSION["flash_message"];
    unset($_SESSION["flash_message"]); // clear after showing once
}
include "includes/head.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Admin</title>
</head>

<body>
    <div class="d-flex">
        <?php include "sidebar.php"; ?>

        <div class="container p-4">
            <h2 class="mb-4">Create Admin</h2>

            <?php if (!empty($message)): ?>
            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="post" class="p-3 border rounded bg-light shadow-sm">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3 position-relative">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control password-field pe-5" required>
                    <i class="bi bi-eye toggle-password"></i>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="viewer">Viewer</option>
                        <option value="approval">Approval Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary px-4">Create Admin</button>
            </form>
        </div>
    </div>

    <script src="includes/table.js"></script>
</body>

</html>