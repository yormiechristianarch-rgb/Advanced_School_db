<?php
/**
 * admin/search.php — SIMS Global Search Page
 *
 * Performs server-side search across all main entities using the
 * same logic as search_api.php, but renders an HTML results page.
 * Protected by admin_auth.php.
 */
require_once '../config.php';
require_once 'admin_auth.php';

$pageTitle = 'Search';
$adminPage = 'search';

// ── Input ─────────────────────────────────────────────────────
$q      = trim($_GET['q']    ?? '');
$type   = trim($_GET['type'] ?? 'all');
$limit  = 20;

$validTypes = ['all','students','courses','instructors',
               'departments','classrooms','classes','enrollments'];
if (!in_array($type, $validTypes, true)) { $type = 'all'; }

$like    = '%' . $q . '%';
$results = [];
$total   = 0;
$error   = '';

// ── Run search only if query is present ───────────────────────
if ($q !== '' && mb_strlen($q) >= 2) {

    try {

        // STUDENTS
        if ($type === 'all' || $type === 'students') {
            $s = $pdo->prepare(
                "SELECT student_id, reg_number, first_name, last_name, email, gender, admission_date
                 FROM students
                 WHERE student_id LIKE :q1 OR reg_number LIKE :q2
                    OR first_name LIKE :q3 OR last_name  LIKE :q4
                    OR email      LIKE :q5
                    OR CONCAT(first_name,' ',last_name) LIKE :q6
                 ORDER BY last_name, first_name LIMIT :lim"
            );
            $s->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,
                         ':q4'=>$like,':q5'=>$like,':q6'=>$like,':lim'=>$limit]);
            $results['students'] = $s->fetchAll();
            $total += count($results['students']);
        }

        // COURSES
        if ($type === 'all' || $type === 'courses') {
            $c = $pdo->prepare(
                "SELECT c.course_id, c.course_code, c.course_name, c.credits, c.level, d.dept_name
                 FROM courses c JOIN departments d ON c.department_id=d.department_id
                 WHERE c.course_id LIKE :q1 OR c.course_code LIKE :q2
                    OR c.course_name LIKE :q3 OR d.dept_name LIKE :q4
                 ORDER BY c.course_code LIMIT :lim"
            );
            $c->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,':lim'=>$limit]);
            $results['courses'] = $c->fetchAll();
            $total += count($results['courses']);
        }

        // INSTRUCTORS
        if ($type === 'all' || $type === 'instructors') {
            $i = $pdo->prepare(
                "SELECT i.instructor_id, i.first_name, i.last_name, i.email,
                        i.employee_number, d.dept_name, i.hire_date
                 FROM instructors i JOIN departments d ON i.department_id=d.department_id
                 WHERE i.instructor_id LIKE :q1 OR i.first_name LIKE :q2
                    OR i.last_name LIKE :q3 OR i.email LIKE :q4
                    OR i.employee_number LIKE :q5
                    OR CONCAT(i.first_name,' ',i.last_name) LIKE :q6
                 ORDER BY i.last_name, i.first_name LIMIT :lim"
            );
            $i->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,
                         ':q4'=>$like,':q5'=>$like,':q6'=>$like,':lim'=>$limit]);
            $results['instructors'] = $i->fetchAll();
            $total += count($results['instructors']);
        }

        // DEPARTMENTS
        if ($type === 'all' || $type === 'departments') {
            $d = $pdo->prepare(
                "SELECT department_id, dept_code, dept_name, hod_name, established_year
                 FROM departments
                 WHERE department_id LIKE :q1 OR dept_code LIKE :q2
                    OR dept_name LIKE :q3 OR hod_name LIKE :q4
                 ORDER BY dept_name LIMIT :lim"
            );
            $d->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,':lim'=>$limit]);
            $results['departments'] = $d->fetchAll();
            $total += count($results['departments']);
        }

        // CLASSROOMS
        if ($type === 'all' || $type === 'classrooms') {
            $cr = $pdo->prepare(
                "SELECT classroom_id, room_number, building, capacity, has_projector, has_lab
                 FROM classrooms
                 WHERE classroom_id LIKE :q1 OR room_number LIKE :q2 OR building LIKE :q3
                 ORDER BY room_number LIMIT :lim"
            );
            $cr->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,':lim'=>$limit]);
            $results['classrooms'] = $cr->fetchAll();
            $total += count($results['classrooms']);
        }

        // CLASSES
        if ($type === 'all' || $type === 'classes') {
            $cl = $pdo->prepare(
                "SELECT cl.class_id, co.course_code, co.course_name,
                        cl.semester, cl.academic_year, cl.schedule_day,
                        CONCAT(i.first_name,' ',i.last_name) AS instructor_name,
                        cr.room_number
                 FROM classes cl
                 JOIN courses     co ON cl.course_id     = co.course_id
                 JOIN instructors i  ON cl.instructor_id = i.instructor_id
                 LEFT JOIN classrooms cr ON cl.classroom_id = cr.classroom_id
                 WHERE cl.class_id LIKE :q1 OR co.course_code LIKE :q2
                    OR co.course_name LIKE :q3 OR cl.semester LIKE :q4
                    OR CONCAT(i.first_name,' ',i.last_name) LIKE :q5
                    OR cr.room_number LIKE :q6
                 ORDER BY co.course_code, cl.semester LIMIT :lim"
            );
            $cl->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,
                          ':q4'=>$like,':q5'=>$like,':q6'=>$like,':lim'=>$limit]);
            $results['classes'] = $cl->fetchAll();
            $total += count($results['classes']);
        }

        // ENROLLMENTS
        if ($type === 'all' || $type === 'enrollments') {
            $en = $pdo->prepare(
                "SELECT e.enrollment_id, e.enrollment_date, e.status,
                        CONCAT(s.first_name,' ',s.last_name) AS student_name,
                        s.email AS student_email, co.course_code, co.course_name, cl.semester
                 FROM enrollments e
                 JOIN students    s  ON e.student_id     = s.student_id
                 JOIN classes     cl ON e.class_id       = cl.class_id
                 JOIN courses     co ON cl.course_id     = co.course_id
                 WHERE e.enrollment_id LIKE :q1
                    OR CONCAT(s.first_name,' ',s.last_name) LIKE :q2
                    OR s.email LIKE :q3 OR co.course_code LIKE :q4
                    OR co.course_name LIKE :q5 OR cl.semester LIKE :q6
                 ORDER BY e.enrollment_date DESC LIMIT :lim"
            );
            $en->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like,
                          ':q4'=>$like,':q5'=>$like,':q6'=>$like,':lim'=>$limit]);
            $results['enrollments'] = $en->fetchAll();
            $total += count($results['enrollments']);
        }

    } catch (PDOException $e) {
        $error = 'A database error occurred. Please try again.';
        // Log error internally (not exposed to user)
        error_log('SIMS search error: ' . $e->getMessage());
    }

} elseif ($q !== '' && mb_strlen($q) < 2) {
    $error = 'Please enter at least 2 characters to search.';
}

