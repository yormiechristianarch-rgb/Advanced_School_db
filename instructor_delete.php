<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['instructor_id']??0);
if($id<=0){header('Location: instructors.php');exit;}
$s=$pdo->prepare("SELECT instructor_id,first_name,last_name FROM instructors WHERE instructor_id=:id");
$s->execute([':id'=>$id]); $ins=$s->fetch(); if(!$ins){header('Location: instructors.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['confirm']??'')==='yes'){
    try{$pdo->prepare("DELETE FROM instructors WHERE instructor_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Instructor deleted.']; header('Location: instructors.php');exit;
    }catch(PDOException $e){
        $_SESSION['flash']=['type'=>'error','msg'=>'Cannot delete: instructor is assigned to classes.'];
        header('Location: instructors.php');exit;
    }
}
$pageTitle='Delete Instructor'; $adminPage='instructors';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="instructors.php">Instructors</a> &rsaquo; Delete</div></div>
<div class="delete-card">
  <h2>Delete Instructor</h2>
  <p>Are you sure you want to delete this instructor? This action cannot be undone.</p>
  <div class="delete-info"><strong><?=htmlspecialchars($ins['first_name'].' '.$ins['last_name'],ENT_QUOTES,'UTF-8')?></strong></div>
  <form method="POST"><input type="hidden" name="instructor_id" value="<?=(int)$id?>"><input type="hidden" name="confirm" value="yes">
    <div class="btn-row"><button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button><a href="instructors.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
