<?php
// includes/session_manager.php
if (session_status() === PHP_SESSION_NONE) {
    // Always start session before any output
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'read_and_close'  => false, // ensure writable session during login
    ]);
}

// Idle timeout = 30 minutes
define('SESSION_IDLE_TIMEOUT', 1800);

function manageSessionTimeout()
{
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $currentTime = time();

        if (isset($_SESSION['last_activity'])) {
            $idleTime = $currentTime - $_SESSION['last_activity'];
            if ($idleTime > SESSION_IDLE_TIMEOUT) {
                // Timeout: destroy session cleanly
                session_unset();
                session_destroy();
                header("Location: index.php?timeout=1");
                exit;
            }
        }
        $_SESSION['last_activity'] = $currentTime;
    }
}

function checkAdminAuth()
{
    manageSessionTimeout();

    if (empty($_SESSION['admin_logged_in'])) {
        header("Location: index.php");
        exit;
    }
}