<?php
require_once '../config.php'; require_once 'admin_auth.php';
$pageTitle='Classes'; $adminPage='classes';
$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
$showAdd=isset($_GET['action'])&&$_GET['action']==='add';
$addError='';
$semesters=['Semester 1','Semester 2','Semester 3'];

if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['_action']??'')==='add'){
    $cid=(int)($_POST['course_id']??0); $iid=(int)($_POST['instructor_id']??0);
    $rid=(int)($_POST['classroom_id']??0); $sem=trim($_POST['semester']??'');
    $yr=(int)($_POST['academic_year']??date('Y'));
    $sd=trim($_POST['schedule_day']??'TBA'); $st=trim($_POST['start_time']??'08:00');
    $et=trim($_POST['end_time']??'10:00'); $max=(int)($_POST['max_students']??40);
    $showAdd=true;
    if($cid<=0||$iid<=0||$sem===''||!in_array($sem,$semesters,true)){
        $addError='Course, instructor, and a valid semester are required.';
    } else {
        try{
            $pdo->prepare("INSERT INTO classes(course_id,instructor_id,classroom_id,semester,academic_year,schedule_day,start_time,end_time,max_students)
                           VALUES(:ci,:ii,:ri,:sem,:yr,:sd,:st,:et,:max)")
                ->execute([':ci'=>$cid,':ii'=>$iid,':ri'=>$rid?:null,':sem'=>$sem,':yr'=>$yr,
                           ':sd'=>$sd?:'TBA',':st'=>$st?:'08:00:00',':et'=>$et?:'10:00:00',':max'=>$max?:40]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Class scheduled.']; header('Location: classes.php');exit;
        }catch(PDOException $e){$addError=$e->getCode()==='23000'?'This course/instructor/semester combination already exists.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
}

$classes=$pdo->query(
    "SELECT cl.class_id,co.course_code,co.course_name,
            CONCAT(i.first_name,' ',i.last_name) AS instructor,
            cr.room_number,cl.semester,cl.academic_year,cl.max_students
     FROM classes cl
     JOIN courses     co ON cl.course_id=co.course_id
     JOIN instructors i  ON cl.instructor_id=i.instructor_id
     LEFT JOIN classrooms cr ON cl.classroom_id=cr.classroom_id
     ORDER BY cl.academic_year DESC,cl.semester,co.course_code"
)->fetchAll();

$courses=$pdo->query("SELECT course_id,course_code,course_name FROM courses ORDER BY course_code")->fetchAll();
$instructors=$pdo->query("SELECT instructor_id,first_name,last_name FROM instructors ORDER BY last_name,first_name")->fetchAll();
$rooms=$pdo->query("SELECT classroom_id,room_number,building,capacity FROM classrooms ORDER BY room_number")->fetchAll();

require_once '../includes/admin_sidebar.php';
?>
<?php if($flash):?><div class="admin-alert <?=$flash['type']?>"><strong><?=ucfirst($flash['type'])?></strong><?=htmlspecialchars($flash['msg'],ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-ph"><div class="admin-ph-row"><h2 class="admin-ph-title">Classes</h2><a href="classes.php?action=add" class="btn btn-primary btn-sm">+ Schedule Class</a></div></div>

<?php if($showAdd):?>
<div class="admin-form-card" style="margin-bottom:28px;">
  <p style="font-weight:700;color:var(--primary);margin-bottom:20px;">Schedule New Class</p>
  <?php if($addError):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($addError,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
  <form method="POST" novalidate><input type="hidden" name="_action" value="add">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Semester <span class="req">*</span></label>
        <select name="semester" class="form-control" required><option value="">— Select —</option>
        <?php foreach($semesters as $sem):?><option value="<?=$sem?>" <?=(($_POST['semester']??'')===$sem)?'selected':''?>><?=$sem?></option><?php endforeach;?></select></div>
      <div class="form-group"><label class="form-label">Academic Year</label><input type="number" name="academic_year" class="form-control" min="2000" max="2100" value="<?=htmlspecialchars($_POST['academic_year']??date('Y'),ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Course <span class="req">*</span></label>
      <select name="course_id" class="form-control" required><option value="">— Select Course —</option>
      <?php foreach($courses as $c):?><option value="<?=(int)$c['course_id']?>" <?=((int)($_POST['course_id']??0)===(int)$c['course_id'])?'selected':''?>><?=htmlspecialchars($c['course_code'].' — '.$c['course_name'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Instructor <span class="req">*</span></label>
        <select name="instructor_id" class="form-control" required><option value="">— Select —</option>
        <?php foreach($instructors as $ins):?><option value="<?=(int)$ins['instructor_id']?>" <?=((int)($_POST['instructor_id']??0)===(int)$ins['instructor_id'])?'selected':''?>><?=htmlspecialchars($ins['first_name'].' '.$ins['last_name'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
      <div class="form-group"><label class="form-label">Classroom</label>
        <select name="classroom_id" class="form-control"><option value="">— Online / TBA —</option>
        <?php foreach($rooms as $r):?><option value="<?=(int)$r['classroom_id']?>" <?=((int)($_POST['classroom_id']??0)===(int)$r['classroom_id'])?'selected':''?>><?=htmlspecialchars($r['room_number'].' — '.$r['building'].' (Cap:'.$r['capacity'].')',ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Schedule Day</label><input type="text" name="schedule_day" class="form-control" placeholder="e.g. Monday / Wednesday" value="<?=htmlspecialchars($_POST['schedule_day']??'TBA',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Max Students</label><input type="number" name="max_students" class="form-control" min="1" value="<?=htmlspecialchars($_POST['max_students']??'40',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Start Time</label><input type="time" name="start_time" class="form-control" value="<?=htmlspecialchars($_POST['start_time']??'08:00',ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control" value="<?=htmlspecialchars($_POST['end_time']??'10:00',ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Save Class</button><a href="classes.php" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php endif;?>

<div class="admin-table-card">
  <div class="admin-table-toolbar"><span class="admin-table-toolbar-title">All Classes (<?=count($classes)?>)</span>
  </div>
  <div class="admin-table-wrap">
    <?php if(empty($classes)):?><p class="admin-empty">No classes scheduled yet.</p><?php else:?>
    <table id="classes-table">
      <thead><tr><th>#</th><th>Course</th><th>Instructor</th><th>Room</th><th>Semester</th><th>Year</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($classes as $i=>$cl):?>
        <tr>
          <td style="color:var(--text-muted)"><?=$i+1?></td>
          <td><strong><?=htmlspecialchars($cl['course_code'],ENT_QUOTES,'UTF-8')?></strong> <?=htmlspecialchars($cl['course_name'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($cl['instructor'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($cl['room_number']??'Online',ENT_QUOTES,'UTF-8')?></td>
          <td><?=htmlspecialchars($cl['semester'],ENT_QUOTES,'UTF-8')?></td>
          <td><?=(int)$cl['academic_year']?></td>
          <td><div class="act-row">
            <a href="class_view.php?id=<?=(int)$cl['class_id']?>" class="btn-view">View</a>
            <a href="class_edit.php?id=<?=(int)$cl['class_id']?>" class="btn-edit">Edit</a>
            <a href="class_delete.php?id=<?=(int)$cl['class_id']?>" class="btn-delete" onclick="return confirm('Delete this class?')">Delete</a>
          </div></td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
    <?php endif;?>
  </div>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
