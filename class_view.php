<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??0); if($id<=0){header('Location: classes.php');exit;}
$s=$pdo->prepare(
    "SELECT cl.*,co.course_code,co.course_name,
            CONCAT(i.first_name,' ',i.last_name) AS instructor_name,
            cr.room_number,cr.building,cr.capacity
     FROM classes cl
     JOIN courses co ON cl.course_id=co.course_id
     JOIN instructors i ON cl.instructor_id=i.instructor_id
     LEFT JOIN classrooms cr ON cl.classroom_id=cr.classroom_id
     WHERE cl.class_id=:id"
);
$s->execute([':id'=>$id]); $cl=$s->fetch(); if(!$cl){header('Location: classes.php');exit;}

$enrollments=$pdo->prepare(
    "SELECT e.enrollment_id,e.enrollment_date,e.status,
            CONCAT(s.first_name,' ',s.last_name) AS student_name, s.email
     FROM enrollments e JOIN students s ON e.student_id=s.student_id
     WHERE e.class_id=:id ORDER BY s.last_name,s.first_name"
);
$enrollments->execute([':id'=>$id]); $enrolList=$enrollments->fetchAll();

$pageTitle='View Class'; $adminPage='classes';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph">
  <div class="admin-ph-breadcrumb"><a href="classes.php">Classes</a> &rsaquo; View</div>
</div>
<div class="view-card" style="margin-bottom:24px;">
  <div class="view-card-header">
    <div class="view-card-title"><?=htmlspecialchars($cl['course_code'].' — '.$cl['course_name'],ENT_QUOTES,'UTF-8')?></div>
    <div class="view-card-sub"><?=htmlspecialchars($cl['semester'],ENT_QUOTES,'UTF-8')?> / <?=(int)$cl['academic_year']?></div>
  </div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">Class ID</span><span class="view-value"><?=(int)$cl['class_id']?></span></div>
    <div class="view-row"><span class="view-label">Course</span><span class="view-value"><?=htmlspecialchars($cl['course_code'].' — '.$cl['course_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Instructor</span><span class="view-value"><?=htmlspecialchars($cl['instructor_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Classroom</span><span class="view-value"><?=htmlspecialchars($cl['room_number']??'Online/TBA',ENT_QUOTES,'UTF-8')?><?=$cl['building']?' — '.htmlspecialchars($cl['building'],ENT_QUOTES,'UTF-8'):''?></span></div>
    <div class="view-row"><span class="view-label">Semester</span><span class="view-value"><?=htmlspecialchars($cl['semester'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Academic Year</span><span class="view-value"><?=(int)$cl['academic_year']?></span></div>
    <div class="view-row"><span class="view-label">Schedule Day</span><span class="view-value"><?=htmlspecialchars($cl['schedule_day'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Time</span><span class="view-value"><?=htmlspecialchars($cl['start_time'],ENT_QUOTES,'UTF-8')?> &ndash; <?=htmlspecialchars($cl['end_time'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Max Students</span><span class="view-value"><?=(int)$cl['max_students']?></span></div>
    <div class="view-row"><span class="view-label">Enrolled</span><span class="view-value"><?=count($enrolList)?></span></div>
  </div>
  <div class="view-card-footer">
    <a href="class_edit.php?id=<?=(int)$id?>" class="btn btn-primary btn-sm">Edit</a>
    <a href="classes.php" class="btn btn-ghost btn-sm">Back</a>
  </div>
</div>

<?php if(!empty($enrolList)):?>
<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">Enrolled Students (<?=count($enrolList)?>)</span></div>
  <div class="admin-table-wrap">
    <table>
      <thead><tr><th>#</th><th>Student</th><th>Email</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach($enrolList as $i=>$e):?>
        <tr>
          <td style="color:var(--text-muted)"><?=$i+1?></td>
          <td><strong><?=htmlspecialchars($e['student_name'],ENT_QUOTES,'UTF-8')?></strong></td>
          <td><?=htmlspecialchars($e['email'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($e['enrollment_date'],ENT_QUOTES,'UTF-8')?></td>
          <td><span class="badge <?=$e['status']==='Active'?'badge-green':($e['status']==='Completed'?'badge-blue':'badge-red')?>"><?=htmlspecialchars($e['status'],ENT_QUOTES,'UTF-8')?></span></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<?php endif;?>
<?php require_once '../includes/admin_footer.php'; ?>
