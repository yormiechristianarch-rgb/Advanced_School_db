<?php
require_once 'config.php';
$pageTitle  = 'Schedule Class';
$activePage = 'schedule_class';

$success = $_GET['success'] ?? null;
$error   = $_GET['error']   ?? null;

$old_semester      = $_GET['semester']      ?? '';
$old_course_id     = (int)($_GET['course_id']     ?? 0);
$old_instructor_id = (int)($_GET['instructor_id'] ?? 0);
$old_classroom_id  = (int)($_GET['classroom_id']  ?? 0);

$courseStmt = $pdo->prepare('SELECT course_id, course_code, course_name FROM courses ORDER BY course_code ASC');
$courseStmt->execute();
$courses = $courseStmt->fetchAll();

$instrStmt = $pdo->prepare('SELECT instructor_id, first_name, last_name FROM instructors ORDER BY last_name ASC, first_name ASC');
$instrStmt->execute();
$instructors = $instrStmt->fetchAll();

$roomStmt = $pdo->prepare('SELECT classroom_id, room_number, building, capacity FROM classrooms ORDER BY room_number ASC');
$roomStmt->execute();
$classrooms = $roomStmt->fetchAll();

$semesterOptions = ['Semester 1', 'Semester 2', 'Semester 3'];
$formReady = !empty($courses) && !empty($instructors) && !empty($classrooms);

require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="ph-text">
      <nav class="ph-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Dashboard</a> &rsaquo; Schedule Class
      </nav>
      <h1 class="ph-title">Schedule a Class</h1>
      <p class="ph-desc">Assign a course, instructor, and classroom to create a scheduled class offering.</p>
    </div>
  </div>
</div>

<main>
<div class="main-narrow">

  <?php if ($success): ?>
  <div class="alert alert-success" role="status">
    <div class="alert-body">
      <strong class="alert-title">Class scheduled successfully</strong>
      <a href="schedule_class.php">Schedule another</a> or <a href="index.php">return to dashboard</a>.
    </div>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-error" role="alert">
    <div class="alert-body">
      <strong class="alert-title">Scheduling failed</strong>
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  </div>
  <?php endif; ?>

  <?php
  $missing = [];
  if (empty($courses))     $missing[] = '<a href="add_course.php">add a course</a>';
  if (empty($instructors)) $missing[] = 'add an instructor (via phpMyAdmin)';
  if (empty($classrooms))  $missing[] = 'add a classroom (via phpMyAdmin)';
  if (!empty($missing)):
  ?>
  <div class="alert alert-warning" role="alert">
    <div class="alert-body">
      <strong class="alert-title">Missing data</strong>
      Before scheduling a class, please <?= implode(', ', $missing) ?>.
    </div>
  </div>
  <?php endif; ?>

  <?php if ($formReady): ?>
  <div class="form-card fade-up">
    <form action="process_schedule_class.php" method="POST" novalidate>

      <div class="form-section">
        <span class="form-section-label">Scheduling Details</span>

        <div class="form-group">
          <label class="form-label" for="semester">Semester <span class="req">*</span></label>
          <select id="semester" name="semester" class="form-control" required>
            <option value="">— Select Semester —</option>
            <?php foreach ($semesterOptions as $sem): ?>
              <option value="<?= htmlspecialchars($sem, ENT_QUOTES, 'UTF-8') ?>"
                <?= ($old_semester === $sem) ? 'selected' : '' ?>>
                <?= htmlspecialchars($sem, ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="course_id">Course <span class="req">*</span></label>
          <select id="course_id" name="course_id" class="form-control" required>
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $course): ?>
              <option value="<?= (int)$course['course_id'] ?>"
                <?= ($old_course_id === (int)$course['course_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($course['course_code'].' — '.$course['course_name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="form-hint">course_id is stored as a foreign key in classes.</p>
        </div>
      </div>

      <div class="form-section">
        <span class="form-section-label">Assignment</span>
        <div class="form-row">

          <div class="form-group">
            <label class="form-label" for="instructor_id">Instructor <span class="req">*</span></label>
            <select id="instructor_id" name="instructor_id" class="form-control" required>
              <option value="">— Select Instructor —</option>
              <?php foreach ($instructors as $instr): ?>
                <option value="<?= (int)$instr['instructor_id'] ?>"
                  <?= ($old_instructor_id === (int)$instr['instructor_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($instr['first_name'].' '.$instr['last_name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">instructor_id stored as FK in classes.</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="classroom_id">Classroom <span class="req">*</span></label>
            <select id="classroom_id" name="classroom_id" class="form-control" required>
              <option value="">— Select Classroom —</option>
              <?php foreach ($classrooms as $room): ?>
                <option value="<?= (int)$room['classroom_id'] ?>"
                  <?= ($old_classroom_id === (int)$room['classroom_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($room['room_number'].' — '.$room['building'].' (Cap: '.(int)$room['capacity'].')', ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">classroom_id stored as FK in classes.</p>
          </div>

        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn btn-primary btn-lg">Schedule Class</button>
        <a href="index.php" class="btn btn-ghost btn-lg">Cancel</a>
      </div>

    </form>
  </div>
  <?php endif; ?>

</div>
</main>

<?php require_once 'includes/footer.php'; ?>
