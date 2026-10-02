<?php
/**
 * search_api.php — SIMS Search AJAX Endpoint
 *
 * Returns JSON.  Called by search.js for live suggestions.
 * Also used by search.php for full server-side results.
 *
 * GET parameters:
 *   q      — search query string (required, min 2 chars)
 *   type   — 'all' | 'students' | 'courses' | 'instructors' |
 *            'departments' | 'classrooms' | 'classes' | 'enrollments'
 *   limit  — max results per group (default 10, max 50)
 *   format — 'suggestions' (compact) | 'full' (default)
 *
 * Security:
 *   - Admin session required.
 *   - All DB values use PDO prepared statements.
 *   - Input is trimmed and validated; no SQL concatenation.
 */

require_once '../config.php';
require_once 'admin_auth.php';   // redirects to login if no valid session

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// ── Input sanitisation ────────────────────────────────────────
$q      = trim($_GET['q']     ?? '');
$type   = trim($_GET['type']  ?? 'all');
$limit  = min((int)($_GET['limit'] ?? 10), 50);
$format = trim($_GET['format'] ?? 'full');

$validTypes = ['all','students','courses','instructors',
               'departments','classrooms','classes','enrollments'];

if ($q === '' || mb_strlen($q) < 2) {
    echo json_encode(['total' => 0, 'results' => [], 'query' => $q,
                      'error' => 'Query must be at least 2 characters.']);
    exit;
}

if (!in_array($type, $validTypes, true)) {
    $type = 'all';
}

$like   = '%' . $q . '%';
$results = [];
$total   = 0;

