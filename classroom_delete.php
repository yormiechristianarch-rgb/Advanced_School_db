<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['classroom_id']??0);
if($id<=0){header('Location: classrooms.php');exit;}
$s=$pdo->prepare("SELECT classroom_id,room_number,building FROM classrooms WHERE classroom_id=:id");
$s->execute([':id'=>$id]); $room=$s->fetch(); if(!$room){header('Location: classrooms.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['confirm']??'')==='yes'){
    try{$pdo->prepare("DELETE FROM classrooms WHERE classroom_id=:id")->execute([':id'=>$id]);
        $_SESSION['flash']=['type'=>'success','msg'=>'Classroom deleted.']; header('Location: classrooms.php');exit;
    }catch(PDOException $e){
        $_SESSION['flash']=['type'=>'error','msg'=>'Cannot delete: classroom is used by scheduled classes.'];
        header('Location: classrooms.php');exit;
    }
}
$pageTitle='Delete Classroom'; $adminPage='classrooms';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="classrooms.php">Classrooms</a> &rsaquo; Delete</div></div>
<div class="delete-card">
  <h2>Delete Classroom</h2>
  <p>Are you sure you want to delete this classroom?</p>
  <div class="delete-info"><strong><?=htmlspecialchars($room['room_number'],ENT_QUOTES,'UTF-8')?></strong> — <?=htmlspecialchars($room['building'],ENT_QUOTES,'UTF-8')?></div>
  <form method="POST"><input type="hidden" name="classroom_id" value="<?=(int)$id?>"><input type="hidden" name="confirm" value="yes">
    <div class="btn-row"><button type="submit" class="btn btn-primary" style="background:var(--error);border-color:var(--error);">Yes, Delete</button><a href="classrooms.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
