<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['dept_id']??0);
if($id<=0){header('Location: departments.php');exit;}
$s=$pdo->prepare("SELECT department_id,dept_code,dept_name FROM departments WHERE department_id=:id");
$s->execute([':id'=>$id]); $dept=$s->fetch(); if(!$dept){header('Location: departments.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['confirm']??'')==='yes'){
    try{$pdo->prepare("DELETE FROM departments WHERE department_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Department deleted.']; header('Location: departments.php');exit;
    }catch(PDOException $e){
        $_SESSION['flash']=['type'=>'error','msg'=>'Cannot delete: department is used by courses or instructors. Remove those first.'];
        header('Location: departments.php');exit;
    }
}
$pageTitle='Delete Department'; $adminPage='departments';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="departments.php">Departments</a> &rsaquo; Delete</div></div>
<div class="delete-card">
  <h2>Delete Department</h2>
  <p>Are you sure you want to delete this department? If courses or instructors belong to it, deletion will be blocked.</p>
  <div class="delete-info"><strong><?=htmlspecialchars($dept['dept_code'],ENT_QUOTES,'UTF-8')?></strong> — <?=htmlspecialchars($dept['dept_name'],ENT_QUOTES,'UTF-8')?></div>
  <form method="POST"><input type="hidden" name="dept_id" value="<?=(int)$id?>"><input type="hidden" name="confirm" value="yes">
    <div class="btn-row"><button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button><a href="departments.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
