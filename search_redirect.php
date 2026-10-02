<?php
/**
 * search_redirect.php — Public search gateway
 *
 * If the admin is already logged in → forward straight to admin/search.php.
 * If not → show a friendly message with a login prompt instead of
 *           silently bouncing the user to the admin login form.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$q = trim($_GET['q'] ?? '');

// Already logged in as admin → forward to the real search page
if (!empty($_SESSION['admin_id'])) {
    $url = 'admin/search.php';
    if ($q !== '') {
        $url .= '?q=' . urlencode($q) . '&type=all';
    }
    header('Location: ' . $url);
    exit;
}

// Guest — show a helpful message
$pageTitle  = 'Search';
$activePage = '';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="ph-text">
      <h1 class="ph-title">Search</h1>
      <p class="ph-desc">Search across the entire Student Information Management System.</p>
    </div>
  </div>
</div>

<main>
  <div class="main-narrow" style="padding-top:var(--sp-8);">

    <div class="content-card" style="text-align:center;padding:48px 32px;">
      <p style="font-size:1rem;color:var(--text-secondary);margin-bottom:8px;">
        The search feature is available to administrators only.
      </p>
      <?php if ($q !== ''): ?>
      <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:24px;">
        Your search for <strong>&ldquo;<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>&rdquo;</strong>
        will be run after you log in.
      </p>
      <?php else: ?>
      <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:24px;">
        Please log in as an administrator to search students, courses, instructors and more.
      </p>
      <?php endif; ?>
      <a href="admin/admin_login.php<?= $q !== '' ? '?redirect=' . urlencode('admin/search.php?q=' . urlencode($q) . '&type=all') : '' ?>"
         class="btn btn-primary btn-lg">
        Admin Login
      </a>
      <a href="index.php" class="btn btn-ghost btn-lg" style="margin-left:10px;">Back to Home</a>
    </div>

  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
