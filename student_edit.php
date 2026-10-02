<?php
require_once '../config.php';
require_once 'admin_auth.php';

$id = (int)($_GET['id'] ?? $_POST['student_id'] ?? 0);
if ($id <= 0) { header('Location: students.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id=:id");
$stmt->execute([':id'=>$id]);
$student = $stmt->fetch();
if (!$student) { header('Location: students.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname  = trim($_POST['first_name'] ?? '');
    $lname  = trim($_POST['last_name']  ?? '');
    $email  = trim($_POST['email']      ?? '');
    $gender = trim($_POST['gender']     ?? '');
    $date   = trim($_POST['admission_date'] ?? '');
    $phone  = trim($_POST['phone']      ?? '');

    if ($fname==='' || $lname==='' || $email==='' || $date==='') {
        $error = 'First name, last name, email, and admission date are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $pdo->prepare("UPDATE students SET first_name=:fn,last_name=:ln,email=:em,gender=:g,admission_date=:d,phone=:ph WHERE student_id=:id")
                ->execute([':fn'=>$fname,':ln'=>$lname,':em'=>$email,':g'=>$gender?:null,':d'=>$date,':ph'=>$phone?:null,':id'=>$id]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Student updated successfully.'];
            header('Location: students.php'); exit;
        } catch (PDOException $e) {
            $error = $e->getCode()==='23000'
                ? 'That email address is already used by another student.'
                : 'Database error: '.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');
        }
    }
    $student = array_merge($student,['first_name'=>$fname,'last_name'=>$lname,'email'=>$email,'gender'=>$gender,'admission_date'=>$date,'phone'=>$phone]);
}

$pageTitle = 'Edit Student';
$adminPage = 'students';
require_once '../includes/admin_sidebar.php';
?>

<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="students.php">Students</a> &rsaquo; Edit</div>
  <div class="admin-ph-row"><h2 class="admin-ph-title">Edit Student</h2></div>
</div>

<?php if ($error): ?><div class="admin-alert error"><strong>Error</strong><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>

<div class="admin-form-card">
  <form method="POST" action="student_edit.php" novalidate>
    <input type="hidden" name="student_id" value="<?= (int)$id ?>">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">First Name <span class="req">*</span></label>
        <input type="text" name="first_name" class="form-control" maxlength="100" required value="<?= htmlspecialchars($student['first_name'],ENT_QUOTES,'UTF-8') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Last Name <span class="req">*</span></label>
        <input type="text" name="last_name" class="form-control" maxlength="100" required value="<?= htmlspecialchars($student['last_name'],ENT_QUOTES,'UTF-8') ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Email <span class="req">*</span></label>
      <input type="email" name="email" class="form-control" maxlength="150" required value="<?= htmlspecialchars($student['email'],ENT_QUOTES,'UTF-8') ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control">
          <option value="">— Select —</option>
          <option value="Male"   <?= $student['gender']==='Male'  ?'selected':'' ?>>Male</option>
          <option value="Female" <?= $student['gender']==='Female'?'selected':'' ?>>Female</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" maxlength="20" value="<?= htmlspecialchars($student['phone']??'',ENT_QUOTES,'UTF-8') ?>">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Admission Date <span class="req">*</span></label>
      <input type="date" name="admission_date" class="form-control" required value="<?= htmlspecialchars($student['admission_date'],ENT_QUOTES,'UTF-8') ?>">
    </div>
    <div class="btn-row">
      <button type="submit" class="btn btn-primary">Update Student</button>
      <a href="student_view.php?id=<?= (int)$id ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
