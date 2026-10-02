<?php
require_once '../config.php';
require_once 'admin_auth.php';
$pageTitle = 'Instructors';
$adminPage = 'instructors';
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$showAdd = isset($_GET['action']) && $_GET['action']==='add';
$addError = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['_action']??'')==='add') {
    $fn   = trim($_POST['first_name']??''); $ln  = trim($_POST['last_name']??'');
    $em   = trim($_POST['email']??'');      $ph  = trim($_POST['phone']??'');
    $emp  = trim($_POST['employee_number']??''); $did = (int)($_POST['dept_id']??0);
    $hd   = trim($_POST['hire_date']??'');
    $showAdd = true;
    if ($fn===''||$ln===''||$em===''||$emp===''||$did<=0) {
        $addError='First name, last name, email, employee number, and department are required.';
    } elseif (!filter_var($em,FILTER_VALIDATE_EMAIL)) {
        $addError='Invalid email address.';
    } else {
        try {
            $pdo->prepare("INSERT INTO instructors(department_id,first_name,last_name,email,phone,employee_number,hire_date) VALUES(:d,:fn,:ln,:em,:ph,:emp,:hd)")
                ->execute([':d'=>$did,':fn'=>$fn,':ln'=>$ln,':em'=>$em,':ph'=>$ph?:null,':emp'=>$emp,':hd'=>$hd?:null]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Instructor added.']; header('Location: instructors.php'); exit;
        } catch(PDOException $e){
            $addError = $e->getCode()==='23000' ? 'Email or employee number already exists.' : htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');
        }
    }
}
$instructors = $pdo->query("SELECT i.*,d.dept_name FROM instructors i JOIN departments d ON i.department_id=d.department_id ORDER BY i.last_name,i.first_name")->fetchAll();
$depts = $pdo->query("SELECT department_id,dept_name FROM departments ORDER BY dept_name")->fetchAll();
require_once '../includes/admin_sidebar.php';
?>
<?php if($flash):?><div class="admin-alert <?=$flash['type']?>"><strong><?=ucfirst($flash['type'])?></strong><?=htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-ph"><div class="admin-ph-row"><h2 class="admin-ph-title">Instructors</h2><a href="instructors.php?action=add" class="btn btn-primary btn-sm">+ Add Instructor</a></div></div>
<?php if($showAdd):?>
<div class="admin-form-card" style="margin-bottom:28px;">
  <p style="font-weight:700;color:var(--primary);margin-bottom:20px;">Add New Instructor</p>
  <?php if($addError):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($addError,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
  <form method="POST" novalidate>
    <input type="hidden" name="_action" value="add">
    <div class="form-row">
      <div class="form-group"><label class="form-label">First Name <span class="req">*</span></label><input type="text" name="first_name" class="form-control" maxlength="100" required value="<?=htmlspecialchars($_POST['first_name']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Last Name <span class="req">*</span></label><input type="text" name="last_name" class="form-control" maxlength="100" required value="<?=htmlspecialchars($_POST['last_name']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Email <span class="req">*</span></label><input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($_POST['email']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($_POST['phone']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Employee No. <span class="req">*</span></label><input type="text" name="employee_number" class="form-control" required value="<?=htmlspecialchars($_POST['employee_number']??'',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control" value="<?=htmlspecialchars($_POST['hire_date']??'',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Department <span class="req">*</span></label>
      <select name="dept_id" class="form-control" required><option value="">— Select —</option>
      <?php foreach($depts as $d):?><option value="<?=(int)$d['department_id']?>" <?=((int)($_POST['dept_id']??0)===(int)$d['department_id'])?'selected':''?>><?=htmlspecialchars($d['dept_name'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Save</button><a href="instructors.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php endif;?>
<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">All Instructors (<?=count($instructors)?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if(empty($instructors)):?><p class="admin-empty">No instructors found.</p><?php else:?>
    <table id="instructors-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Emp. No.</th><th>Department</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($instructors as $i=>$ins):?>
        <tr>
          <td style="color:var(--text-muted)"><?=$i+1?></td>
          <td><strong><?=htmlspecialchars($ins['first_name'].' '.$ins['last_name'],ENT_QUOTES,'UTF-8')?></strong></td>
          <td><?=htmlspecialchars($ins['email'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($ins['employee_number'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($ins['dept_name'],ENT_QUOTES,'UTF-8')?></td>
          <td><div class="act-row">
            <a href="instructor_view.php?id=<?=(int)$ins['instructor_id']?>" class="btn-view">View</a>
            <a href="instructor_edit.php?id=<?=(int)$ins['instructor_id']?>" class="btn-edit">Edit</a>
            <a href="instructor_delete.php?id=<?=(int)$ins['instructor_id']?>" class="btn-delete" onclick="return confirm('Delete this instructor?')">Delete</a>
          </div></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
    <?php endif;?>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
