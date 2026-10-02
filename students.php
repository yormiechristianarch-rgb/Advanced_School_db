<?php
require_once '../config.php';
require_once 'admin_auth.php';

$pageTitle = 'Students';
$adminPage = 'students';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$students = $pdo->query(
    "SELECT student_id,reg_number,first_name,last_name,email,gender,admission_date
     FROM students ORDER BY student_id DESC"
)->fetchAll();

require_once '../includes/admin_sidebar.php';
?>

<?php if ($flash): ?>
<div class="admin-alert <?= $flash['type'] ?>"><strong><?= ucfirst($flash['type']) ?></strong><?= htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8') ?></div>
<?php endif; ?>

<div class="admin-ph">
  <div class="admin-ph-row">
    <h2 class="admin-ph-title">Students</h2>
    <a href="../enroll_student.php" class="btn btn-primary btn-sm" target="_blank">+ Enroll New Student</a>
  </div>
</div>

<div class="admin-table-card">
  <div class="admin-table-toolbar">
    <span class="admin-table-toolbar-title">All Students (<?= count($students) ?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if (empty($students)): ?>
      <p class="admin-empty">No students enrolled yet.</p>
    <?php else: ?>
    <table id="students-table">
      <thead>
        <tr><th>#</th><th>Reg No.</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Gender</th><th>Admitted</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($students as $i => $s): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($s['reg_number'],ENT_QUOTES,'UTF-8') ?></strong></td>
          <td><?= htmlspecialchars($s['first_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= htmlspecialchars($s['last_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= htmlspecialchars($s['email'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= htmlspecialchars($s['gender']??'—',ENT_QUOTES,'UTF-8') ?></td>
          <td><?= htmlspecialchars($s['admission_date'],ENT_QUOTES,'UTF-8') ?></td>
          <td>
            <div class="act-row">
              <a href="student_view.php?id=<?= (int)$s['student_id'] ?>" class="btn-view">View</a>
              <a href="student_edit.php?id=<?= (int)$s['student_id'] ?>" class="btn-edit">Edit</a>
              <a href="student_delete.php?id=<?= (int)$s['student_id'] ?>" class="btn-delete"
                 onclick="return confirm('Delete student <?= htmlspecialchars($s['first_name'].' '.$s['last_name'],ENT_QUOTES,'UTF-8') ?>?')">Delete</a>
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
