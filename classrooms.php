<?php
require_once '../config.php'; require_once 'admin_auth.php';
$pageTitle='Classrooms'; $adminPage='classrooms';
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
$showAdd=isset($_GET['action'])&&$_GET['action']==='add';
$addError='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['_action']??'')==='add'){
    $rn=trim($_POST['room_number']??''); $bld=trim($_POST['building']??'');
    $cap=(int)($_POST['capacity']??0); $proj=(int)($_POST['has_projector']??0); $lab=(int)($_POST['has_lab']??0);
    $showAdd=true;
    if($rn===''||$bld===''||$cap<=0){$addError='Room number, building, and a positive capacity are required.';}
    else{
        try{
            $pdo->prepare("INSERT INTO classrooms(room_number,building,capacity,has_projector,has_lab) VALUES(:r,:b,:c,:p,:l)")
                ->execute([':r'=>$rn,':b'=>$bld,':c'=>$cap,':p'=>$proj,':l'=>$lab]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Classroom added.']; header('Location: classrooms.php');exit;
        }catch(PDOException $e){$addError=$e->getCode()==='23000'?'Room number already exists.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
}
$rooms=$pdo->query("SELECT * FROM classrooms ORDER BY room_number")->fetchAll();
require_once '../includes/admin_sidebar.php';
?>
<?php if($flash):?><div class="admin-alert <?=$flash['type']?>"><strong><?=ucfirst($flash['type'])?></strong><?=htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-ph"><div class="admin-ph-row"><h2 class="admin-ph-title">Classrooms</h2><a href="classrooms.php?action=add" class="btn btn-primary btn-sm">+ Add Classroom</a></div></div>
<?php if($showAdd):?>
<div class="admin-form-card" style="margin-bottom:28px;">
  <p style="font-weight:700;color:var(--primary);margin-bottom:20px;">Add Classroom</p>
  <?php if($addError):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($addError,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
  <form method="POST" novalidate><input type="hidden" name="_action" value="add">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Room Number <span class="req">*</span></label><input type="text" name="room_number" class="form-control" maxlength="20" required value="<?=htmlspecialchars($_POST['room_number']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Building <span class="req">*</span></label><input type="text" name="building" class="form-control" maxlength="100" required value="<?=htmlspecialchars($_POST['building']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Capacity <span class="req">*</span></label><input type="number" name="capacity" class="form-control" min="1" required value="<?=htmlspecialchars($_POST['capacity']??'30',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group" style="display:flex;gap:20px;align-items:flex-end;">
        <label style="display:flex;align-items:center;gap:6px;font-size:0.875rem;font-weight:600;color:var(--text-secondary);">
          <input type="checkbox" name="has_projector" value="1" <?=isset($_POST['has_projector'])?'checked':''?>> Projector</label>
        <label style="display:flex;align-items:center;gap:6px;font-size:0.875rem;font-weight:600;color:var(--text-secondary);">
          <input type="checkbox" name="has_lab" value="1" <?=isset($_POST['has_lab'])?'checked':''?>> Lab</label>
      </div>
    </div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Save</button><a href="classrooms.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php endif;?>
<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">All Classrooms (<?=count($rooms)?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if(empty($rooms)):?><p class="admin-empty">No classrooms found.</p><?php else:?>
    <table id="rooms-table">
      <thead><tr><th>#</th><th>Room No.</th><th>Building</th><th>Capacity</th><th>Projector</th><th>Lab</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($rooms as $i=>$r):?>
        <tr>
          <td style="color:var(--text-muted)"><?=$i+1?></td>
          <td><strong><?=htmlspecialchars($r['room_number'],ENT_QUOTES,'UTF-8')?></strong></td>
          <td><?=htmlspecialchars($r['building'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=(int)$r['capacity']?></td>
          <td><?=$r['has_projector']?'Yes':'No'?></td>
          <td><?=$r['has_lab']?'Yes':'No'?></td>
          <td><div class="act-row">
            <a href="classroom_view.php?id=<?=(int)$r['classroom_id']?>" class="btn-view">View</a>
            <a href="classroom_edit.php?id=<?=(int)$r['classroom_id']?>" class="btn-edit">Edit</a>
            <a href="classroom_delete.php?id=<?=(int)$r['classroom_id']?>" class="btn-delete" onclick="return confirm('Delete room <?=htmlspecialchars($r['room_number'],ENT_QUOTES,'UTF-8')?>?')">Delete</a>
          </div></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
    <?php endif;?>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
