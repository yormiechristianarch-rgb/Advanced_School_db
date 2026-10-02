<?php
require_once '../config.php'; require_once 'admin_auth.php';
$pageTitle='Enrollments'; $adminPage='enrollments';
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);

$enrollments=$pdo->query(
    "SELECT e.enrollment_id, e.enrollment_date, e.status,
            CONCAT(s.first_name,' ',s.last_name) AS student_name, s.email,
            co.course_code, co.course_name, cl.semester, cl.academic_year,
            CONCAT(i.first_name,' ',i.last_name) AS instructor_name
     FROM enrollments e
     JOIN students    s  ON e.student_id    = s.student_id
     JOIN classes     cl ON e.class_id      = cl.class_id
     JOIN courses     co ON cl.course_id    = co.course_id
     JOIN instructors i  ON cl.instructor_id= i.instructor_id
     ORDER BY e.enrollment_date DESC, e.enrollment_id DESC"
)->fetchAll();

require_once '../includes/admin_sidebar.php';
?>
<?php if($flash):?><div class="admin-alert <?=$flash['type']?>"><strong><?=ucfirst($flash['type'])?></strong><?=htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-ph">
  <div class="admin-ph-row">
    <h2 class="admin-ph-title">Enrollments</h2>
    <a href="../enroll_student.php" class="btn btn-primary btn-sm" target="_blank">+ Enroll Student</a>
  </div>
</div>

<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">All Enrollments (<?=count($enrollments)?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if(empty($enrollments)):?>
      <p class="admin-empty">No enrollments recorded yet.</p>
    <?php else:?>
    <table id="enrollments-table">
      <thead>
        <tr><th>#</th><th>Student</th><th>Course</th><th>Instructor</th><th>Semester</th><th>Date</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach($enrollments as $i=>$e):?>
        <tr>
          <td style="color:var(--text-muted);font-size:0.78rem;"><?=$i+1?></td>
          <td>
            <strong><?=htmlspecialchars($e['student_name'],ENT_QUOTES,'UTF-8')?></strong>
            <div style="font-size:0.75rem;color:var(--text-muted);"><?=htmlspecialchars($e['email'],ENT_QUOTES,'UTF-8')?></div>
          </td>
          <td>
            <strong><?=htmlspecialchars($e['course_code'],ENT_QUOTES,'UTF-8')?></strong>
            <div style="font-size:0.75rem;color:var(--text-muted);"><?=htmlspecialchars($e['course_name'],ENT_QUOTES,'UTF-8')?></div>
          </td>
          <td><?=htmlspecialchars($e['instructor_name'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($e['semester'],ENT_QUOTES,'UTF-8')?> / <?=(int)$e['academic_year']?></td>
          <td style="white-space:nowrap;"><?=htmlspecialchars($e['enrollment_date'],ENT_QUOTES,'UTF-8')?></td>
          <td>
            <?php $sc=$e['status']==='Active'?'badge-green':($e['status']==='Completed'?'badge-blue':'badge-red');?>
            <span class="badge <?=$sc?>"><?=htmlspecialchars($e['status'],ENT_QUOTES,'UTF-8')?></span>
          </td>
          <td>
            <div class="act-row">
              <a href="enrollment_view.php?id=<?=(int)$e['enrollment_id']?>" class="btn-view">View</a>
              <a href="enrollment_delete.php?id=<?=(int)$e['enrollment_id']?>" class="btn-delete"
                 onclick="return confirm('Delete this enrollment record?')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
    <?php endif;?>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
