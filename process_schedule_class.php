<?php
/**
 * process_schedule_class.php
 * Form Task 2 — Process the Schedule Class form submission.
 *
 * Flow:
 *   1.  Block non-POST requests.
 *   2.  Collect and sanitize POST values.
 *   3.  Validate each field with specific rules.
 *   4.  Verify each foreign key ID actually exists in its parent table.
 *   5.  INSERT into classes using a PDO prepared statement.
 *   6.  Redirect to schedule_class.php with ?success=1 or ?error=<msg>.
 *
 * Produces NO HTML output — only processes and redirects (PRG pattern).
 *
 * Foreign keys written to classes:
 *   course_id     → courses(course_id)
 *   instructor_id → instructors(instructor_id)
 *   classroom_id  → classrooms(classroom_id)
 */

// ---------------------------------------------------------------
// SECTION 1: Database connection
// ---------------------------------------------------------------
require_once "config.php";

// ---------------------------------------------------------------
// SECTION 2: Method guard
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: schedule_class.php");
    exit;
}

// ---------------------------------------------------------------
// SECTION 3: Collect and sanitize POST values
//
// semester      – trim() removes whitespace; kept as string.
// course_id     – (int) cast: non-numeric input becomes 0,
//                 which fails the > 0 check below.
// instructor_id – same integer cast approach.
// classroom_id  – same integer cast approach.
// ---------------------------------------------------------------
$semester      = trim($_POST['semester']      ?? '');
$course_id     = (int) ($_POST['course_id']     ?? 0);
$instructor_id = (int) ($_POST['instructor_id'] ?? 0);
$classroom_id  = (int) ($_POST['classroom_id']  ?? 0);

// ---------------------------------------------------------------
// SECTION 4: Validation
//
// Step A — field-level checks (empty, range, whitelist).
// Step B — database existence checks for every FK.
//
// All errors are collected first so the user sees every
// problem at once rather than one redirect per error.
// ---------------------------------------------------------------
$errors = [];

// --- Step A: Field-level validation ---

// Semester must be one of the known values (whitelist).
// This stops arbitrary strings being stored in the column.
$validSemesters = ["Semester 1", "Semester 2", "Semester 3"];
if ($semester === '') {
    $errors[] = "Please select a semester.";
} elseif (!in_array($semester, $validSemesters, true)) {
    $errors[] = "Invalid semester selected.";
}

// course_id must be a positive integer
if ($course_id <= 0) {
    $errors[] = "Please select a course.";
}

// instructor_id must be a positive integer
if ($instructor_id <= 0) {
    $errors[] = "Please select an instructor.";
}

// classroom_id must be a positive integer
if ($classroom_id <= 0) {
    $errors[] = "Please select a classroom.";
}

// ---------------------------------------------------------------
// SECTION 5: Foreign key existence checks
//
// Even after the integer check, a user could POST any integer
// (e.g. course_id=9999 via browser dev tools).
// We verify each ID against its parent table before attempting
// the INSERT — this is what "do not trust manually modified IDs"
// means in practice.
//
// We only run the DB checks when the integer itself passed
// validation above (skip if already flagged as <= 0 to avoid
// redundant queries).
// ---------------------------------------------------------------

// Verify course_id exists in courses
if ($course_id > 0) {
    $chkCourse = $pdo->prepare(
        "SELECT course_id FROM courses WHERE course_id = :id"
    );
    $chkCourse->execute([':id' => $course_id]);
    if (!$chkCourse->fetchColumn()) {
        $errors[] = "Selected course does not exist in the database.";
    }
}

// Verify instructor_id exists in instructors
if ($instructor_id > 0) {
    $chkInstr = $pdo->prepare(
        "SELECT instructor_id FROM instructors WHERE instructor_id = :id"
    );
    $chkInstr->execute([':id' => $instructor_id]);
    if (!$chkInstr->fetchColumn()) {
        $errors[] = "Selected instructor does not exist in the database.";
    }
}

