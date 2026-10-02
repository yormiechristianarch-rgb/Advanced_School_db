<?php
/**
 * index.php — SIMS Dashboard
 */
require_once 'config.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

try {
    $totalDepartments = (int) $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn();
    $totalCourses     = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    $totalInstructors = (int) $pdo->query('SELECT COUNT(*) FROM instructors')->fetchColumn();
    $totalClassrooms  = (int) $pdo->query('SELECT COUNT(*) FROM classrooms')->fetchColumn();
    $totalClasses     = (int) $pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn();
    $totalStudents    = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
    $totalEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:40px;color:#b91c1c;"><strong>Database error:</strong> '
        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>');
}

try {
    $recentStmt = $pdo->prepare(
        "SELECT CONCAT(s.first_name,' ',s.last_name) AS student_name, s.email, s.gender,
                co.course_code, co.course_name, cl.semester,
                CONCAT(i.first_name,' ',i.last_name) AS instructor_name,
                e.enrollment_date, e.status
         FROM   enrollments e
         JOIN   students    s  ON e.student_id     = s.student_id
         JOIN   classes     cl ON e.class_id       = cl.class_id
         JOIN   courses     co ON cl.course_id     = co.course_id
         JOIN   instructors i  ON cl.instructor_id = i.instructor_id
         ORDER  BY e.enrollment_date DESC LIMIT 8"
    );
    $recentStmt->execute();
    $recentEnrollments = $recentStmt->fetchAll();
} catch (PDOException $e) { $recentEnrollments = []; }

