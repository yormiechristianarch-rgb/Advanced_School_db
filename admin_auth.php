<?php
/**
 * admin_auth.php
 * Include at the top of every admin page to enforce authentication.
 * Usage:  require_once 'admin_auth.php';   (from within admin/)
 *
 * Starts or resumes the session, then redirects to admin_login.php
 * if no valid admin session exists.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    // Prevent caching of protected pages
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Location: admin_login.php');
    exit;
}
