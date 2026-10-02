<?php
require_once '../config.php'; require_once 'admin_auth.php';
$id=(int)($_GET['id']??$_POST['class_id']??0);
if($id<=0){header('Location: classes.php');exit;}
$s=$pdo->prepare("SELECT * FROM classes WHERE class_id=:id");
$s->execute([':id'=>$id]); $cl=$s->fetch(); if(!$cl){header('Location: classes.php');exit;}

$courses=$pdo->query("SELECT course_id,course_code,course_name FROM courses ORDER BY course_code")->fetchAll();
$instructors=$pdo->query("SELECT instructor_id,first_name,last_name FROM instructors ORDER BY last_name,first_name")->fetchAll();
$rooms=$pdo->query("SELECT classroom_id,room_number,building,capacity FROM classrooms ORDER BY room_number")->fetchAll();
$semesters=['Semester 1','Semester 2','Semester 3'];
$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $cid=(int)($_POST['course_id']??0); $iid=(int)($_POST['instructor_id']??0);
    $rid=(int)($_POST['classroom_id']??0); $sem=trim($_POST['semester']??'');
    $yr=(int)($_POST['academic_year']??date('Y'));
    $sd=trim($_POST['schedule_day']??'TBA'); $st=trim($_POST['start_time']??'08:00');
    $et=trim($_POST['end_time']??'10:00'); $max=(int)($_POST['max_students']??40);
    if($cid<=0||$iid<=0||$sem===''||!in_array($sem,$semesters,true)){
        $error='Course, instructor, and a valid semester are required.';
    } else {
        try{
            $pdo->prepare("UPDATE classes SET course_id=:ci,instructor_id=:ii,classroom_id=:ri,semester=:sem,academic_year=:yr,schedule_day=:sd,start_time=:st,end_time=:et,max_students=:max WHERE class_id=:id")
                ->execute([':ci'=>$cid,':ii'=>$iid,':ri'=>$rid?:null,':sem'=>$sem,':yr'=>$yr,':sd'=>$sd,':st'=>$st,':et'=>$et,':max'=>$max,':id'=>$id]);
            $_SESSION['flash']=['type'=>'success','msg'=>'Class updated.']; header('Location: classes.php');exit;
        }catch(PDOException $e){$error=$e->getCode()==='23000'?'This course/instructor/semester combination already exists.':htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
    }
    $cl=array_merge($cl,['course_id'=>$cid,'instructor_id'=>$iid,'classroom_id'=>$rid,'semester'=>$sem,'academic_year'=>$yr,'schedule_day'=>$sd,'start_time'=>$st,'end_time'=>$et,'max_students'=>$max]);
}

$pageTitle='Edit Class'; $adminPage='classes';
require_once '../includes/admin_sidebar.php';
?>
<div class="admin-ph"><div class="admin-ph-breadcrumb"><a href="classes.php">Classes</a> &rsaquo; Edit</div></div>
<?php if($error):?><div class="admin-alert error"><strong>Error</strong><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="admin-form-card">
  <form method="POST" novalidate>
    <input type="hidden" name="class_id" value="<?=(int)$id?>">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Semester <span class="req">*</span></label>
        <select name="semester" class="form-control" required><option value="">— Select —</option>
        <?php foreach($semesters as $sem):?><option value="<?=$sem?>" <?=($cl['semester']===$sem)?'selected':''?>><?=$sem?></option><?php endforeach;?></select></div>
      <div class="form-group"><label class="form-label">Academic Year</label>
        <input type="number" name="academic_year" class="form-control" min="2000" max="2100" value="<?=(int)$cl['academic_year']?>"></div>
    </div>
    <div class="form-group"><label class="form-label">Course <span class="req">*</span></label>
      <select name="course_id" class="form-control" required><option value="">— Select Course —</option>
      <?php foreach($courses as $c):?><option value="<?=(int)$c['course_id']?>" <?=((int)$cl['course_id']===(int)$c['course_id'])?'selected':''?>><?=htmlspecialchars($c['course_code'].' — '.$c['course_name'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Instructor <span class="req">*</span></label>
        <select name="instructor_id" class="form-control" required><option value="">— Select —</option>
        <?php foreach($instructors as $ins):?><option value="<?=(int)$ins['instructor_id']?>" <?=((int)$cl['instructor_id']===(int)$ins['instructor_id'])?'selected':''?>><?=htmlspecialchars($ins['first_name'].' '.$ins['last_name'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
      <div class="form-group"><label class="form-label">Classroom</label>
        <select name="classroom_id" class="form-control"><option value="">— Online / TBA —</option>
        <?php foreach($rooms as $r):?><option value="<?=(int)$r['classroom_id']?>" <?=((int)($cl['classroom_id']??0)===(int)$r['classroom_id'])?'selected':''?>><?=htmlspecialchars($r['room_number'].' — '.$r['building'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Schedule Day</label>
        <input type="text" name="schedule_day" class="form-control" value="<?=htmlspecialchars($cl['schedule_day'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">Max Students</label>
        <input type="number" name="max_students" class="form-control" min="1" value="<?=(int)$cl['max_students']?>"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Start Time</label>
        <input type="time" name="start_time" class="form-control" value="<?=htmlspecialchars($cl['start_time'],ENT_QUOTES,'UTF-8')?>"></div>
      <div class="form-group"><label class="form-label">End Time</label>
        <input type="time" name="end_time" class="form-control" value="<?=htmlspecialchars($cl['end_time'],ENT_QUOTES,'UTF-8')?>"></div>
    </div>
    <div class="btn-row"><button type="submit" class="btn btn-primary">Update Class</button><a href="class_view.php?id=<?=(int)$id?>" class="btn btn-ghost">Cancel</a></div>
  </form>
</div>
<?php require_once '../includes/admin_footer.php'; ?>
