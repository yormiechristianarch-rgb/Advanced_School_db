<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??0); if($id<=0){header('Location: departments.php');exit;}
$s=$pdo->prepare("SELECT * FROM departments WHERE department_id=:id");
$s->execute([':id'=>$id]); $dept=$s->fetch(); if(!$dept){header('Location: departments.php');exit;}
$pageTitle='View Department'; $adminPage='departments';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="departments.php">Departments</a> &rsaquo; View</div></div>
<div class="view-card">
  <div class="view-card-header"><div class="view-card-title"><?=htmlspecialchars($dept['dept_name'],ENT_QUOTES,'UTF-8')?></div><div class="view-card-sub">Code: <?=htmlspecialchars($dept['dept_code'],ENT_QUOTES,'UTF-8')?></div></div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">ID</span><span class="view-value"><?=(int)$dept['department_id']?></span></div>
    <div class="view-row"><span class="view-label">Code</span><span class="view-value"><?=htmlspecialchars($dept['dept_code'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Name</span><span class="view-value"><?=htmlspecialchars($dept['dept_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">HOD</span><span class="view-value"><?=htmlspecialchars($dept['hod_name']??'—',ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Est. Year</span><span class="view-value"><?=htmlspecialchars($dept['established_year']??'—',ENT_QUOTES,'UTF-8')?></span></div>
  </div>
  <div class="view-card-footer"><a href="department_edit.php?id=<?=(int)$id?>" class="btn btn-primary btn-sm">Edit</a><a href="departments.php" class="btn btn-ghost btn-sm">Back</a></div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
