<?php
require_once '../config.php';
require_once 'admin_auth.php';

$id = (int)($_GET['id'] ?? $_POST['course_id'] ?? 0);
if ($id <= 0) { header('Location: courses.php'); exit; }

$stmt = $pdo->prepare("SELECT course_id,course_code,course_name FROM courses WHERE course_id=:id");
$stmt->execute([':id'=>$id]);
$course = $stmt->fetch();
if (!$course) { header('Location: courses.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    try {
        $pdo->prepare("DELETE FROM courses WHERE course_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Course deleted successfully.'];
        header('Location: courses.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Cannot delete this course because it is referenced by scheduled classes. Remove those classes first.'];
        header('Location: courses.php'); exit;
    }
}

$pageTitle = 'Delete Course';
$adminPage = 'courses';
require_once '../includes/admin_sidebar.php';
?>

<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="courses.php">Courses</a> &rsaquo; Delete</div>
</div>

<div class="delete-card">
  <h2>Delete Course</h2>
  <p>Are you sure you want to permanently delete this course? This action cannot be undone.</p>
  <div class="delete-info">
    <strong><?= htmlspecialchars($course['course_code'],ENT_QUOTES,'UTF-8') ?></strong>
    — <?= htmlspecialchars($course['course_name'],ENT_QUOTES,'UTF-8') ?>
  </div>
  <form method="POST" action="course_delete.php">
    <input type="hidden" name="course_id" value="<?= (int)$id ?>">
    <input type="hidden" name="confirm"   value="yes">
    <div class="btn-row">
      <button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button>
      <a href="courses.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
