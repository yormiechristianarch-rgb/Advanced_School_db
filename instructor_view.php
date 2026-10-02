<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??0); if($id<=0){header('Location: instructors.php');exit;}
$s=$pdo->prepare("SELECT i.*,d.dept_name FROM instructors i JOIN departments d ON i.department_id=d.department_id WHERE i.instructor_id=:id");
$s->execute([':id'=>$id]); $ins=$s->fetch(); if(!$ins){header('Location: instructors.php');exit;}
$pageTitle='View Instructor'; $adminPage='instructors';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="instructors.php">Instructors</a> &rsaquo; View</div></div>
<div class="view-card">
  <div class="view-card-header">
    <div class="view-card-title"><?=htmlspecialchars($ins['first_name'].' '.$ins['last_name'],ENT_QUOTES,'UTF-8')?></div>
    <div class="view-card-sub"><?=htmlspecialchars($ins['dept_name'],ENT_QUOTES,'UTF-8')?></div>
  </div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">ID</span><span class="view-value"><?=(int)$ins['instructor_id']?></span></div>
    <div class="view-row"><span class="view-label">First Name</span><span class="view-value"><?=htmlspecialchars($ins['first_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Last Name</span><span class="view-value"><?=htmlspecialchars($ins['last_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Email</span><span class="view-value"><?=htmlspecialchars($ins['email'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Phone</span><span class="view-value"><?=htmlspecialchars($ins['phone']??'—',ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Employee No.</span><span class="view-value"><?=htmlspecialchars($ins['employee_number'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Department</span><span class="view-value"><?=htmlspecialchars($ins['dept_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Hire Date</span><span class="view-value"><?=htmlspecialchars($ins['hire_date']??'—',ENT_QUOTES,'UTF-8')?></span></div>
  </div>
  <div class="view-card-footer">
    <a href="instructor_edit.php?id=<?=(int)$id?>" class="btn btn-primary btn-sm">Edit</a>
    <a href="instructors.php" class="btn btn-ghost btn-sm">Back</a>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
