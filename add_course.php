<?php
require_once 'config.php';
$pageTitle  = 'Add Course';
$activePage = 'add_course';

$success = $_GET['success'] ?? null;
$error   = $_GET['error']   ?? null;

$old_course_code  = htmlspecialchars(trim($_GET['course_code']  ?? ''),  ENT_QUOTES, 'UTF-8');
$old_course_title = htmlspecialchars(trim($_GET['course_title'] ?? ''),  ENT_QUOTES, 'UTF-8');
$old_credits      = htmlspecialchars(trim($_GET['credits']      ?? '3'), ENT_QUOTES, 'UTF-8');
$old_dept_id      = (int) ($_GET['dept_id'] ?? 0);

$deptStmt = $pdo->prepare('SELECT department_id, dept_name FROM departments ORDER BY dept_name ASC');
$deptStmt->execute();
$departments = $deptStmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="ph-text">
      <nav class="ph-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Dashboard</a> &rsaquo; Add Course
      </nav>
      <h1 class="ph-title">Add New Course</h1>
      <p class="ph-desc">Create a course record and assign it to a department.</p>
    </div>
  </div>
</div>

<main>
<div class="main-narrow">

  <?php if ($success): ?>
  <div class="alert alert-success" role="status">
    <div class="alert-body">
      <strong class="alert-title">Course added successfully</strong>
      The course has been saved. <a href="add_course.php">Add another</a> or <a href="index.php">return to dashboard</a>.
    </div>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-error" role="alert">
    <div class="alert-body">
      <strong class="alert-title">Unable to add course</strong>
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($departments)): ?>
  <div class="alert alert-warning" role="alert">
    <div class="alert-body">
      <strong class="alert-title">No departments found</strong>
      Please add at least one department via phpMyAdmin before adding a course.
    </div>
  </div>
  <?php else: ?>

  <div class="form-card fade-up">
    <form action="process_add_course.php" method="POST" novalidate>

      <div class="form-section">
        <span class="form-section-label">Course Identity</span>

        <div class="form-group">
          <label class="form-label" for="course_code">Course Code <span class="req">*</span></label>
          <input type="text" id="course_code" name="course_code" class="form-control"
                 maxlength="20" required placeholder="e.g. CS302" value="<?= $old_course_code ?>" autocomplete="off">
          <p class="form-hint">Unique identifier — e.g. CS101, MA201. Max 20 characters.</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="course_title">Course Title <span class="req">*</span></label>
          <input type="text" id="course_title" name="course_title" class="form-control"
                 maxlength="200" required placeholder="e.g. Database Systems" value="<?= $old_course_title ?>">
        </div>
      </div>

      <div class="form-section">
        <span class="form-section-label">Classification</span>
        <div class="form-row">

          <div class="form-group">
            <label class="form-label" for="credits">Credits <span class="req">*</span></label>
            <input type="number" id="credits" name="credits" class="form-control"
                   min="1" max="20" required value="<?= $old_credits ?>">
            <p class="form-hint">Credit units (1 – 20)</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="dept_id">Department <span class="req">*</span></label>
            <select id="dept_id" name="dept_id" class="form-control" required>
              <option value="">— Select Department —</option>
              <?php foreach ($departments as $dept): ?>
                <option value="<?= (int)$dept['department_id'] ?>"
                  <?= ($old_dept_id === (int)$dept['department_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($dept['dept_name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">Owning department for this course.</p>
          </div>

        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary btn-lg">Save Course</button>
        <a href="index.php" class="btn btn-ghost btn-lg">Cancel</a>
      </div>

    </form>
  </div>

  <?php endif; ?>
</div>
</main>

<?php require_once 'includes/footer.php'; ?>
