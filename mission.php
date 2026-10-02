<?php
$pageTitle  = 'Mission';
$activePage = 'mission';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="ph-text">
      <nav class="ph-breadcrumb" aria-label="Breadcrumb"><a href="index.php">Home</a> &rsaquo; Mission</nav>
      <h1 class="ph-title">Mission &amp; Vision</h1>
      <p class="ph-desc">The guiding principles behind the Student Information Management System.</p>
    </div>
  </div>
</div>

<main>
<div style="max-width:860px;margin:0 auto;">

  <!-- Mission + Vision side by side -->
  <div class="grid-2" style="margin-bottom:28px;">

    <div class="content-card">
      <div class="cc-header"><div class="cc-title">Our Mission</div></div>
      <div class="cc-body">
        <p style="font-size:0.95rem;line-height:1.75;color:var(--text-secondary);">
          To provide educational institutions with a reliable, secure, and efficient Student
          Information Management System that simplifies academic administration, ensures data
          integrity, and enables informed decision-making through accurate and accessible records.
        </p>
      </div>
    </div>

    <div class="content-card">
      <div class="cc-header"><div class="cc-title">Our Vision</div></div>
      <div class="cc-body">
        <p style="font-size:0.95rem;line-height:1.75;color:var(--text-secondary);">
          To be the trusted academic records platform that empowers institutions to deliver
          better educational outcomes by eliminating administrative inefficiency, reducing errors,
          and connecting students, faculty, and administrators through a single coherent system.
        </p>
      </div>
    </div>

  </div>

  <!-- Goals -->
  <div class="content-card" style="margin-bottom:28px;">
    <div class="cc-header"><div class="cc-title">Our Goals</div></div>
    <div class="cc-body">
      <?php
      $goals=[
        ['Efficient Student Records Management','Maintain accurate, up-to-date student information including registration details, enrollment history, and personal records in a single, searchable system.'],
        ['Accurate Academic Records','Ensure that course, class, and enrollment data is always consistent through relational database constraints, preventing duplicates and orphaned records.'],
        ['Improved Administrative Efficiency','Reduce the time administrators spend on routine tasks by providing fast, intuitive tools for adding, editing, and managing academic records.'],
        ['Easy Access to Academic Information','Give authorised users immediate access to the information they need — from student enrollment lists to class schedules — without navigating multiple systems.'],
        ['Data Integrity at Every Step','Use database transactions, foreign-key constraints, and server-side validation to guarantee that every record saved is complete, valid, and consistent.'],
        ['Reliable Record Management','Provide a stable, tested platform that institutions can depend on for daily operations, with clear error handling and rollback protection when operations fail.'],
        ['Better Academic Communication','Bridge the gap between academic administration and the classroom by providing shared, accurate data that all authorised users can access and trust.'],
      ];
      foreach($goals as $i=>[$title,$desc]):?>
      <div style="display:flex;gap:16px;padding:14px 0;border-bottom:1px solid var(--border-light);<?=$i===count($goals)-1?'border-bottom:none':''?>">
        <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--primary-700),var(--primary-500));border-radius:var(--r);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.78rem;font-weight:800;color:#fff;"><?=$i+1?></div>
        <div>
          <p style="font-weight:700;color:var(--text);margin-bottom:4px;font-size:0.9rem;"><?=htmlspecialchars($title,ENT_QUOTES,'UTF-8')?></p>
          <p style="font-size:0.865rem;color:var(--text-secondary);line-height:1.6;"><?=htmlspecialchars($desc,ENT_QUOTES,'UTF-8')?></p>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>

  <!-- Values -->
  <div class="content-card">
    <div class="cc-header"><div class="cc-title">Our Values</div></div>
    <div class="cc-body">
      <div class="grid-3" style="gap:16px;">
        <?php
        $values=[
          ['Integrity',      'Every record is accurate, complete, and protected from corruption through validation and constraints.'],
          ['Reliability',    'The system is available and dependable for the daily administrative tasks that institutions depend on.'],
          ['Efficiency',     'Workflows are streamlined so administrators spend less time on data entry and more time on meaningful work.'],
          ['Security',       'Access is controlled, passwords are hashed, and all database operations use prepared statements.'],
          ['Transparency',   'All data is traceable, auditable, and accessible to authorised users without hidden processes.'],
          ['Accessibility',  'The platform is usable on any modern device and is designed with clarity and professional usability in mind.'],
        ];
        foreach($values as [$val,$desc]):?>
        <div style="background:var(--accent-tint);border:1px solid var(--accent-100);border-top:3px solid var(--accent-600);border-radius:var(--r-md);padding:18px 16px;">
          <p style="font-weight:700;color:var(--primary);margin-bottom:6px;font-size:0.9rem;"><?=htmlspecialchars($val,ENT_QUOTES,'UTF-8')?></p>
          <p style="font-size:0.82rem;color:var(--text-secondary);line-height:1.55;"><?=htmlspecialchars($desc,ENT_QUOTES,'UTF-8')?></p>
        </div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

</div>
</main>

<?php require_once 'includes/footer.php'; ?>
