<?php
require_once 'config.php';
$pageTitle  = 'Enroll Student';
$activePage = 'enroll_student';

$success = $_GET['success'] ?? null;
$error   = $_GET['error']   ?? null;

$old_first_name = htmlspecialchars(trim($_GET['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$old_last_name  = htmlspecialchars(trim($_GET['last_name']  ?? ''), ENT_QUOTES, 'UTF-8');
$old_email      = htmlspecialchars(trim($_GET['email']      ?? ''), ENT_QUOTES, 'UTF-8');
$old_reg_date   = htmlspecialchars(trim($_GET['reg_date']   ?? ''), ENT_QUOTES, 'UTF-8');
$old_gender     = htmlspecialchars(trim($_GET['gender']     ?? ''), ENT_QUOTES, 'UTF-8');
$old_class_id   = (int)($_GET['class_id'] ?? 0);

$classStmt = $pdo->prepare(
    "SELECT cl.class_id, co.course_code, co.course_name, cl.semester,
            CONCAT(i.first_name,' ',i.last_name) AS instructor_name
     FROM   classes cl
     JOIN   courses     co ON cl.course_id     = co.course_id
     JOIN   instructors i  ON cl.instructor_id = i.instructor_id
     ORDER  BY co.course_code ASC, cl.semester ASC"
);
$classStmt->execute();
$classes = $classStmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="ph-text">
      <nav class="ph-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Dashboard</a> &rsaquo; Enroll Student
      </nav>
      <h1 class="ph-title">Register &amp; Enroll Student</h1>
      <p class="ph-desc">Register a new student and enroll them in a class — saved atomically in a single database transaction.</p>
    </div>
  </div>
</div>

<main>
<div class="main-narrow">

  <?php if ($success): ?>
  <div class="alert alert-success" role="status">
    <div class="alert-body">
      <strong class="alert-title">Student enrolled successfully</strong>
      Both records committed. <a href="enroll_student.php">Enroll another</a> or <a href="index.php">return to dashboard</a>.
    </div>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-error" role="alert">
    <div class="alert-body">
      <strong class="alert-title">Enrollment failed — transaction rolled back</strong>
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($classes)): ?>
  <div class="alert alert-warning" role="alert">
    <div class="alert-body">
      <strong class="alert-title">No scheduled classes found</strong>
      <a href="schedule_class.php">Schedule a class</a> before enrolling a student.
    </div>
  </div>
  <?php else: ?>

  <div class="tx-banner" role="note">
    <div>
      <strong>Transaction protected</strong>
      <p>The student record and enrollment are inserted together. If either write fails, both are rolled back — no partial data is saved.</p>
    </div>
  </div>

  <div class="form-card fade-up">
    <form action="process_enroll_student.php" method="POST" novalidate>

      <div class="form-section">
        <span class="form-section-label">Student Identity</span>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="first_name">First Name <span class="req">*</span></label>
            <input type="text" id="first_name" name="first_name" class="form-control"
                   maxlength="100" required placeholder="e.g. Alice"
                   value="<?= $old_first_name ?>" autocomplete="given-name">
          </div>
          <div class="form-group">
            <label class="form-label" for="last_name">Last Name <span class="req">*</span></label>
            <input type="text" id="last_name" name="last_name" class="form-control"
                   maxlength="100" required placeholder="e.g. Mwangi"
                   value="<?= $old_last_name ?>" autocomplete="family-name">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email Address <span class="req">*</span></label>
          <input type="email" id="email" name="email" class="form-control"
                 maxlength="150" required placeholder="e.g. alice.mwangi@school.ac.ke"
                 value="<?= $old_email ?>" autocomplete="email">
          <p class="form-hint">Must be unique — each student requires their own email address.</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="gender">Gender <span class="req">*</span></label>
          <select id="gender" name="gender" class="form-control" required>
            <option value="">— Select Gender —</option>
            <option value="Male"   <?= ($old_gender==='Male')   ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($old_gender==='Female') ? 'selected' : '' ?>>Female</option>
          </select>
        </div>
      </div>

      <div class="form-section">
        <span class="form-section-label">Enrollment Details</span>
        <div class="form-row">

          <div class="form-group">
            <label class="form-label" for="reg_date">Registration Date <span class="req">*</span></label>
            <input type="date" id="reg_date" name="reg_date" class="form-control"
                   required value="<?= $old_reg_date ?>">
            <p class="form-hint">Date the student is admitted.</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="class_id">Class <span class="req">*</span></label>
            <select id="class_id" name="class_id" class="form-control" required>
              <option value="">— Select Class —</option>
              <?php foreach ($classes as $cls): ?>
                <option value="<?= (int)$cls['class_id'] ?>"
                  <?= ($old_class_id===(int)$cls['class_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cls['course_code'].' — '.$cls['course_name'].' — '.$cls['semester'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">class_id stored as FK in enrollments.</p>
          </div>

        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-accent btn-lg">Register &amp; Enroll Student</button>
        <a href="index.php" class="btn btn-ghost btn-lg">Cancel</a>
      </div>

    </form>
  </div>

  <?php endif; ?>
</div>
</main>

<?php require_once 'includes/footer.php'; ?>
