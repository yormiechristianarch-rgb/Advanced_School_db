<?php
$pageTitle  = 'About';
$activePage = 'about';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="ph-text">
      <nav class="ph-breadcrumb" aria-label="Breadcrumb"><a href="index.php">Home</a> &rsaquo; About</nav>
      <h1 class="ph-title">About SIMS</h1>
      <p class="ph-desc">Learn about the Student Information Management System and what it offers.</p>
    </div>
  </div>
</div>

<main>
<div style="max-width:860px;margin:0 auto;">

  <!-- About SIMS -->
  <div class="content-card" style="margin-bottom:28px;">
    <div class="cc-header"><div class="cc-title">About SIMS</div></div>
    <div class="cc-body">
      <p style="margin-bottom:14px;">
        The <strong>Student Information Management System (SIMS)</strong> is a comprehensive web-based
        platform designed to centralise and streamline the management of academic records within a
        university or educational institution.
      </p>
      <p>
        Built on a robust relational database with PHP and MySQL, SIMS provides administrators,
        faculty, and academic staff with a single, secure platform to manage students, courses,
        instructors, departments, classrooms, class schedules, and enrollment records.
      </p>
    </div>
  </div>

  <!-- Purpose -->
  <div class="content-card" style="margin-bottom:28px;">
    <div class="cc-header"><div class="cc-title">Our Purpose</div></div>
    <div class="cc-body">
      <p style="margin-bottom:14px;">
        Academic institutions handle large volumes of student and course data that must be accurate,
        accessible, and secure. Traditional paper-based or fragmented digital systems often lead to
        data inconsistencies, inefficiencies, and communication gaps between departments.
      </p>
      <p>
        SIMS was designed to solve these problems by providing a unified, structured system that
        enforces data integrity through foreign-key relationships, unique constraints, and
        transaction-protected operations — ensuring that every record saved is consistent and reliable.
      </p>
    </div>
  </div>

  <!-- Who It Is For -->
  <div class="content-card" style="margin-bottom:28px;">
    <div class="cc-header"><div class="cc-title">Who SIMS Is Designed For</div></div>
    <div class="cc-body">
      <div class="grid-2" style="gap:20px;">
        <?php
        $users=[
          ['Academic Administrators','Manage all records, schedule classes, and oversee enrollment across the institution.'],
          ['Department Heads','Track courses and instructors within their department.'],
          ['Faculty Members','Access class schedules, student enrollment lists, and course information.'],
          ['Admissions Staff','Register new students and enroll them into their first class in a single step.'],
        ];
        foreach($users as [$role,$desc]):?>
        <div style="background:var(--bg-tinted);border:1px solid var(--border-light);border-radius:var(--r);padding:16px;">
          <p style="font-weight:700;color:var(--primary);margin-bottom:6px;"><?=htmlspecialchars($role,ENT_QUOTES,'UTF-8')?></p>
          <p style="font-size:0.875rem;color:var(--text-secondary);"><?=htmlspecialchars($desc,ENT_QUOTES,'UTF-8')?></p>
        </div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

  <!-- Key Features -->
  <div class="content-card" style="margin-bottom:28px;">
    <div class="cc-header"><div class="cc-title">Key Features</div></div>
    <div class="cc-body">
      <?php
      $features=[
        ['Student Management','Register students, manage personal information, track enrollment history, and handle gender-inclusive records.'],
        ['Course Management','Create and manage course records with unique course codes, credit units, and department assignments.'],
        ['Class Scheduling','Schedule classes by linking courses, instructors, and classrooms with semester and academic year information.'],
        ['Enrollment Processing','Enroll students into classes using a ACID-compliant database transaction — both the student record and enrollment are committed together or rolled back entirely.'],
        ['Instructor Management','Maintain instructor profiles, department affiliations, employee numbers, and contact details.'],
        ['Department Management','Organise the institution into departments with Head-of-Department records and establishment history.'],
        ['Classroom Management','Track physical rooms with capacity, projector availability, and lab designations.'],
        ['Admin Dashboard','Secure administrator interface with full CRUD capabilities across all entities, statistics, and quick actions.'],
        ['Security','All database operations use PDO prepared statements. Administrator access is protected by session-based authentication with hashed passwords.'],
      ];
      foreach($features as $i=>[$title,$desc]):?>
      <div style="display:flex;gap:14px;padding:12px 0;border-bottom:1px solid var(--border-light);<?=$i===count($features)-1?'border-bottom:none':''?>">
        <div style="width:26px;height:26px;background:var(--primary-tint);border-radius:var(--r-full);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.72rem;font-weight:700;color:var(--primary);"><?=$i+1?></div>
        <div><p style="font-weight:600;color:var(--text);margin-bottom:3px;"><?=htmlspecialchars($title,ENT_QUOTES,'UTF-8')?></p><p style="font-size:0.875rem;color:var(--text-secondary);"><?=htmlspecialchars($desc,ENT_QUOTES,'UTF-8')?></p></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>

  <!-- Benefits -->
  <div class="content-card">
    <div class="cc-header"><div class="cc-title">Benefits of SIMS</div></div>
    <div class="cc-body">
      <div class="grid-3" style="gap:16px;">
        <?php
        $benefits=[
          ['Centralised Data','All academic records in one place — no duplicate spreadsheets or paper files.'],
          ['Data Integrity','Relational constraints and transactions prevent orphaned or inconsistent records.'],
          ['Efficiency','Register a student and enroll them in a class in a single operation.'],
          ['Accessibility','Web-based access from any device on the institution network.'],
          ['Security','Role-based admin access with hashed passwords and session management.'],
          ['Scalability','Designed to handle growing numbers of students, courses, and classes.'],
        ];
        foreach($benefits as [$title,$desc]):?>
        <div style="background:var(--bg-tinted);border:1px solid var(--border-light);border-radius:var(--r);padding:16px;text-align:center;">
          <p style="font-weight:700;color:var(--primary);margin-bottom:6px;"><?=htmlspecialchars($title,ENT_QUOTES,'UTF-8')?></p>
          <p style="font-size:0.82rem;color:var(--text-secondary);"><?=htmlspecialchars($desc,ENT_QUOTES,'UTF-8')?></p>
        </div>
        <?php endforeach;?>
      </div>
    </div>
  </div>

</div>
</main>

<?php require_once 'includes/footer.php'; ?>
