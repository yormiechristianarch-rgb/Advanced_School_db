<?php
/**
 * create_admin.php — ONE-TIME SETUP SCRIPT
 * Run once, then DELETE this file immediately.
 * Access restricted to localhost only for safety.
 */

// Restrict access to localhost only
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteIp, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    die('<p style="font-family:sans-serif;padding:40px;">Access denied. This setup script can only be run from localhost.</p>');
}
 * This script:
 *  1. Creates the admins table if it does not exist.
 *  2. Inserts the default admin account using password_hash().
 *
 * Default credentials:
 *   Username : admin
 *   Password : Admin@1234
 *
 * SECURITY: Delete this file immediately after first use.
 * ─────────────────────────────────────────────────────────────────
 */
require_once '../config.php';

// ── 1. Create admins table ────────────────────────────────────
$pdo->exec("
CREATE TABLE IF NOT EXISTS admins (
    admin_id    INT          NOT NULL AUTO_INCREMENT,
    username    VARCHAR(60)  NOT NULL,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_admins      PRIMARY KEY (admin_id),
    CONSTRAINT uq_admin_user  UNIQUE (username),
    CONSTRAINT uq_admin_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// ── 2. Insert default admin (only if table is empty) ─────────
$count = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
$message = '';

if ($count === 0) {
    $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO admins (username, password, full_name, email)
         VALUES (:u, :pw, :fn, :em)"
    );
    $stmt->execute([
        ':u'  => 'admin',
        ':pw' => $hash,
        ':fn' => 'System Administrator',
        ':em' => 'admin@school.ac.ke',
    ]);
    $message = 'Admin account created successfully.';
} else {
    $message = 'Admins table already has records — no changes made.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SIMS — Admin Setup</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/sims.css">
  <style>body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--primary-950);}
  .box{background:var(--surface);border-radius:var(--r-xl);padding:40px;max-width:480px;width:100%;box-shadow:var(--shadow-lg);border-top:4px solid var(--accent-500);}
  h1{font-size:1.3rem;color:var(--primary);margin-bottom:12px;}
  .creds{background:var(--bg-tinted);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;margin:16px 0;font-family:var(--font-mono);font-size:0.875rem;}
  .warn{color:var(--error);font-weight:600;margin-top:16px;font-size:0.875rem;}
  </style>
</head>
<body>
<div class="box">
  <h1>SIMS Admin Setup</h1>
  <p style="color:var(--text-secondary);margin-bottom:12px;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
  <div class="creds">
    Username: <strong>admin</strong><br>
    Password: <strong>Admin@1234</strong>
  </div>
  <p><a href="admin_login.php" class="btn btn-primary" style="display:inline-block;">Go to Admin Login</a></p>
  <p class="warn">&#9888; Delete this file (create_admin.php) immediately after logging in.</p>
</div>
</body>
</html>