try {
    $recentCourses = $pdo->query(
        "SELECT c.course_code, c.course_name, c.credits, d.dept_name
         FROM courses c JOIN departments d ON c.department_id=d.department_id
         ORDER BY c.course_id DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) { $recentCourses = []; }

require_once 'includes/header.php';
?>

<section class="hero" aria-label="SIMS hero banner">
  <video class="hero-video" autoplay muted loop playsinline aria-hidden="true">
    <source src="https://res.cloudinary.com/nbrwzqb2/video/upload/v1790019278/kling_20260919_VIDEO__6100_0.mp4" type="video/mp4">
  </video>
  <div class="hero-content">
    <div class="hero-badge">Academic Management Platform</div>
    <h1 class="hero-title">Student Information<br><em>Management System</em></h1>
    <p class="hero-desc">
      Centralise your institution's academic data — manage courses,
      schedule classes, and enroll students in one secure platform.
    </p>
    <div class="hero-cta">
      <a href="enroll_student.php" class="btn btn-accent btn-lg">Enroll Student</a>
      <a href="add_course.php" class="btn btn-outline btn-lg">Add Course</a>
    </div>
    <?php if ($totalStudents > 0 || $totalCourses > 0 || $totalEnrollments > 0): ?>
    <div class="hero-stats">
      <div class="hero-stat">
        <span class="hero-stat-val"><?= $totalStudents ?></span>
        <span class="hero-stat-lbl">Students</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-val"><?= $totalCourses ?></span>
        <span class="hero-stat-lbl">Courses</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-val"><?= $totalEnrollments ?></span>
        <span class="hero-stat-lbl">Enrollments</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-val"><?= $totalInstructors ?></span>
        <span class="hero-stat-lbl">Instructors</span>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<main>

  <div class="alert alert-success fade-up" role="status">
    <div class="alert-body">
      <strong class="alert-title">Database connected</strong>
      Connected to <strong>advanced_school_db</strong> on localhost via PDO/MySQL.
    </div>
  </div>

  <p class="section-title fade-up">Database Summary</p>

  <div class="stat-grid">
    <div class="stat-card sc-blue fade-up delay-1">
      <div class="sc-val"><?= $totalStudents ?></div>
      <div class="sc-label">Students</div>
    </div>
    <div class="stat-card sc-green fade-up delay-2">
      <div class="sc-val"><?= $totalCourses ?></div>
      <div class="sc-label">Courses</div>
    </div>
    <div class="stat-card sc-amber fade-up delay-3">
      <div class="sc-val"><?= $totalInstructors ?></div>
      <div class="sc-label">Instructors</div>
    </div>
    <div class="stat-card sc-navy fade-up delay-4">
      <div class="sc-val"><?= $totalDepartments ?></div>
      <div class="sc-label">Departments</div>
    </div>
    <div class="stat-card sc-red fade-up delay-1">
      <div class="sc-val"><?= $totalClasses ?></div>
      <div class="sc-label">Classes</div>
    </div>
    <div class="stat-card sc-teal fade-up delay-2">
      <div class="sc-val"><?= $totalClassrooms ?></div>
      <div class="sc-label">Classrooms</div>
    </div>
    <div class="stat-card sc-purple fade-up delay-3">
      <div class="sc-val"><?= $totalEnrollments ?></div>
      <div class="sc-label">Enrollments</div>
    </div>
  </div>

  <!-- System Information — moved above quick actions, full width -->
  <div class="content-card fade-up" style="margin-bottom:var(--sp-8);">
    <div class="cc-header">
      <div class="cc-title">System Information</div>
    </div>
    <div class="cc-body">
      <table class="info-table">
        <tbody>
          <tr><td>Database</td><td>advanced_school_db</td></tr>
          <tr><td>Driver</td><td>PDO / MySQL (InnoDB)</td></tr>
          <tr><td>PHP Version</td><td><?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></td></tr>
          <tr><td>Server</td><td>XAMPP / Apache</td></tr>
          <tr><td>Charset</td><td>utf8mb4 / unicode_ci</td></tr>
          <tr><td>Total Courses</td><td><strong><?= $totalCourses ?></strong></td></tr>
          <tr><td>Total Students</td><td><strong><?= $totalStudents ?></strong></td></tr>
          <tr><td>Last accessed</td><td><?= date('d M Y, H:i') ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <p class="section-title fade-up">Quick Actions</p>
  <div class="quick-actions fade-up delay-1">
    <a href="add_course.php"     class="btn btn-primary">Add Course</a>
    <a href="schedule_class.php" class="btn btn-primary">Schedule Class</a>
    <a href="enroll_student.php" class="btn btn-accent">Enroll Student</a>
  </div>

  <div class="dashboard-grid">

    <!-- Recent Enrollments -->
    <div class="content-card span-full fade-up">
      <div class="cc-header">
        <div class="cc-title">Recent Enrollments</div>
        <a href="enroll_student.php" class="btn btn-sm btn-ghost">+ New Enrollment</a>
      </div>
      <div class="cc-body-flush">
        <?php if (empty($recentEnrollments)): ?>
          <div class="empty-state">
            <p>No enrollments yet. <a href="enroll_student.php">Enroll the first student →</a></p>
          </div>
        <?php else: ?>
          <div class="table-wrap" style="border:none;border-radius:0;box-shadow:none;">
            <table id="enroll-table">
              <thead>
                <tr>
                  <th>#</th><th>Student</th><th>Gender</th><th>Course</th>
                  <th>Semester</th><th>Instructor</th><th>Date</th><th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentEnrollments as $i => $row):
                  $status = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
                  $badgeClass = match($status) {
                    'Active' => 'badge-green', 'Completed' => 'badge-blue',
                    'Withdrawn' => 'badge-red', default => 'badge-gray'
                  };
                  $gender = htmlspecialchars($row['gender'] ?? '', ENT_QUOTES, 'UTF-8');
                  $genderBadge = match($gender) {
                    'Male' => 'badge-navy', 'Female' => 'badge-purple', default => 'badge-gray'
                  };
                ?>
                <tr>
                  <td style="color:var(--text-muted);font-size:0.78rem;"><?= $i+1 ?></td>
                  <td>
                    <strong><?= htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></div>
                  </td>
                  <td>
                    <?php if ($gender): ?>
                      <span class="badge <?= $genderBadge ?>"><?= $gender ?></span>
                    <?php else: ?>
                      <span class="badge badge-gray">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($row['course_code'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <div style="font-size:0.75rem;color:var(--text-muted);"><?= htmlspecialchars($row['course_name'], ENT_QUOTES, 'UTF-8') ?></div>
                  </td>
                  <td><?= htmlspecialchars($row['semester'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['instructor_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td style="white-space:nowrap;"><?= htmlspecialchars($row['enrollment_date'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge <?= $badgeClass ?>"><?= $status ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <p class="tbl-no-match" id="enroll-no-match"></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Courses -->
    <div class="content-card fade-up delay-1">
      <div class="cc-header">
        <div class="cc-title">Recent Courses</div>
        <a href="add_course.php" class="btn btn-sm btn-ghost">+ Add Course</a>
      </div>
      <div class="cc-body-flush" id="courses-list">
        <?php if (empty($recentCourses)): ?>
          <div class="empty-state"><p>No courses yet. <a href="add_course.php">Add the first →</a></p></div>
        <?php else: ?>
          <?php foreach ($recentCourses as $c): ?>
            <div class="course-item">
              <div class="course-name">
                <strong><?= htmlspecialchars($c['course_code'], ENT_QUOTES, 'UTF-8') ?> &mdash; <?= htmlspecialchars($c['course_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <span><?= htmlspecialchars($c['dept_name'], ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <span class="badge badge-navy"><?= (int)$c['credits'] ?> cr</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /.dashboard-grid -->

</main>

<?php require_once 'includes/footer.php'; ?>