// Helper: safe HTML output
function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

require_once '../includes/admin_sidebar.php';
?>

<!-- search.css loaded globally via admin_sidebar.php -->

<div class="admin-ph">
  <div class="admin-ph-row">
    <div>
      <h2 class="admin-ph-title">Search</h2>
      <p style="font-size:0.855rem;color:var(--text-muted);margin-top:2px;">
        Search across students, courses, instructors, departments, classrooms, classes and enrollments.
      </p>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     SEARCH FORM
═══════════════════════════════════════════════════════════ -->
<div class="search-form-card">
  <form id="search-form" method="GET" action="search.php" novalidate role="search">

    <!-- Main input + submit -->
    <div class="search-input-row">
      <div class="search-input-wrap" style="position:relative;">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input
          type="text"
          id="search-main-input"
          name="q"
          class="search-main-input"
          placeholder="Search students, courses, instructors…"
          value="<?= h($q) ?>"
          autocomplete="off"
          aria-label="Search query"
          data-api="search_api.php"
        >
        <!-- Live suggestions dropdown -->
        <div id="search-suggestions" class="search-suggestions" role="listbox"
             aria-label="Search suggestions"></div>
      </div>

      <button type="submit" class="search-submit-btn" aria-label="Run search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        Search
      </button>
    </div>

    <!-- Type filter -->
    <div class="search-filter-row">
      <span class="search-filter-label">Filter by:</span>

      <select id="search-type" name="type" class="search-type-select"
              aria-label="Search type">
        <option value="all"         <?= $type==='all'         ? 'selected' : '' ?>>All records</option>
        <option value="students"    <?= $type==='students'    ? 'selected' : '' ?>>Students</option>
        <option value="courses"     <?= $type==='courses'     ? 'selected' : '' ?>>Courses</option>
        <option value="instructors" <?= $type==='instructors' ? 'selected' : '' ?>>Instructors</option>
        <option value="departments" <?= $type==='departments' ? 'selected' : '' ?>>Departments</option>
        <option value="classrooms"  <?= $type==='classrooms'  ? 'selected' : '' ?>>Classrooms</option>
        <option value="classes"     <?= $type==='classes'     ? 'selected' : '' ?>>Classes</option>
        <option value="enrollments" <?= $type==='enrollments' ? 'selected' : '' ?>>Enrollments</option>
      </select>

      <!-- Quick-type chips -->
      <div class="search-chips" role="group" aria-label="Quick type filter">
        <?php
        $chips = ['all'=>'All','students'=>'Students','courses'=>'Courses',
                  'instructors'=>'Instructors','departments'=>'Departments',
                  'classrooms'=>'Classrooms','classes'=>'Classes','enrollments'=>'Enrollments'];
        foreach ($chips as $val => $lbl):
        ?>
          <span class="search-chip<?= ($type===$val) ? ' search-chip--active' : '' ?>"
                data-type="<?= h($val) ?>" role="button" tabindex="0"
                aria-pressed="<?= ($type===$val) ? 'true' : 'false' ?>">
            <?= h($lbl) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>

  </form>
