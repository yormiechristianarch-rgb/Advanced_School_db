<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??0); if($id<=0){header('Location: classrooms.php');exit;}
$s=$pdo->prepare("SELECT * FROM classrooms WHERE classroom_id=:id");
$s->execute([':id'=>$id]); $room=$s->fetch(); if(!$room){header('Location: classrooms.php');exit;}
$pageTitle='View Classroom'; $adminPage='classrooms';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="classrooms.php">Classrooms</a> &rsaquo; View</div></div>
<div class="view-card">
  <div class="view-card-header"><div class="view-card-title"><?=htmlspecialchars($room['room_number'],ENT_QUOTES,'UTF-8')?></div><div class="view-card-sub"><?=htmlspecialchars($room['building'],ENT_QUOTES,'UTF-8')?></div></div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">Room ID</span><span class="view-value"><?=(int)$room['classroom_id']?></span></div>
    <div class="view-row"><span class="view-label">Room Number</span><span class="view-value"><?=htmlspecialchars($room['room_number'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Building</span><span class="view-value"><?=htmlspecialchars($room['building'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Capacity</span><span class="view-value"><?=(int)$room['capacity']?></span></div>
    <div class="view-row"><span class="view-label">Has Projector</span><span class="view-value"><?=$room['has_projector']?'Yes':'No'?></span></div>
    <div class="view-row"><span class="view-label">Has Lab</span><span class="view-value"><?=$room['has_lab']?'Yes':'No'?></span></div>
  </div>
  <div class="view-card-footer"><a href="classroom_edit.php?id=<?=(int)$id?>" class="btn btn-primary btn-sm">Edit</a><a href="classrooms.php" class="btn btn-ghost btn-sm">Back</a></div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
