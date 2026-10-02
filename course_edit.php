<?php
require_once '../config.php';
require_once 'admin_auth.php';

$id = (int)($_GET['id'] ?? $_POST['course_id'] ?? 0);
if ($id <= 0) { header('Location: courses.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id=:id");
$stmt->execute([':id'=>$id]);
$course = $stmt->fetch();
if (!$course) { header('Location: courses.php'); exit; }

$depts = $pdo->query("SELECT department_id,dept_name FROM departments ORDER BY dept_name ASC")->fetchAll();
$levelOptions = ['Undergraduate', 'Postgraduate', 'Diploma', 'Certificate'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code    = trim($_POST['course_code']  ?? '');
    $name    = trim($_POST['course_title'] ?? '');
    $credits = (int)($_POST['credits']     ?? 0);
    $deptId  = (int)($_POST['dept_id']     ?? 0);
    $level   = trim($_POST['level']        ?? 'Undergraduate');
    $desc    = trim($_POST['description']  ?? '');

    if ($code==='' || $name==='' || $credits<1 || $credits>20 || $deptId<=0) {
        $error = 'Course code, title, credits (1–20), and department are required.';
    } else {
        try {
            $pdo->prepare(
                "UPDATE courses
                 SET course_code=:code, course_name=:name, credits=:credits,
                     department_id=:dept, level=:level, description=:desc
                 WHERE course_id=:id"
            )->execute([
                ':code'    => $code,
                ':name'    => $name,
                ':credits' => $credits,
                ':dept'    => $deptId,
                ':level'   => $level,
                ':desc'    => $desc ?: null,
                ':id'      => $id,
            ]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Course updated successfully.'];
            header('Location: courses.php'); exit;
        } catch (PDOException $e) {
            $error = $e->getCode()==='23000'
                ? "Course code \"" . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "\" is already used by another course."
                : 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
    // Re-populate with submitted values on error
    $course = array_merge($course, [
        'course_code'   => $code,
        'course_name'   => $name,
        'credits'       => $credits,
        'department_id' => $deptId,
        'level'         => $level,
        'description'   => $desc,
    ]);
}

$pageTitle = 'Edit Course';
$adminPage = 'courses';
require_once '../includes/admin_sidebar.php';
?>

<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="courses.php">Courses</a> &rsaquo; Edit</div>
  <div class="admin-ph-row"><h2 class="admin-ph-title">Edit Course</h2></div>
</div>

<?php if ($error): ?>
<div class="admin-alert error"><strong>Error</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="admin-form-card">
  <form method="POST" action="course_edit.php" novalidate>
    <input type="hidden" name="course_id" value="<?= (int)$id ?>">

    <!-- Row 1: code + credits -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="course_code">Course Code <span class="req">*</span></label>
        <input type="text" id="course_code" name="course_code" class="form-control"
               maxlength="20" required
               value="<?= htmlspecialchars($course['course_code'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="credits">Credits <span class="req">*</span></label>
        <input type="number" id="credits" name="credits" class="form-control"
               min="1" max="20" required value="<?= (int)$course['credits'] ?>">
        <p class="form-hint">Credit units (1 – 20)</p>
      </div>
    </div>

    <!-- Course title -->
    <div class="form-group">
      <label class="form-label" for="course_title">Course Title <span class="req">*</span></label>
      <input type="text" id="course_title" name="course_title" class="form-control"
             maxlength="200" required
             value="<?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <!-- Row 2: department + level -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="dept_id">Department <span class="req">*</span></label>
        <select id="dept_id" name="dept_id" class="form-control" required>
          <option value="">— Select —</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= (int)$d['department_id'] ?>"
              <?= ((int)$course['department_id'] === (int)$d['department_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['dept_name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="level">Level</label>
        <select id="level" name="level" class="form-control">
          <?php foreach ($levelOptions as $lv): ?>
            <option value="<?= htmlspecialchars($lv, ENT_QUOTES, 'UTF-8') ?>"
              <?= ($course['level'] === $lv) ? 'selected' : '' ?>>
              <?= htmlspecialchars($lv, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Description -->
    <div class="form-group">
      <label class="form-label" for="description">Description</label>
      <textarea id="description" name="description" class="form-control"
                rows="3" maxlength="1000"
                style="resize:vertical;"><?= htmlspecialchars($course['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      <p class="form-hint">Optional short description of the course.</p>
    </div>

    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Update Course</button>
      <a href="course_view.php?id=<?= (int)$id ?>" class="btn btn-ghost">Cancel</a>
    </div>

  </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
