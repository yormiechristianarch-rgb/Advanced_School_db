<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['classroom_id']??0);
if($id<=0){header('Location: classrooms.php');exit;}
$s=$pdo->prepare("SELECT * FROM classrooms WHERE classroom_id=:id");
$s->execute([':id'=>$id]); $room=$s->fetch(); if(!$room){header('Location: classrooms.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $rn=trim($_POST['room_number']??''); $bld=trim($_POST['building']??'');
    $cap=(int)($_POST['capacity']??0); $proj=(int)isset($_POST['has_projector']); $lab=(int)isset($_POST['has_lab']);
    if($rn===''||$bld===''||$cap<=0){$error='Room number, building, and positive capacity required.';}
    else{
        try{
            $pdo->prepare("UPDATE classrooms SET room_number=:r,building=:b,capacity=:c,has_projector=:p,has_lab=:l WHERE classroom_id=:id")
                ->execute([':r'=>$rn,':b'=>$bld,':c'=>$cap,':p'=>$proj,':l'=>$lab,':id'=>$id]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Classroom updated.']; header('Location: classrooms.php');exit;
        }catch(PDOException $e){$error=$e->getCode()==='23000'?'Room number already exists.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
    $room=array_merge($room,['room_number'=>$rn,'building'=>$bld,'capacity'=>$cap,'has_projector'=>$proj,'has_lab'=>$lab]);
}
$pageTitle='Edit Classroom'; $adminPage='classrooms';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="classrooms.php">Classrooms</a> &rsaquo; Edit</div></div>
<?php if($error):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-form-card">
  <form method="POST" novalidate>
    <input type="hidden" name="classroom_id" value="<?=(int)$id?>">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Room Number <span class="req">*</span></label><input type="text" name="room_number" class="form-control" maxlength="20" required value="<?=htmlspecialchars($room['room_number'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Building <span class="req">*</span></label><input type="text" name="building" class="form-control" maxlength="100" required value="<?=htmlspecialchars($room['building'],ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Capacity <span class="req">*</span></label><input type="number" name="capacity" class="form-control" min="1" required value="<?=(int)$room['capacity']?>"></div>
      <div class="form-group" style="display:flex;gap:20px;align-items:flex-end;">
        <label style="display:flex;align-items:center;gap:6px;font-size:0.875rem;font-weight:600;color:var(--text-secondary);">
          <input type="checkbox" name="has_projector" value="1" <?=$room['has_projector']?'checked':''?>> Projector</label>
        <label style="display:flex;align-items:center;gap:6px;font-size:0.875rem;font-weight:600;color:var(--text-secondary);">
          <input type="checkbox" name="has_lab" value="1" <?=$room['has_lab']?'checked':''?>> Lab</label>
      </div>
    </div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Update</button><a href="classroom_view.php?id=<?=(int)$id?>" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
