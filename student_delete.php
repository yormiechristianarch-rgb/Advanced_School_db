<?php
require_once '../config.php';
require_once 'admin_auth.php';

$id = (int)($_GET['id'] ?? $_POST['student_id'] ?? 0);
if ($id <= 0) { header('Location: students.php'); exit; }

$stmt = $pdo->prepare("SELECT student_id,first_name,last_name,email,reg_number FROM students WHERE student_id=:id");
$stmt->execute([':id'=>$id]);
$student = $stmt->fetch();
if (!$student) { header('Location: students.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm']??'') === 'yes') {
    try {
        $pdo->prepare("DELETE FROM students WHERE student_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Student deleted successfully.'];
        header('Location: students.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['type'=>'error','msg'=>'Cannot delete this student record.'];
        header('Location: students.php'); exit;
    }
}

$pageTitle = 'Delete Student';
$adminPage = 'students';
require_once '../includes/admin_sidebar.php';
?>

<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="students.php">Students</a> &rsaquo; Delete</div>
</div>
<div class="delete-card">
  <h2>Delete Student</h2>
  <p>Deleting this student will also remove all their enrollment records. This cannot be undone.</p>
  <div class="delete-info">
    <strong><?= htmlspecialchars($student['first_name'].' '.$student['last_name'],ENT_QUOTES,'UTF-8') ?></strong><br>
    Reg: <?= htmlspecialchars($student['reg_number'],ENT_QUOTES,'UTF-8') ?> &mdash; <?= htmlspecialchars($student['email'],ENT_QUOTES,'UTF-8') ?>
  </div>
  <form method="POST" action="student_delete.php">
    <input type="hidden" name="student_id" value="<?= (int)$id ?>">
    <input type="hidden" name="confirm"    value="yes">
    <div class="btn-row">
      <button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button>
      <a href="students.php" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
