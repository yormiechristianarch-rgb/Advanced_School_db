<?php
require_once '../config.php';
require_once 'admin_auth.php';

$pageTitle = 'Courses';
$adminPage = 'courses';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ── Handle Add form ───────────────────────────────────────────
$addError = '';
$addSuccess = '';
$showAdd = isset($_GET['action']) && $_GET['action'] === 'add';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $code    = trim($_POST['course_code']  ?? '');
    $name    = trim($_POST['course_title'] ?? '');
    $credits = (int)($_POST['credits']     ?? 0);
    $deptId  = (int)($_POST['dept_id']     ?? 0);
    $level   = trim($_POST['level']        ?? 'Undergraduate');
    $showAdd = true;

    if ($code === '' || $name === '' || $credits < 1 || $credits > 20 || $deptId <= 0) {
        $addError = 'Course code, title, credits (1–20), and department are required.';
    } else {
        try {
            $pdo->prepare("INSERT INTO courses (course_code,course_name,credits,department_id,level)
                           VALUES (:code,:name,:credits,:dept,:level)")
                ->execute([':code'=>$code,':name'=>$name,':credits'=>$credits,':dept'=>$deptId,':level'=>$level]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Course added successfully.'];
            header('Location: courses.php'); exit;
        } catch (PDOException $e) {
            $addError = $e->getCode() === '23000'
                ? "Course code \"" . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "\" already exists."
                : 'Database error: '.htmlspecialchars($e->getMessage(), ENT_QUOTES,'UTF-8');
        }
    }
}

$courses = $pdo->query(
    "SELECT c.course_id,c.course_code,c.course_name,c.credits,d.dept_name
     FROM courses c JOIN departments d ON c.department_id=d.department_id
     ORDER BY c.course_code ASC"
)->fetchAll();

$depts = $pdo->query("SELECT department_id,dept_name FROM departments ORDER BY dept_name ASC")->fetchAll();

require_once '../includes/admin_sidebar.php';
?>

<?php if ($flash): ?>
<div class="admin-alert <?= $flash['type'] ?>"><strong><?= $flash['type']==='success'?'Success':'Error' ?></strong><?= htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8') ?></div>
<?php endif; ?>

<div class="admin-ph">
  <div class="admin-ph-row">
    <h2 class="admin-ph-title">Courses</h2>
    <a href="courses.php?action=add" class="btn btn-primary btn-sm">+ Add Course</a>
  </div>
</div>

<!-- Add Course Form -->
<?php if ($showAdd): ?>
<div class="admin-form-card" style="margin-bottom:28px;">
  <p style="font-weight:700;color:var(--primary);margin-bottom:20px;font-size:1rem;">Add New Course</p>
  <?php if ($addError): ?><div class="admin-alert error"><strong>Error</strong><?= htmlspecialchars($addError,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
  <form method="POST" action="courses.php" novalidate>
    <input type="hidden" name="_action" value="add">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" for="course_code">Course Code <span class="req">*</span></label>
        <input type="text" id="course_code" name="course_code" class="form-control" maxlength="20" required placeholder="e.g. CS302" value="<?= htmlspecialchars($_POST['course_code']??'',ENT_QUOTES,'UTF-8') ?>">
      </div>
      <div class="form-group">
        <label class="form-label" for="credits">Credits <span class="req">*</span></label>
        <input type="number" id="credits" name="credits" class="form-control" min="1" max="20" required value="<?= htmlspecialchars($_POST['credits']??'3',ENT_QUOTES,'UTF-8') ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" for="course_title">Course Title <span class="req">*</span></label>
      <input type="text" id="course_title" name="course_title" class="form-control" maxlength="200" required placeholder="e.g. Database Systems" value="<?= htmlspecialchars($_POST['course_title']??'',ENT_QUOTES,'UTF-8') ?>">
    </div>
    <div class="form-group">
      <label class="form-label" for="dept_id">Department <span class="req">*</span></label>
      <select id="dept_id" name="dept_id" class="form-control" required>
        <option value="">— Select Department —</option>
        <?php foreach ($depts as $d): ?>
          <option value="<?= (int)$d['department_id'] ?>" <?= ((int)($_POST['dept_id']??0)===(int)$d['department_id'])?'selected':'' ?>><?= htmlspecialchars($d['dept_name'],ENT_QUOTES,'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="level">Level</label>
      <select id="level" name="level" class="form-control">
        <?php foreach (['Undergraduate','Postgraduate','Diploma','Certificate'] as $lv): ?>
          <option value="<?= $lv ?>" <?= (($_POST['level']??'Undergraduate')===$lv)?'selected':'' ?>><?= $lv ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Save Course</button>
      <a href="courses.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Courses Table -->
<div class="admin-table-card">
  <div class="admin-table-toolbar">
    <span class="admin-table-toolbar-title">All Courses (<?= count($courses) ?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if (empty($courses)): ?>
      <p class="admin-empty">No courses found. <a href="courses.php?action=add">Add the first course.</a></p>
    <?php else: ?>
    <table id="courses-table">
      <thead><tr><th>#</th><th>Code</th><th>Title</th><th>Credits</th><th>Department</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($courses as $i => $c): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($c['course_code'],ENT_QUOTES,'UTF-8') ?></strong></td>
          <td><?= htmlspecialchars($c['course_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= (int)$c['credits'] ?></td>
          <td><?= htmlspecialchars($c['dept_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td>
            <div class="act-row">
              <a href="course_view.php?id=<?= (int)$c['course_id'] ?>" class="btn-view">View</a>
              <a href="course_edit.php?id=<?= (int)$c['course_id'] ?>" class="btn-edit">Edit</a>
              <a href="course_delete.php?id=<?= (int)$c['course_id'] ?>" class="btn-delete"
                 onclick="return confirm('Delete course <?= htmlspecialchars($c['course_code'],ENT_QUOTES,'UTF-8') ?>? This cannot be undone.')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
