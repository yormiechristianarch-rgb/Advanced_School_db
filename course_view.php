<?php
require_once '../config.php';
require_once 'admin_auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: courses.php'); exit; }

$stmt = $pdo->prepare(
    "SELECT c.*,d.dept_name FROM courses c
     JOIN departments d ON c.department_id=d.department_id
     WHERE c.course_id=:id"
);
$stmt->execute([':id'=>$id]);
$course = $stmt->fetch();
if (!$course) { header('Location: courses.php'); exit; }

// Classes that use this course
$classes = $pdo->prepare(
    "SELECT cl.class_id,cl.semester,cl.academic_year,
            CONCAT(i.first_name,' ',i.last_name) AS instructor
     FROM classes cl JOIN instructors i ON cl.instructor_id=i.instructor_id
     WHERE cl.course_id=:id ORDER BY cl.academic_year DESC,cl.semester ASC"
);
$classes->execute([':id'=>$id]);
$classList = $classes->fetchAll();

$pageTitle = 'View Course';
$adminPage = 'courses';
require_once '../includes/admin_sidebar.php';
?>

<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="courses.php">Courses</a> &rsaquo; View</div>
  <div class="admin-ph-row"><h2 class="admin-ph-title">Course Details</h2></div>
</div>

<div class="view-card" style="margin-bottom:24px;">
  <div class="view-card-header">
    <div class="view-card-title"><?= htmlspecialchars($course['course_code'],ENT_QUOTES,'UTF-8') ?> — <?= htmlspecialchars($course['course_name'],ENT_QUOTES,'UTF-8') ?></div>
    <div class="view-card-sub">Course ID: <?= (int)$course['course_id'] ?></div>
  </div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">Course Code</span><span class="view-value"><?= htmlspecialchars($course['course_code'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Course Title</span><span class="view-value"><?= htmlspecialchars($course['course_name'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Credits</span><span class="view-value"><?= (int)$course['credits'] ?></span></div>
    <div class="view-row"><span class="view-label">Department</span><span class="view-value"><?= htmlspecialchars($course['dept_name'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Level</span><span class="view-value"><?= htmlspecialchars($course['level'],ENT_QUOTES,'UTF-8') ?></span></div>
    <?php if ($course['description']): ?>
    <div class="view-row"><span class="view-label">Description</span><span class="view-value"><?= htmlspecialchars($course['description'],ENT_QUOTES,'UTF-8') ?></span></div>
    <?php endif; ?>
  </div>
  <div class="view-card-footer">
    <a href="course_edit.php?id=<?= (int)$course['course_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
    <a href="courses.php" class="btn btn-ghost btn-sm">Back</a>
  </div>
</div>

<?php if (!empty($classList)): ?>
<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">Scheduled Classes (<?= count($classList) ?>)</span></div>
  <div class="admin-table-wrap">
    <table>
      <thead><tr><th>#</th><th>Semester</th><th>Year</th><th>Instructor</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($classList as $i => $cl): ?>
        <tr>
          <td style="color:var(--text-muted);"><?= $i+1 ?></td>
          <td><?= htmlspecialchars($cl['semester'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= (int)$cl['academic_year'] ?></td>
          <td><?= htmlspecialchars($cl['instructor'],ENT_QUOTES,'UTF-8') ?></td>
          <td><a href="class_view.php?id=<?= (int)$cl['class_id'] ?>" class="btn-view">View</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once '../includes/admin_footer.php'; ?>
