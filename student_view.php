<?php
require_once '../config.php';
require_once 'admin_auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: students.php'); exit; }

$s = $pdo->prepare("SELECT * FROM students WHERE student_id=:id");
$s->execute([':id'=>$id]);
$student = $s->fetch();
if (!$student) { header('Location: students.php'); exit; }

$enrollments = $pdo->prepare(
    "SELECT e.enrollment_id,e.enrollment_date,e.status,
            co.course_code,co.course_name,cl.semester,cl.academic_year
     FROM enrollments e
     JOIN classes     cl ON e.class_id   = cl.class_id
     JOIN courses     co ON cl.course_id = co.course_id
     WHERE e.student_id=:id ORDER BY e.enrollment_date DESC"
);
$enrollments->execute([':id'=>$id]);
$enrolList = $enrollments->fetchAll();

$pageTitle = 'View Student';
$adminPage = 'students';
require_once '../includes/admin_sidebar.php';
?>

<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="students.php">Students</a> &rsaquo; View</div>
  <div class="admin-ph-row"><h2 class="admin-ph-title">Student Details</h2></div>
</div>

<div class="view-card" style="margin-bottom:24px;">
  <div class="view-card-header">
    <div class="view-card-title"><?= htmlspecialchars($student['first_name'].' '.$student['last_name'],ENT_QUOTES,'UTF-8') ?></div>
    <div class="view-card-sub">Reg: <?= htmlspecialchars($student['reg_number'],ENT_QUOTES,'UTF-8') ?></div>
  </div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">Student ID</span><span class="view-value"><?= (int)$student['student_id'] ?></span></div>
    <div class="view-row"><span class="view-label">Reg Number</span><span class="view-value"><?= htmlspecialchars($student['reg_number'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">First Name</span><span class="view-value"><?= htmlspecialchars($student['first_name'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Last Name</span><span class="view-value"><?= htmlspecialchars($student['last_name'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Email</span><span class="view-value"><?= htmlspecialchars($student['email'],ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Gender</span><span class="view-value"><?= htmlspecialchars($student['gender']??'—',ENT_QUOTES,'UTF-8') ?></span></div>
    <div class="view-row"><span class="view-label">Admission Date</span><span class="view-value"><?= htmlspecialchars($student['admission_date'],ENT_QUOTES,'UTF-8') ?></span></div>
    <?php if ($student['phone']): ?><div class="view-row"><span class="view-label">Phone</span><span class="view-value"><?= htmlspecialchars($student['phone'],ENT_QUOTES,'UTF-8') ?></span></div><?php endif; ?>
    <?php if ($student['date_of_birth']): ?><div class="view-row"><span class="view-label">Date of Birth</span><span class="view-value"><?= htmlspecialchars($student['date_of_birth'],ENT_QUOTES,'UTF-8') ?></span></div><?php endif; ?>
  </div>
  <div class="view-card-footer">
    <a href="student_edit.php?id=<?= (int)$id ?>" class="btn btn-primary btn-sm">Edit</a>
    <a href="students.php" class="btn btn-ghost btn-sm">Back</a>
  </div>
</div>

<?php if (!empty($enrolList)): ?>
<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">Enrollments (<?= count($enrolList) ?>)</span></div>
  <div class="admin-table-wrap">
    <table>
      <thead><tr><th>#</th><th>Course</th><th>Semester</th><th>Year</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($enrolList as $i => $e): ?>
        <tr>
          <td style="color:var(--text-muted);"><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($e['course_code'],ENT_QUOTES,'UTF-8') ?></strong> <?= htmlspecialchars($e['course_name'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= htmlspecialchars($e['semester'],ENT_QUOTES,'UTF-8') ?></td>
          <td><?= (int)$e['academic_year'] ?></td>
          <td><?= htmlspecialchars($e['enrollment_date'],ENT_QUOTES,'UTF-8') ?></td>
          <td><span class="badge <?= $e['status']==='Active'?'badge-green':($e['status']==='Completed'?'badge-blue':'badge-red') ?>"><?= htmlspecialchars($e['status'],ENT_QUOTES,'UTF-8') ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once '../includes/admin_footer.php'; ?>
