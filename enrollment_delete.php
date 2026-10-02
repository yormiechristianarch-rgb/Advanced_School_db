<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['enrollment_id']??0);
if($id<=0){header('Location: enrollments.php');exit;}
$s=$pdo->prepare(
    "SELECT e.enrollment_id,CONCAT(s.first_name,' ',s.last_name) AS student_name,co.course_code,cl.semester
     FROM enrollments e
     JOIN students s ON e.student_id=s.student_id
     JOIN classes cl ON e.class_id=cl.class_id
     JOIN courses co ON cl.course_id=co.course_id
     WHERE e.enrollment_id=:id"
);
$s->execute([':id'=>$id]); $e=$s->fetch(); if(!$e){header('Location: enrollments.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['confirm']??'')==='yes'){
    try{$pdo->prepare("DELETE FROM enrollments WHERE enrollment_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Enrollment deleted.']; header('Location: enrollments.php');exit;
    }catch(PDOException $ex){
        $_SESSION['flash']=['type'=>'error','msg'=>'Cannot delete this enrollment record.'];
        header('Location: enrollments.php');exit;
    }
}
$pageTitle='Delete Enrollment'; $adminPage='enrollments';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="enrollments.php">Enrollments</a> &rsaquo; Delete</div></div>
<div class="delete-card">
  <h2>Delete Enrollment</h2>
  <p>Are you sure you want to delete this enrollment record? Any associated grade records will also be removed.</p>
  <div class="delete-info">
    <strong><?=htmlspecialchars($e['student_name'],ENT_QUOTES,'UTF-8')?></strong><br>
    <?=htmlspecialchars($e['course_code'],ENT_QUOTES,'UTF-8')?> &mdash; <?=htmlspecialchars($e['semester'],ENT_QUOTES,'UTF-8')?>
  </div>
  <form method="POST"><input type="hidden" name="enrollment_id" value="<?=(int)$id?>"><input type="hidden" name="confirm" value="yes">
    <div class="btn-row"><button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button><a href="enrollments.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
