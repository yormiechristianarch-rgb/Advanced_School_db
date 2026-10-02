<?php
require_once '../config.php'; require_once 'admin_auth.php';
$pageTitle='Settings'; $adminPage='settings';
$adminId=(int)$_SESSION['admin_id'];

$stmt=$pdo->prepare("SELECT * FROM admins WHERE admin_id=:id");
$stmt->execute([':id'=>$adminId]); $admin=$stmt->fetch();

$profileError=''; $profileSuccess='';
$pwError=''; $pwSuccess='';

// ── Update profile ────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['_action']??'')==='profile'){
    $fn  =trim($_POST['full_name']??'');
    $em  =trim($_POST['email']??'');
    $user=trim($_POST['username']??'');
    if($fn===''||$em===''||$user===''){$profileError='All profile fields are required.';}
    elseif(!filter_var($em,FILTER_VALIDATE_EMAIL)){$profileError='Invalid email address.';}
    else{
        try{
            $pdo->prepare("UPDATE admins SET full_name=:fn,email=:em,username=:u WHERE admin_id=:id")
                ->execute([':fn'=>$fn,':em'=>$em,':u'=>$user,':id'=>$adminId]);
            $_SESSION['admin_full_name']=$fn;
            $_SESSION['admin_username']=$user;
            $_SESSION['admin_email']=$em;
            $profileSuccess='Profile updated successfully.';
            $admin=array_merge($admin,['full_name'=>$fn,'email'=>$em,'username'=>$user]);
        }catch(PDOException $e){$profileError=$e->getCode()==='23000'?'Username or email already in use.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
}

// ── Change password ───────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['_action']??'')==='password'){
    $cur  = $_POST['current_password'] ?? '';    // never trim passwords
    $new  = $_POST['new_password']     ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    if($cur===''||$new===''||$conf===''){$pwError='All password fields are required.';}
    elseif(!password_verify($cur,$admin['password'])){$pwError='Current password is incorrect.';}
    elseif($new!==$conf){$pwError='New passwords do not match.';}
    elseif(strlen($new)<8){$pwError='New password must be at least 8 characters.';}
    else{
        $hash=password_hash($new,PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admins SET password=:pw WHERE admin_id=:id")->execute([':pw'=>$hash,':id'=>$adminId]);
        $pwSuccess='Password changed successfully.';
        $admin['password']=$hash;
    }
}

require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph">
  <div class="admin-ph-row"><h2 class="admin-ph-title">Account Settings</h2></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;" class="admin-settings-grid">

  <!-- Profile -->
  <div class="admin-form-card">
    <p style="font-weight:700;color:var(--primary);margin-bottom:20px;font-size:1rem;">Profile Information</p>
    <?php if($profileError):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($profileError,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
    <?php if($profileSuccess):?><div class="admin-alert success"><strong>Success</strong><?=htmlspecialchars($profileSuccess,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
    <form method="POST" novalidate>
      <input type="hidden" name="_action" value="profile">
      <div class="form-group">
        <label class="form-label">Full Name <span class="req">*</span></label>
        <input type="text" name="full_name" class="form-control" required value="<?=htmlspecialchars($admin['full_name'],ENT_QUOTES,'UTF-8')?>">
      </div>
      <div class="form-group">
        <label class="form-label">Email <span class="req">*</span></label>
        <input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($admin['email'],ENT_QUOTES,'UTF-8')?>">
      </div>
      <div class="form-group">
        <label class="form-label">Username <span class="req">*</span></label>
        <input type="text" name="username" class="form-control" required autocomplete="username" value="<?=htmlspecialchars($admin['username'],ENT_QUOTES,'UTF-8')?>">
      </div>
      <div class="btn-row"><button type="submit" class="btn btn-primary">Update Profile</button></div>
    </form>
  </div>

  <!-- Change Password -->
  <div class="admin-form-card">
    <p style="font-weight:700;color:var(--primary);margin-bottom:20px;font-size:1rem;">Change Password</p>
    <?php if($pwError):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($pwError,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
    <?php if($pwSuccess):?><div class="admin-alert success"><strong>Success</strong><?=htmlspecialchars($pwSuccess,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
    <form method="POST" novalidate>
      <input type="hidden" name="_action" value="password">
      <div class="form-group">
        <label class="form-label">Current Password <span class="req">*</span></label>
        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
      </div>
      <div class="form-group">
        <label class="form-label">New Password <span class="req">*</span></label>
        <input type="password" name="new_password" class="form-control" required autocomplete="new-password">
        <p class="form-hint">Minimum 8 characters.</p>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm New Password <span class="req">*</span></label>
        <input type="password" name="confirm_password" class="form-control" required autocomplete="new-password">
      </div>
      <div class="btn-row"><button type="submit" class="btn btn-primary">Change Password</button></div>
    </form>
  </div>

</div>

<!-- Account info read-only -->
<div class="admin-form-card" style="margin-top:24px;max-width:400px;">
  <p style="font-weight:700;color:var(--primary);margin-bottom:16px;font-size:0.9rem;">Session Information</p>
  <div class="view-row"><span class="view-label">Admin ID</span><span class="view-value"><?=(int)$admin['admin_id']?></span></div>
  <div class="view-row"><span class="view-label">Username</span><span class="view-value"><?=htmlspecialchars($admin['username'],ENT_QUOTES,'UTF-8')?></span></div>
  <div class="view-row"><span class="view-label">Account Created</span><span class="view-value"><?=htmlspecialchars($admin['created_at'],ENT_QUOTES,'UTF-8')?></span></div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
