<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['class_id']??0);
if($id<=0){header('Location: classes.php');exit;}
$s=$pdo->prepare("SELECT cl.class_id,co.course_code,co.course_name,cl.semester,cl.academic_year
     FROM classes cl JOIN courses co ON cl.course_id=co.course_id WHERE cl.class_id=:id");
$s->execute([':id'=>$id]); $cl=$s->fetch(); if(!$cl){header('Location: classes.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['confirm']??'')==='yes'){
    try{$pdo->prepare("DELETE FROM classes WHERE class_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Class deleted.']; header('Location: classes.php');exit;
    }catch(PDOException $e){
        $_SESSION['flash']=['type'=>'error','msg'=>'Cannot delete: class has enrollment records. Delete enrollments first.'];
        header('Location: classes.php');exit;
    }
}
$pageTitle='Delete Class'; $adminPage='classes';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="classes.php">Classes</a> &rsaquo; Delete</div></div>
<div class="delete-card">
  <h2>Delete Class</h2>
  <p>Deleting this class will also remove all related enrollment records. This cannot be undone.</p>
  <div class="delete-info">
    <strong><?=htmlspecialchars($cl['course_code'].' — '.$cl['course_name'],ENT_QUOTES,'UTF-8')?></strong><br>
    <?=htmlspecialchars($cl['semester'],ENT_QUOTES,'UTF-8')?> / <?=(int)$cl['academic_year']?>
  </div>
  <form method="POST"><input type="hidden" name="class_id" value="<?=(int)$id?>"><input type="hidden" name="confirm" value="yes">
    <div class="btn-row"><button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button><a href="classes.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
