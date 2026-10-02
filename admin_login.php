<?php
/**
 * admin_login.php — Admin Login Page
 */
require_once '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in → go to dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

// ── Auto-create admins table if it doesn't exist ──────────────
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

// ── Seed default admin if table is empty ─────────────────────
$adminCount = (int) $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
if ($adminCount === 0) {
    $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
    $pdo->prepare(
        "INSERT INTO admins (username, password, full_name, email)
         VALUES (:u, :pw, :fn, :em)"
    )->execute([
        ':u'  => 'admin',
        ':pw' => $hash,
        ':fn' => 'System Administrator',
        ':em' => 'admin@school.ac.ke',
    ]);
}

$error   = '';
$success = '';

// ── Process login POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';          // never trim passwords

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT admin_id, username, password, full_name, email
             FROM admins WHERE username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['admin_id']        = $admin['admin_id'];
            $_SESSION['admin_username']  = $admin['username'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
            $_SESSION['admin_email']     = $admin['email'];

            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMS &mdash; Admin Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/sims.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    body { background: var(--primary-950); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .login-wrap { width: 100%; max-width: 420px; padding: var(--sp-6); }
    .login-card { background: var(--surface); border-radius: var(--r-xl); padding: var(--sp-8); box-shadow: var(--shadow-lg); border-top: 4px solid var(--accent-500); }
    .login-brand { text-align: center; margin-bottom: var(--sp-7); }
    .login-brand-name { font-size: 2rem; font-weight: 800; color: var(--primary); letter-spacing: -0.5px; }
    .login-brand-sub  { font-size: 0.80rem; color: var(--text-muted); margin-top: 2px; }
    .login-title { font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: var(--sp-6); text-align: center; }
    .login-footer { text-align: center; margin-top: var(--sp-5); font-size: 0.80rem; color: var(--text-muted); }
    .login-footer a { color: var(--primary-light); }
  </style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">

    <div class="login-brand">
      <div class="login-brand-name">SIMS</div>
      <div class="login-brand-sub">Student Information Management System</div>
    </div>

    <p class="login-title">Administrator Login</p>

    <?php if ($error): ?>
    <div class="alert alert-error" role="alert" style="margin-bottom:var(--sp-5);">
      <div class="alert-body">
        <strong class="alert-title">Login failed</strong>
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    </div>
    <?php endif; ?>

    <form action="admin_login.php" method="POST" novalidate>

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control"
               required autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>

      <div class="form-group" style="margin-bottom:var(--sp-6);">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
        Login
      </button>

    </form>

    <div class="login-footer">
      <a href="../index.php">&larr; Back to Public Site</a>
    </div>

  </div>
</div>
<script src="../assets/js/sims.js"></script>
</body>
</html>
