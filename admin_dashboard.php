<?php
require_once '../config.php';
require_once 'admin_auth.php';

$pageTitle = 'Dashboard';
$adminPage = 'dashboard';

// ── Stats ─────────────────────────────────────────────────────
$counts = [
    'students'    => 0, 'courses'    => 0, 'instructors' => 0,
    'departments' => 0, 'classrooms' => 0, 'classes'     => 0,
    'enrollments' => 0,
];
$dbError = '';

try {
    foreach (array_keys($counts) as $tbl) {
        $counts[$tbl] = (int) $pdo->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
    }
} catch (PDOException $e) {
    $dbError = 'Could not load dashboard statistics.';
    error_log('SIMS dashboard stats error: ' . $e->getMessage());
}

// ── Recent students (last 5) ──────────────────────────────────
$recentStudents = [];
try {
    $recentStudents = $pdo->query(
        "SELECT student_id, first_name, last_name, email, admission_date
         FROM students ORDER BY student_id DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) { error_log('SIMS dash students: ' . $e->getMessage()); }

// ── Recent courses (last 5) ───────────────────────────────────
$recentCourses = [];
try {
    $recentCourses = $pdo->query(
        "SELECT c.course_id, c.course_code, c.course_name, c.credits, d.dept_name
         FROM courses c JOIN departments d ON c.department_id=d.department_id
         ORDER BY c.course_id DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) { error_log('SIMS dash courses: ' . $e->getMessage()); }

// ── Recent classes (last 5) ───────────────────────────────────
$recentClasses = [];
try {
    $recentClasses = $pdo->query(
        "SELECT cl.class_id, co.course_code, co.course_name,
                CONCAT(i.first_name,' ',i.last_name) AS instructor,
                cl.semester, cl.academic_year
         FROM classes cl
         JOIN courses     co ON cl.course_id     = co.course_id
         JOIN instructors i  ON cl.instructor_id = i.instructor_id
         ORDER BY cl.class_id DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) { error_log('SIMS dash classes: ' . $e->getMessage()); }

require_once '../includes/admin_sidebar.php';
?>

<!-- Stats -->
<div class="admin-ph">
  <div class="admin-ph-row">
    <h2 class="admin-ph-title">Welcome, <?= htmlspecialchars($_SESSION['admin_full_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></h2>
  </div>
</div>

<?php if ($dbError): ?>
<div class="admin-alert warning"><strong>Notice</strong> <?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="admin-stat-grid">
  <?php
  $statConfig = [
    'students'    => ['Students',    'var(--primary-400)',  'var(--primary-50)'],
    'courses'     => ['Courses',     '#16a34a',             '#dcfce7'],
    'instructors' => ['Instructors', 'var(--accent-600)',   'var(--accent-tint)'],
    'departments' => ['Departments', 'var(--primary-700)',  'var(--primary-100)'],
    'classrooms'  => ['Classrooms',  '#0d9488',             '#ccfbf1'],
    'classes'     => ['Classes',     'var(--error-mid)',    'var(--error-light)'],
    'enrollments' => ['Enrollments', '#7c3aed',             '#ede9fe'],
  ];
  foreach ($statConfig as $key => [$label, $color, $tint]):
  ?>
  <div class="admin-stat-card" style="--sc-color:<?= $color ?>;--sc-tint:<?= $tint ?>;">
    <div class="admin-stat-val"><?= $counts[$key] ?></div>
    <div class="admin-stat-label"><?= $label ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<p class="section-title" style="margin-bottom:16px;">Quick Actions</p>
<div class="admin-quick-grid" style="margin-bottom:32px;">
  <a href="courses.php?action=add" class="admin-quick-btn">Add Course<span class="aqb-label">Create new course</span></a>
  <a href="courses.php"            class="admin-quick-btn">Manage Courses<span class="aqb-label">View &amp; edit</span></a>
  <a href="students.php"           class="admin-quick-btn">Manage Students<span class="aqb-label">View &amp; edit</span></a>
  <a href="instructors.php"        class="admin-quick-btn">Manage Instructors<span class="aqb-label">View &amp; edit</span></a>
  <a href="departments.php"        class="admin-quick-btn">Manage Departments<span class="aqb-label">View &amp; edit</span></a>
  <a href="classrooms.php"         class="admin-quick-btn">Manage Classrooms<span class="aqb-label">View &amp; edit</span></a>
  <a href="classes.php"            class="admin-quick-btn">Manage Classes<span class="aqb-label">View &amp; edit</span></a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:28px;" class="admin-dash-3col">

  <!-- Recent Students -->
  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <span class="admin-table-toolbar-title">Recent Students</span>
      <a href="students.php" class="btn btn-sm btn-ghost">View all</a>
    </div>
    <div class="admin-table-wrap">
      <?php if (empty($recentStudents)): ?>
        <p class="admin-empty">No students yet.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Name</th><th>Admitted</th></tr></thead>
        <tbody>
        <?php foreach ($recentStudents as $s): ?>
          <tr>
            <td><strong><?= htmlspecialchars($s['first_name'].' '.$s['last_name'], ENT_QUOTES,'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars($s['admission_date'], ENT_QUOTES,'UTF-8') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Courses -->
  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <span class="admin-table-toolbar-title">Recent Courses</span>
      <a href="courses.php" class="btn btn-sm btn-ghost">View all</a>
    </div>
    <div class="admin-table-wrap">
      <?php if (empty($recentCourses)): ?>
        <p class="admin-empty">No courses yet.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Code</th><th>Title</th></tr></thead>
        <tbody>
        <?php foreach ($recentCourses as $c): ?>
          <tr>
            <td><strong><?= htmlspecialchars($c['course_code'], ENT_QUOTES,'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars($c['course_name'], ENT_QUOTES,'UTF-8') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Classes -->
  <div class="admin-table-card">
    <div class="admin-table-toolbar">
      <span class="admin-table-toolbar-title">Recent Classes</span>
      <a href="classes.php" class="btn btn-sm btn-ghost">View all</a>
    </div>
    <div class="admin-table-wrap">
      <?php if (empty($recentClasses)): ?>
        <p class="admin-empty">No classes yet.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Course</th><th>Semester</th></tr></thead>
        <tbody>
        <?php foreach ($recentClasses as $cl): ?>
          <tr>
            <td><strong><?= htmlspecialchars($cl['course_code'], ENT_QUOTES,'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars($cl['semester'], ENT_QUOTES,'UTF-8') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once '../includes/admin_footer.php'; ?>
