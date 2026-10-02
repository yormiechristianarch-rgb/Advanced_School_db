<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??0); if($id<=0){header('Location: enrollments.php');exit;}
$s=$pdo->prepare(
    "SELECT e.*,
            CONCAT(s.first_name,' ',s.last_name) AS student_name, s.email, s.reg_number,
            co.course_code,co.course_name,cl.semester,cl.academic_year,cl.schedule_day,
            CONCAT(i.first_name,' ',i.last_name) AS instructor_name,
            cr.room_number
     FROM enrollments e
     JOIN students s    ON e.student_id=s.student_id
     JOIN classes cl    ON e.class_id=cl.class_id
     JOIN courses co    ON cl.course_id=co.course_id
     JOIN instructors i ON cl.instructor_id=i.instructor_id
     LEFT JOIN classrooms cr ON cl.classroom_id=cr.classroom_id
     WHERE e.enrollment_id=:id"
);
$s->execute([':id'=>$id]); $e=$s->fetch(); if(!$e){header('Location: enrollments.php');exit;}
$pageTitle='View Enrollment'; $adminPage='enrollments';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="enrollments.php">Enrollments</a> &rsaquo; View</div></div>
<div class="view-card">
  <div class="view-card-header">
    <div class="view-card-title">Enrollment #<?=(int)$e['enrollment_id']?></div>
    <div class="view-card-sub"><?=htmlspecialchars($e['student_name'],ENT_QUOTES,'UTF-8')?> &mdash; <?=htmlspecialchars($e['course_code'],ENT_QUOTES,'UTF-8')?></div>
  </div>
  <div class="view-card-body">
    <div class="view-row"><span class="view-label">Enrollment ID</span><span class="view-value"><?=(int)$e['enrollment_id']?></span></div>
    <div class="view-row"><span class="view-label">Student</span><span class="view-value"><?=htmlspecialchars($e['student_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Reg Number</span><span class="view-value"><?=htmlspecialchars($e['reg_number'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Email</span><span class="view-value"><?=htmlspecialchars($e['email'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Course</span><span class="view-value"><?=htmlspecialchars($e['course_code'].' — '.$e['course_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Instructor</span><span class="view-value"><?=htmlspecialchars($e['instructor_name'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Semester</span><span class="view-value"><?=htmlspecialchars($e['semester'],ENT_QUOTES,'UTF-8')?> / <?=(int)$e['academic_year']?></span></div>
    <div class="view-row"><span class="view-label">Schedule</span><span class="view-value"><?=htmlspecialchars($e['schedule_day'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Classroom</span><span class="view-value"><?=htmlspecialchars($e['room_number']??'Online/TBA',ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Enrollment Date</span><span class="view-value"><?=htmlspecialchars($e['enrollment_date'],ENT_QUOTES,'UTF-8')?></span></div>
    <div class="view-row"><span class="view-label">Status</span>
      <span class="view-value"><span class="badge <?=$e['status']==='Active'?'badge-green':($e['status']==='Completed'?'badge-blue':'badge-red')?>"><?=htmlspecialchars($e['status'],ENT_QUOTES,'UTF-8')?></span></span>
    </div>
  </div>
  <div class="view-card-footer">
    <a href="enrollment_delete.php?id=<?=(int)$id?>" class="btn btn-primary btn-sm" style="background:var(--error);border-color:var(--error);">Delete</a>
    <a href="enrollments.php" class="btn btn-ghost btn-sm">Back</a>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
