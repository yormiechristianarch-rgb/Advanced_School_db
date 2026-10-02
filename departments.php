<?php
require_once '../config.php'; require_once 'admin_auth.php';
$pageTitle='Departments'; $adminPage='departments';
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
$showAdd=isset($_GET['action'])&&$_GET['action']==='add';
$addError='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['_action']??'')==='add'){
    $code=trim($_POST['dept_code']??''); $name=trim($_POST['dept_name']??'');
    $hod=trim($_POST['hod_name']??''); $yr=(int)($_POST['established_year']??0);
    $showAdd=true;
    if($code===''||$name===''){$addError='Department code and name are required.';}
    else{
        try{
            $pdo->prepare("INSERT INTO departments(dept_code,dept_name,hod_name,established_year) VALUES(:c,:n,:h,:y)")
                ->execute([':c'=>$code,':n'=>$name,':h'=>$hod?:null,':y'=>$yr?:null]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Department added.']; header('Location: departments.php');exit;
        }catch(PDOException $e){$addError=$e->getCode()==='23000'?'Department code or name already exists.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
}
$depts=$pdo->query("SELECT * FROM departments ORDER BY dept_name")->fetchAll();
require_once '../includes/admin_sidebar.php';
?>
<?php if($flash):?><div class="admin-alert <?=$flash['type']?>"><strong><?=ucfirst($flash['type'])?></strong><?=htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-ph"><div class="admin-ph-row"><h2 class="admin-ph-title">Departments</h2><a href="departments.php?action=add" class="btn btn-primary btn-sm">+ Add Department</a></div></div>
<?php if($showAdd):?>
<div class="admin-form-card" style="margin-bottom:28px;">
  <p style="font-weight:700;color:var(--primary);margin-bottom:20px;">Add Department</p>
  <?php if($addError):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($addError,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
  <form method="POST" novalidate><input type="hidden" name="_action" value="add">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Dept Code <span class="req">*</span></label><input type="text" name="dept_code" class="form-control" maxlength="10" required value="<?=htmlspecialchars($_POST['dept_code']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Dept Name <span class="req">*</span></label><input type="text" name="dept_name" class="form-control" maxlength="100" required value="<?=htmlspecialchars($_POST['dept_name']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Head of Department</label><input type="text" name="hod_name" class="form-control" value="<?=htmlspecialchars($_POST['hod_name']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Established Year</label><input type="number" name="established_year" class="form-control" min="1800" max="<?=date('Y')?>" value="<?=htmlspecialchars($_POST['established_year']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Save</button><a href="departments.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php endif;?>
<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">All Departments (<?=count($depts)?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if(empty($depts)):?><p class="admin-empty">No departments found.</p><?php else:?>
    <table id="depts-table">
      <thead><tr><th>#</th><th>Code</th><th>Name</th><th>HOD</th><th>Year</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($depts as $i=>$d):?>
        <tr>
          <td style="color:var(--text-muted)"><?=$i+1?></td>
          <td><strong><?=htmlspecialchars($d['dept_code'],ENT_QUOTES,'UTF-8')?></strong></td>
          <td><?=htmlspecialchars($d['dept_name'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($d['hod_name']??'—',ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($d['established_year']??'—',ENT_QUOTES,'UTF-8')?></td>
          <td><div class="act-row">
            <a href="department_view.php?id=<?=(int)$d['department_id']?>" class="btn-view">View</a>
            <a href="department_edit.php?id=<?=(int)$d['department_id']?>" class="btn-edit">Edit</a>
            <a href="department_delete.php?id=<?=(int)$d['department_id']?>" class="btn-delete" onclick="return confirm('Delete department <?=htmlspecialchars($d['dept_name'],ENT_QUOTES,'UTF-8')?>?')">Delete</a>
          </div></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
    <?php endif;?>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
