<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['instructor_id']??0);
if($id<=0){header('Location: instructors.php');exit;}
$s=$pdo->prepare("SELECT * FROM instructors WHERE instructor_id=:id");
$s->execute([':id'=>$id]); $ins=$s->fetch(); if(!$ins){header('Location: instructors.php');exit;}
$depts=$pdo->query("SELECT department_id,dept_name FROM departments ORDER BY dept_name")->fetchAll();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $fn=trim($_POST['first_name']??''); $ln=trim($_POST['last_name']??'');
    $em=trim($_POST['email']??''); $ph=trim($_POST['phone']??'');
    $emp=trim($_POST['employee_number']??''); $did=(int)($_POST['dept_id']??0);
    $hd=trim($_POST['hire_date']??'');
    if($fn===''||$ln===''||$em===''||$emp===''||$did<=0){$error='Required fields missing.';}
    elseif(!filter_var($em,FILTER_VALIDATE_EMAIL)){$error='Invalid email.';}
    else{
        try{
            $pdo->prepare("UPDATE instructors SET first_name=:fn,last_name=:ln,email=:em,phone=:ph,employee_number=:emp,department_id=:d,hire_date=:hd WHERE instructor_id=:id")
                ->execute([':fn'=>$fn,':ln'=>$ln,':em'=>$em,':ph'=>$ph?:null,':emp'=>$emp,':d'=>$did,':hd'=>$hd?:null,':id'=>$id]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Instructor updated.']; header('Location: instructors.php'); exit;
        }catch(PDOException $e){$error=$e->getCode()==='23000'?'Email/employee number already used.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
    $ins=array_merge($ins,['first_name'=>$fn,'last_name'=>$ln,'email'=>$em,'phone'=>$ph,'employee_number'=>$emp,'department_id'=>$did,'hire_date'=>$hd]);
}
$pageTitle='Edit Instructor'; $adminPage='instructors';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="instructors.php">Instructors</a> &rsaquo; Edit</div></div>
<?php if($error):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-form-card">
  <form method="POST" novalidate>
    <input type="hidden" name="instructor_id" value="<?=(int)$id?>">
    <div class="form-row">
      <div class="form-group"><label class="form-label">First Name <span class="req">*</span></label><input type="text" name="first_name" class="form-control" required value="<?=htmlspecialchars($ins['first_name'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Last Name <span class="req">*</span></label><input type="text" name="last_name" class="form-control" required value="<?=htmlspecialchars($ins['last_name'],ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Email <span class="req">*</span></label><input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($ins['email'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($ins['phone']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Employee No. <span class="req">*</span></label><input type="text" name="employee_number" class="form-control" required value="<?=htmlspecialchars($ins['employee_number'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control" value="<?=htmlspecialchars($ins['hire_date']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Department <span class="req">*</span></label>
      <select name="dept_id" class="form-control" required><option value="">— Select —</option>
      <?php foreach($depts as $d):?><option value="<?=(int)$d['department_id']?>" <?=((int)$ins['department_id']===(int)$d['department_id'])?'selected':''?>><?=htmlspecialchars($d['dept_name'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Update</button><a href="instructor_view.php?id=<?=(int)$id?>" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