// Verify classroom_id exists in classrooms
if ($classroom_id > 0) {
    $chkRoom = $pdo->prepare(
        "SELECT classroom_id FROM classrooms WHERE classroom_id = :id"
    );
    $chkRoom->execute([':id' => $classroom_id]);
    if (!$chkRoom->fetchColumn()) {
        $errors[] = "Selected classroom does not exist in the database.";
    }
}

// ---------------------------------------------------------------
// SECTION 6: Early exit on any validation error
// Redirect back with error message and the submitted values
// so the dropdowns re-select the user's previous choices
// (sticky form behaviour).
// ---------------------------------------------------------------
if (!empty($errors)) {
    $errorMsg = implode(" | ", $errors);

    $redirectUrl = "schedule_class.php"
        . "?error="         . urlencode($errorMsg)
        . "&semester="      . urlencode($semester)
        . "&course_id="     . urlencode((string) $course_id)
        . "&instructor_id=" . urlencode((string) $instructor_id)
        . "&classroom_id="  . urlencode((string) $classroom_id);

    header("Location: $redirectUrl");
    exit;
}

// ---------------------------------------------------------------
// SECTION 7: INSERT into classes using a prepared statement
//
// Named placeholders keep every user/form value completely
// separate from the SQL text — the driver handles all escaping.
//
// Columns written:
//   course_id     → FK referencing courses(course_id)
//   instructor_id → FK referencing instructors(instructor_id)
//   classroom_id  → FK referencing classrooms(classroom_id)
//   semester      → VARCHAR(20), validated against whitelist
//
// The classes table also has NOT NULL columns for schedule_day,
// start_time, end_time, academic_year, and max_students which
// have defaults or can be set to placeholder values here.
// We use the current year for academic_year and sensible
// defaults for the remaining required columns so the INSERT
// succeeds without those extra form fields.
//
// The UNIQUE constraint on (course_id, instructor_id, semester,
// academic_year) means SQLSTATE 23000 fires if the same
// instructor is scheduled for the same course in the same
// semester/year twice.
// ---------------------------------------------------------------
try {
    $stmt = $pdo->prepare(
        "INSERT INTO classes
            (course_id, instructor_id, classroom_id, semester, academic_year,
             schedule_day, start_time, end_time, max_students)
         VALUES
            (:course_id, :instructor_id, :classroom_id, :semester, :academic_year,
             :schedule_day, :start_time, :end_time, :max_students)"
    );

    $stmt->execute([
        ':course_id'     => $course_id,
        ':instructor_id' => $instructor_id,
        ':classroom_id'  => $classroom_id,
        ':semester'      => $semester,
        ':academic_year' => (int) date('Y'),   // current year as default
        ':schedule_day'  => 'TBA',             // To Be Announced — update later
        ':start_time'    => '08:00:00',        // placeholder start time
        ':end_time'      => '10:00:00',        // placeholder end time
        ':max_students'  => 40,                // default capacity
    ]);

    // -----------------------------------------------------------
    // SECTION 8: Success redirect
    // -----------------------------------------------------------
    header("Location: schedule_class.php?success=1");
    exit;

} catch (PDOException $e) {

    // -----------------------------------------------------------
    // SECTION 9: Error handling
    //
    // SQLSTATE 23000 = Integrity constraint violation.
    // The most likely cause here is the UNIQUE constraint on
    // (course_id, instructor_id, semester, academic_year) —
    // this instructor is already scheduled to teach this course
    // in this semester.
    // -----------------------------------------------------------
    if ($e->getCode() === '23000') {
        // htmlspecialchars() applied at the source so the value is safe
        // whether this message is URL-encoded, echoed, or logged.
        $errorMsg = "This instructor is already scheduled to teach that course "
                  . "in " . htmlspecialchars($semester, ENT_QUOTES, 'UTF-8') . ". "
                  . "Each course/instructor combination must be unique per semester.";
    } else {
        $errorMsg = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

    $redirectUrl = "schedule_class.php"
        . "?error="         . urlencode($errorMsg)
        . "&semester="      . urlencode($semester)
        . "&course_id="     . urlencode((string) $course_id)
        . "&instructor_id=" . urlencode((string) $instructor_id)
        . "&classroom_id="  . urlencode((string) $classroom_id);

    header("Location: $redirectUrl");
    exit;
}
