<?php
/**
 * includes/admin_sidebar.php
 * Reusable admin layout shell.
 *
 * Expects from caller (set BEFORE require_once):
 *   $adminPage   — string key matching one of the $adminNav keys
 *   $pageTitle   — string for <title> and page heading
 *
 * Outputs:
 *   Full HTML from <!DOCTYPE> through the opening <div class="admin-layout">
 *   <aside class="admin-sidebar"> … </aside>
 *   <div class="admin-main"> <div class="admin-topbar"> … </div>
 *   <div class="admin-content">
 *
 * The calling page renders its own content, then calls:
 *   require_once '../includes/admin_footer.php';
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$pageTitle = isset($pageTitle) ? (string)$pageTitle : 'Admin';
$adminPage = isset($adminPage) ? (string)$adminPage : '';
$adminName = htmlspecialchars($_SESSION['admin_full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

/* ── Sidebar navigation groups ──────────────────────────────── */
$adminNav = [
    'overview' => [
        ['dashboard',   'Dashboard',   'admin_dashboard.php'],
    ],
    'academic' => [
        ['courses',      'Courses',      'courses.php'],
        ['students',     'Students',     'students.php'],
        ['instructors',  'Instructors',  'instructors.php'],
        ['departments',  'Departments',  'departments.php'],
        ['classrooms',   'Classrooms',   'classrooms.php'],
        ['classes',      'Classes',      'classes.php'],
        ['enrollments',  'Enrollments',  'enrollments.php'],
        ['search',       'Search',       'search.php'],
    ],
    'system' => [
        ['about_page',   'About',        '../about.php'],
        ['mission_page', 'Mission',      '../mission.php'],
        ['settings',     'Settings',     'settings.php'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIMS Admin &mdash; <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/sims.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
  <link rel="stylesheet" href="../assets/css/search.css">
</head>
<body class="admin-body">

<div class="admin-layout">

  <!-- ═══════════════════════════════════
       SIDEBAR
  ═══════════════════════════════════ -->
  <aside class="admin-sidebar" id="admin-sidebar">

    <!-- Brand -->
    <div class="asb-brand">
      <span class="asb-brand-name">SIMS</span>
      <span class="asb-brand-sub">Admin Panel</span>
    </div>

    <!-- Navigation -->
    <nav class="asb-nav" aria-label="Admin navigation">

      <p class="asb-group-label">Overview</p>
      <?php foreach ($adminNav['overview'] as [$key, $label, $href]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
           class="asb-link<?= ($adminPage === $key) ? ' asb-link--active' : '' ?>"
           <?= ($adminPage === $key) ? 'aria-current="page"' : '' ?>>
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>

      <p class="asb-group-label">Academic</p>
      <?php foreach ($adminNav['academic'] as [$key, $label, $href]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
           class="asb-link<?= ($adminPage === $key) ? ' asb-link--active' : '' ?>"
           <?= ($adminPage === $key) ? 'aria-current="page"' : '' ?>>
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>

      <p class="asb-group-label">System</p>
      <?php foreach ($adminNav['system'] as [$key, $label, $href]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
           class="asb-link<?= ($adminPage === $key) ? ' asb-link--active' : '' ?>"
           <?= ($adminPage === $key) ? 'aria-current="page"' : '' ?>
           <?php if (str_starts_with($href, '..')): ?>
             target="_blank" rel="noopener noreferrer"
           <?php endif; ?>>
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>

      <div class="asb-divider"></div>

      <a href="admin_logout.php" class="asb-link asb-link--danger">
        Logout
      </a>

    </nav>
  </aside><!-- /sidebar -->

  <!-- ═══════════════════════════════════
       MAIN AREA
  ═══════════════════════════════════ -->
  <div class="admin-main">

    <!-- Top bar -->
    <header class="admin-topbar">
      <button class="asb-toggle" id="asb-toggle" aria-label="Toggle sidebar" aria-expanded="true">
        <span class="hbar"></span><span class="hbar"></span><span class="hbar"></span>
      </button>
      <h1 class="admin-topbar-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>

      <!-- ── Topbar search bar ─────────────────────────────── -->
      <form id="topbar-search-form" class="topbar-search-form"
            method="GET" action="search.php" role="search"
            aria-label="Quick search">
        <input type="text" name="q" placeholder="Search…"
               autocomplete="off" aria-label="Search records"
               value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" aria-label="Go to search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          Search
        </button>
      </form>

      <div class="admin-topbar-right">
        <span class="admin-topbar-user">Welcome, <?= $adminName ?></span>
        <a href="admin_logout.php" class="btn btn-ghost btn-sm">Logout</a>
      </div>
    </header>

    <!-- Page content starts here (caller renders content) -->
    <div class="admin-content">