</div>

<!-- ══════════════════════════════════════════════════════════
     RESULTS AREA
═══════════════════════════════════════════════════════════ -->

<?php if ($error): ?>
  <div class="admin-alert error" role="alert">
    <strong>Notice</strong> <?= h($error) ?>
  </div>

<?php elseif ($q === ''): ?>
  <!-- Initial state: no search submitted -->
  <div class="search-start-hint">
    <p>Enter a keyword above and press <strong>Search</strong> to find records.</p>
    <p style="font-size:0.80rem;color:var(--text-muted);">
      Searches are case-insensitive and support partial matches.
    </p>
    <div class="search-start-tips">
      <span class="search-start-tip">Try: <strong>john</strong></span>
      <span class="search-start-tip">Try: <strong>CS101</strong></span>
      <span class="search-start-tip">Try: <strong>Semester 1</strong></span>
      <span class="search-start-tip">Try: <strong>Computer</strong></span>
    </div>
  </div>

<?php elseif ($total === 0): ?>
  <!-- No results -->
  <div class="search-empty-state">
    <p>No results found for <strong>&ldquo;<?= h($q) ?>&rdquo;</strong>.</p>
    <p>Try a different keyword, check your spelling, or broaden the filter to <strong>All</strong>.</p>
    <a href="search.php" class="btn btn-ghost btn-sm" style="margin-top:12px;">Clear search</a>
  </div>

