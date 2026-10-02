<?php
/**
 * includes/header.php
 * Shared public header for all front-end pages.
 * Expects $pageTitle and $activePage set before require_once.
 */
$pageTitle  = isset($pageTitle)  ? (string)$pageTitle  : 'SIMS';
$activePage = isset($activePage) ? (string)$activePage : '';

$_navItems = [
  'dashboard'      => ['Home',           'index.php'],
  'about'          => ['About',          'about.php'],
  'mission'        => ['Mission',        'mission.php'],
  'add_course'     => ['Add Course',     'add_course.php'],
  'schedule_class' => ['Schedule Class', 'schedule_class.php'],
  'enroll_student' => ['Enroll Student', 'enroll_student.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SIMS — Student Information Management System.">
  <title>SIMS &mdash; <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/sims.css">
  <link rel="stylesheet" href="assets/css/search.css">
</head>
<body>

<header class="site-header" id="site-header">
  <div class="header-inner">

    <a href="index.php" class="brand" aria-label="SIMS — go to home">
      <div class="brand-copy">
        <span class="brand-name">SIMS</span>
        <span class="brand-tagline">Student Information System</span>
      </div>
    </a>

    <!-- Desktop nav -->
    <nav class="main-nav" aria-label="Main navigation">
      <?php foreach ($_navItems as $key => [$label, $href]): ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
           class="nav-link<?= ($activePage === $key) ? ' nav-link--active' : '' ?>"
           <?= ($activePage === $key) ? 'aria-current="page"' : '' ?>>
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>
      <!-- Admin Login — always visible, styled as a distinct link -->
      <a href="admin/admin_login.php" class="nav-link nav-link--admin-login">
        Admin Login
      </a>
    </nav>

    <!-- Header search bar (desktop only — hidden on mobile via CSS) -->
    <form id="pub-header-search"
          class="header-search-form"
          method="GET"
          action="search_redirect.php"
          role="search"
          aria-label="Site search">
      <input type="text" name="q"
             placeholder="Search records…"
             autocomplete="off"
             aria-label="Search students, courses, instructors">
      <button type="submit" aria-label="Search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
      </button>
    </form>

    <!-- Hamburger -->
    <button class="hamburger" id="hamburger"
            aria-label="Toggle navigation menu"
            aria-expanded="false"
            aria-controls="mobile-nav">
      <span class="hbar"></span>
      <span class="hbar"></span>
      <span class="hbar"></span>
    </button>

  </div>

  <!-- Mobile nav drawer -->
  <nav class="mobile-nav" id="mobile-nav" aria-label="Mobile navigation" hidden>
    <?php foreach ($_navItems as $key => [$label, $href]): ?>
      <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
         class="mobile-nav-link<?= ($activePage === $key) ? ' mobile-nav-link--active' : '' ?>"
         <?= ($activePage === $key) ? 'aria-current="page"' : '' ?>>
        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
      </a>
    <?php endforeach; ?>
    <a href="admin/admin_login.php" class="mobile-nav-link mobile-nav-link--admin">Admin Login</a>
  </nav>

</header>
