<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['dept_id']??0);
if($id<=0){header('Location: departments.php');exit;}
$s=$pdo->prepare("SELECT * FROM departments WHERE department_id=:id");
$s->execute([':id'=>$id]); $dept=$s->fetch(); if(!$dept){header('Location: departments.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $code=trim($_POST['dept_code']??''); $name=trim($_POST['dept_name']??'');
    $hod=trim($_POST['hod_name']??'');   $yr=(int)($_POST['established_year']??0);
    if($code===''||$name===''){$error='Code and name are required.';}
    else{
        try{
            $pdo->prepare("UPDATE departments SET dept_code=:c,dept_name=:n,hod_name=:h,established_year=:y WHERE department_id=:id")
                ->execute([':c'=>$code,':n'=>$name,':h'=>$hod?:null,':y'=>$yr?:null,':id'=>$id]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Department updated.']; header('Location: departments.php');exit;
        }catch(PDOException $e){$error=$e->getCode()==='23000'?'Code or name already exists.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
    $dept=array_merge($dept,['dept_code'=>$code,'dept_name'=>$name,'hod_name'=>$hod,'established_year'=>$yr]);
}
$pageTitle='Edit Department'; $adminPage='departments';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="departments.php">Departments</a> &rsaquo; Edit</div></div>
<?php if($error):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-form-card">
  <form method="POST" novalidate>
    <input type="hidden" name="dept_id" value="<?=(int)$id?>">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Dept Code <span class="req">*</span></label><input type="text" name="dept_code" class="form-control" maxlength="10" required value="<?=htmlspecialchars($dept['dept_code'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Dept Name <span class="req">*</span></label><input type="text" name="dept_name" class="form-control" maxlength="100" required value="<?=htmlspecialchars($dept['dept_name'],ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Head of Department</label><input type="text" name="hod_name" class="form-control" value="<?=htmlspecialchars($dept['hod_name']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Established Year</label><input type="number" name="established_year" class="form-control" min="1800" max="<?=date('Y')?>" value="<?=htmlspecialchars($dept['established_year']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Update</button><a href="department_view.php?id=<?=(int)$id?>" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