<?php else: ?>
  <!-- Summary bar -->
  <div class="search-summary" role="status">
    <span>
      <span class="search-summary-count"><?= $total ?></span>
      result<?= $total !== 1 ? 's' : '' ?> found for
      <strong>&ldquo;<?= h($q) ?>&rdquo;</strong>
      <?php if ($type !== 'all'): ?>
        in <strong><?= h(ucfirst($type)) ?></strong>
      <?php endif; ?>
    </span>
    <a href="search.php" class="search-clear-link">&#x2715; Clear</a>
  </div>

  <!-- ── STUDENTS ─────────────────────────────────────────── -->
  <?php if (!empty($results['students'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Students</span>
      <span class="search-group-count"><?= count($results['students']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr>
            <th>ID</th><th>Reg No.</th><th>Name</th>
            <th>Email</th><th>Gender</th><th>Admitted</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results['students'] as $r): ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['student_id'] ?></td>
            <td><strong><?= h($r['reg_number']) ?></strong></td>
            <td><?= h($r['first_name'] . ' ' . $r['last_name']) ?></td>
            <td><?= h($r['email']) ?></td>
            <td><?= h($r['gender'] ?? '—') ?></td>
            <td style="white-space:nowrap"><?= h($r['admission_date']) ?></td>
            <td>
              <div class="act-row">
                <a href="student_view.php?id=<?= (int)$r['student_id'] ?>" class="btn-view">View</a>
                <a href="student_edit.php?id=<?= (int)$r['student_id'] ?>" class="btn-edit">Edit</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── COURSES ──────────────────────────────────────────── -->
  <?php if (!empty($results['courses'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Courses</span>
      <span class="search-group-count"><?= count($results['courses']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr><th>ID</th><th>Code</th><th>Title</th><th>Credits</th><th>Level</th><th>Department</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($results['courses'] as $r): ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['course_id'] ?></td>
            <td><strong><?= h($r['course_code']) ?></strong></td>
            <td><?= h($r['course_name']) ?></td>
            <td><?= (int)$r['credits'] ?></td>
            <td><?= h($r['level']) ?></td>
            <td><?= h($r['dept_name']) ?></td>
            <td>
              <div class="act-row">
                <a href="course_view.php?id=<?= (int)$r['course_id'] ?>" class="btn-view">View</a>
                <a href="course_edit.php?id=<?= (int)$r['course_id'] ?>" class="btn-edit">Edit</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── INSTRUCTORS ──────────────────────────────────────── -->
  <?php if (!empty($results['instructors'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Instructors</span>
      <span class="search-group-count"><?= count($results['instructors']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Emp. No.</th><th>Department</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($results['instructors'] as $r): ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['instructor_id'] ?></td>
            <td><strong><?= h($r['first_name'] . ' ' . $r['last_name']) ?></strong></td>
            <td><?= h($r['email']) ?></td>
            <td><?= h($r['employee_number']) ?></td>
            <td><?= h($r['dept_name']) ?></td>
            <td>
              <div class="act-row">
                <a href="instructor_view.php?id=<?= (int)$r['instructor_id'] ?>" class="btn-view">View</a>
                <a href="instructor_edit.php?id=<?= (int)$r['instructor_id'] ?>" class="btn-edit">Edit</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── DEPARTMENTS ──────────────────────────────────────── -->
  <?php if (!empty($results['departments'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Departments</span>
      <span class="search-group-count"><?= count($results['departments']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr><th>ID</th><th>Code</th><th>Name</th><th>HOD</th><th>Est. Year</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($results['departments'] as $r): ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['department_id'] ?></td>
            <td><strong><?= h($r['dept_code']) ?></strong></td>
            <td><?= h($r['dept_name']) ?></td>
            <td><?= h($r['hod_name'] ?? '—') ?></td>
            <td><?= h($r['established_year'] ?? '—') ?></td>
            <td>
              <div class="act-row">
                <a href="department_view.php?id=<?= (int)$r['department_id'] ?>" class="btn-view">View</a>
                <a href="department_edit.php?id=<?= (int)$r['department_id'] ?>" class="btn-edit">Edit</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── CLASSROOMS ───────────────────────────────────────── -->
  <?php if (!empty($results['classrooms'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Classrooms</span>
      <span class="search-group-count"><?= count($results['classrooms']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr><th>ID</th><th>Room No.</th><th>Building</th><th>Capacity</th><th>Projector</th><th>Lab</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($results['classrooms'] as $r): ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['classroom_id'] ?></td>
            <td><strong><?= h($r['room_number']) ?></strong></td>
            <td><?= h($r['building']) ?></td>
            <td><?= (int)$r['capacity'] ?></td>
            <td><?= $r['has_projector'] ? 'Yes' : 'No' ?></td>
            <td><?= $r['has_lab'] ? 'Yes' : 'No' ?></td>
            <td>
              <div class="act-row">
                <a href="classroom_view.php?id=<?= (int)$r['classroom_id'] ?>" class="btn-view">View</a>
                <a href="classroom_edit.php?id=<?= (int)$r['classroom_id'] ?>" class="btn-edit">Edit</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── CLASSES ──────────────────────────────────────────── -->
  <?php if (!empty($results['classes'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Classes</span>
      <span class="search-group-count"><?= count($results['classes']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr><th>ID</th><th>Course</th><th>Instructor</th><th>Room</th><th>Semester</th><th>Year</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($results['classes'] as $r): ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['class_id'] ?></td>
            <td><strong><?= h($r['course_code']) ?></strong> <?= h($r['course_name']) ?></td>
            <td><?= h($r['instructor_name']) ?></td>
            <td><?= h($r['room_number'] ?? 'Online') ?></td>
            <td><?= h($r['semester']) ?></td>
            <td><?= (int)$r['academic_year'] ?></td>
            <td>
              <div class="act-row">
                <a href="class_view.php?id=<?= (int)$r['class_id'] ?>" class="btn-view">View</a>
                <a href="class_edit.php?id=<?= (int)$r['class_id'] ?>" class="btn-edit">Edit</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── ENROLLMENTS ──────────────────────────────────────── -->
  <?php if (!empty($results['enrollments'])): ?>
  <div class="search-group">
    <div class="search-group-header">
      <span class="search-group-title">Enrollments</span>
      <span class="search-group-count"><?= count($results['enrollments']) ?></span>
    </div>
    <div class="search-result-table-wrap">
      <table class="search-result-table">
        <thead>
          <tr><th>ID</th><th>Student</th><th>Course</th><th>Semester</th><th>Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($results['enrollments'] as $r): ?>
          <?php
            $sc = match($r['status']) {
              'Active'    => 'badge-green',
              'Completed' => 'badge-blue',
              'Withdrawn' => 'badge-red',
              default     => 'badge-gray',
            };
          ?>
          <tr>
            <td style="color:var(--text-muted)"><?= (int)$r['enrollment_id'] ?></td>
            <td>
              <strong><?= h($r['student_name']) ?></strong>
              <div style="font-size:0.75rem;color:var(--text-muted)"><?= h($r['student_email']) ?></div>
            </td>
            <td><strong><?= h($r['course_code']) ?></strong> <?= h($r['course_name']) ?></td>
            <td><?= h($r['semester']) ?></td>
            <td style="white-space:nowrap"><?= h($r['enrollment_date']) ?></td>
            <td><span class="badge <?= $sc ?>"><?= h($r['status']) ?></span></td>
            <td>
              <div class="act-row">
                <a href="enrollment_view.php?id=<?= (int)$r['enrollment_id'] ?>" class="btn-view">View</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

<?php endif; ?>

<?php require_once '../includes/admin_footer.php'; ?>