// ─────────────────────────────────────────────────────────────
// Helper: run a prepared query and return rows
// ─────────────────────────────────────────────────────────────
function runQuery(PDO $pdo, string $sql, array $params): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─────────────────────────────────────────────────────────────
// STUDENTS
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'students') {
    $rows = runQuery($pdo,
        "SELECT student_id, reg_number, first_name, last_name, email, gender, admission_date
         FROM students
         WHERE student_id   LIKE :q1
            OR reg_number   LIKE :q2
            OR first_name   LIKE :q3
            OR last_name    LIKE :q4
            OR email        LIKE :q5
            OR CONCAT(first_name,' ',last_name) LIKE :q6
         ORDER BY last_name, first_name
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,
         ':q5'=>$like,':q6'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['students'] = array_map(function($r) {
            return [
                'name' => $r['first_name'] . ' ' . $r['last_name'],
                'meta' => $r['email'],
            ];
        }, $rows);
    } else {
        $results['students'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// COURSES
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'courses') {
    $rows = runQuery($pdo,
        "SELECT c.course_id, c.course_code, c.course_name, c.credits, c.level,
                d.dept_name
         FROM courses c
         JOIN departments d ON c.department_id = d.department_id
         WHERE c.course_id   LIKE :q1
            OR c.course_code LIKE :q2
            OR c.course_name LIKE :q3
            OR d.dept_name   LIKE :q4
         ORDER BY c.course_code
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['courses'] = array_map(function($r) {
            return [
                'name' => $r['course_code'] . ' — ' . $r['course_name'],
                'meta' => $r['dept_name'],
            ];
        }, $rows);
    } else {
        $results['courses'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// INSTRUCTORS
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'instructors') {
    $rows = runQuery($pdo,
        "SELECT i.instructor_id, i.first_name, i.last_name, i.email,
                i.employee_number, d.dept_name
         FROM instructors i
         JOIN departments d ON i.department_id = d.department_id
         WHERE i.instructor_id   LIKE :q1
            OR i.first_name      LIKE :q2
            OR i.last_name       LIKE :q3
            OR i.email           LIKE :q4
            OR i.employee_number LIKE :q5
            OR CONCAT(i.first_name,' ',i.last_name) LIKE :q6
         ORDER BY i.last_name, i.first_name
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,
         ':q5'=>$like,':q6'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['instructors'] = array_map(function($r) {
            return [
                'name' => $r['first_name'] . ' ' . $r['last_name'],
                'meta' => $r['dept_name'],
            ];
        }, $rows);
    } else {
        $results['instructors'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// DEPARTMENTS
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'departments') {
    $rows = runQuery($pdo,
        "SELECT department_id, dept_code, dept_name, hod_name, established_year
         FROM departments
         WHERE department_id LIKE :q1
            OR dept_code     LIKE :q2
            OR dept_name     LIKE :q3
            OR hod_name      LIKE :q4
         ORDER BY dept_name
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['departments'] = array_map(function($r) {
            return [
                'name' => $r['dept_code'] . ' — ' . $r['dept_name'],
                'meta' => $r['hod_name'] ?? '',
            ];
        }, $rows);
    } else {
        $results['departments'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// CLASSROOMS
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'classrooms') {
    $rows = runQuery($pdo,
        "SELECT classroom_id, room_number, building, capacity, has_projector, has_lab
         FROM classrooms
         WHERE classroom_id LIKE :q1
            OR room_number  LIKE :q2
            OR building     LIKE :q3
         ORDER BY room_number
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['classrooms'] = array_map(function($r) {
            return [
                'name' => $r['room_number'] . ' — ' . $r['building'],
                'meta' => 'Cap: ' . $r['capacity'],
            ];
        }, $rows);
    } else {
        $results['classrooms'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// CLASSES
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'classes') {
    $rows = runQuery($pdo,
        "SELECT cl.class_id, co.course_code, co.course_name, cl.semester,
                cl.academic_year, cl.schedule_day,
                CONCAT(i.first_name,' ',i.last_name) AS instructor_name,
                cr.room_number
         FROM classes cl
         JOIN courses     co ON cl.course_id     = co.course_id
         JOIN instructors i  ON cl.instructor_id = i.instructor_id
         LEFT JOIN classrooms cr ON cl.classroom_id = cr.classroom_id
         WHERE cl.class_id      LIKE :q1
            OR co.course_code   LIKE :q2
            OR co.course_name   LIKE :q3
            OR cl.semester      LIKE :q4
            OR CONCAT(i.first_name,' ',i.last_name) LIKE :q5
            OR cr.room_number   LIKE :q6
         ORDER BY co.course_code, cl.semester
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,
         ':q5'=>$like,':q6'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['classes'] = array_map(function($r) {
            return [
                'name' => $r['course_code'] . ' — ' . $r['semester'],
                'meta' => $r['instructor_name'],
            ];
        }, $rows);
    } else {
        $results['classes'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// ENROLLMENTS
// ─────────────────────────────────────────────────────────────
if ($type === 'all' || $type === 'enrollments') {
    $rows = runQuery($pdo,
        "SELECT e.enrollment_id, e.enrollment_date, e.status,
                CONCAT(s.first_name,' ',s.last_name) AS student_name,
                s.email AS student_email,
                co.course_code, co.course_name, cl.semester
         FROM enrollments e
         JOIN students    s  ON e.student_id     = s.student_id
         JOIN classes     cl ON e.class_id       = cl.class_id
         JOIN courses     co ON cl.course_id     = co.course_id
         WHERE e.enrollment_id  LIKE :q1
            OR CONCAT(s.first_name,' ',s.last_name) LIKE :q2
            OR s.email          LIKE :q3
            OR co.course_code   LIKE :q4
            OR co.course_name   LIKE :q5
            OR cl.semester      LIKE :q6
         ORDER BY e.enrollment_date DESC
         LIMIT :lim",
        [':q1'=>$like,':q2'=>$like,':q3'=>$like,':q4'=>$like,
         ':q5'=>$like,':q6'=>$like,':lim'=>$limit]
    );

    if ($format === 'suggestions') {
        $results['enrollments'] = array_map(function($r) {
            return [
                'name' => $r['student_name'] . ' → ' . $r['course_code'],
                'meta' => $r['semester'],
            ];
        }, $rows);
    } else {
        $results['enrollments'] = $rows;
    }
    $total += count($rows);
}

// ─────────────────────────────────────────────────────────────
// Output
// ─────────────────────────────────────────────────────────────
echo json_encode([
    'query'   => $q,
    'type'    => $type,
    'total'   => $total,
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
