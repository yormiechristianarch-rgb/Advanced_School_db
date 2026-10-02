<?php
/**
 * process_add_course.php
 * Form Task 1 — Process the Add Course form submission.
 *
 * Flow:
 *   1. Block any request that is not POST.
 *   2. Collect raw POST values.
 *   3. Sanitize / trim text fields.
 *   4. Validate every field with specific rules.
 *   5. Verify dept_id actually exists in the departments table.
 *   6. Insert using a PDO prepared statement.
 *   7. Redirect to add_course.php with ?success=1 or ?error=<msg>.
 *
 * This file produces NO HTML output.
 * It only processes data and redirects — following the
 * Post / Redirect / Get (PRG) pattern, which prevents
 * duplicate submissions when the user refreshes the browser.
 */

// ---------------------------------------------------------------
// SECTION 1: Database connection
// Must be the first executable line so $pdo is available for
// both the dept_id verification query and the INSERT.
// ---------------------------------------------------------------
require_once "config.php";

// ---------------------------------------------------------------
// SECTION 2: Method guard
// If someone visits this URL directly (GET request) or links
// to it, send them back to the form immediately.
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_course.php");
    exit;
}

// ---------------------------------------------------------------
// SECTION 3: Collect and sanitize raw POST values
//
// trim()   – removes accidental leading/trailing whitespace
//            that users often type without noticing.
// (int)    – casts the value to a PHP integer.  Any non-numeric
//            string becomes 0, which fails validation below.
//            This is the correct way to handle integer fields
//            from untrusted POST data.
// ?? ''    – null coalescing operator: if the key does not exist
//            in $_POST (e.g. the form was tampered with), use an
//            empty string / 0 as the default.
// ---------------------------------------------------------------
$course_code  = trim($_POST['course_code']  ?? '');
$course_title = trim($_POST['course_title'] ?? '');
$credits      = (int) ($_POST['credits']    ?? 0);
$dept_id      = (int) ($_POST['dept_id']    ?? 0);

// ---------------------------------------------------------------
// SECTION 4: Server-side validation
//
// Client-side (HTML required/min/max) can be bypassed by anyone
// with browser dev tools or curl, so we always re-validate here.
//
// Rules:
//   course_code  – required, max 20 chars
//   course_title – required, max 200 chars
//   credits      – integer between 1 and 20
//   dept_id      – integer greater than 0
//
// All errors are collected into an array first so we can show
// every problem at once rather than one at a time.
// ---------------------------------------------------------------
$errors = [];

// --- Course Code ---
if ($course_code === '') {
    $errors[] = "Course code is required.";
} elseif (strlen($course_code) > 20) {
    $errors[] = "Course code must not exceed 20 characters.";
}

// --- Course Title ---
if ($course_title === '') {
    $errors[] = "Course title is required.";
} elseif (strlen($course_title) > 200) {
    $errors[] = "Course title must not exceed 200 characters.";
}

// --- Credits ---
if ($credits < 1 || $credits > 20) {
    $errors[] = "Credits must be a whole number between 1 and 20.";
}

// --- Department ID ---
// (int) cast already happened above.  Here we check it is > 0.
// A value of 0 means either nothing was selected or the field
// was removed/tampered with.
if ($dept_id <= 0) {
    $errors[] = "Please select a department.";
}

// ---------------------------------------------------------------
// SECTION 5: Early exit on validation failure
// Build a redirect URL that carries:
//   ?error=<encoded message>
//   &course_code=... &course_title=... etc.
// so add_course.php can re-populate the form fields (sticky form).
// urlencode() makes the values safe to put in a URL.
// ---------------------------------------------------------------
if (!empty($errors)) {
    $errorMsg = implode(" | ", $errors);

    $redirectUrl = "add_course.php"
        . "?error="        . urlencode($errorMsg)
        . "&course_code="  . urlencode($course_code)
        . "&course_title=" . urlencode($course_title)
        . "&credits="      . urlencode((string) $credits)
        . "&dept_id="      . urlencode((string) $dept_id);

    header("Location: $redirectUrl");
    exit;
}

// ---------------------------------------------------------------
// SECTION 6: Verify dept_id exists in the database
//
// Even after the integer check above, someone could POST any
// integer (e.g. dept_id=9999).  We must confirm the ID actually
// exists in the departments table before using it as a foreign
// key — this is what "do not trust hidden or manually modified
// IDs" means in practice.
//
// We use a prepared statement to avoid SQL injection.
// fetchColumn() returns the first column of the first row, or
// false if no row was found.
// ---------------------------------------------------------------
$checkStmt = $pdo->prepare(
    "SELECT department_id FROM departments WHERE department_id = :dept_id"
);
$checkStmt->execute([':dept_id' => $dept_id]);
$deptExists = $checkStmt->fetchColumn();

if (!$deptExists) {
    $redirectUrl = "add_course.php"
        . "?error="        . urlencode("Selected department does not exist. Please choose from the list.")
        . "&course_code="  . urlencode($course_code)
        . "&course_title=" . urlencode($course_title)
        . "&credits="      . urlencode((string) $credits);

    header("Location: $redirectUrl");
    exit;
}

// ---------------------------------------------------------------
// SECTION 7: Insert the new course using a prepared statement
//
// Named placeholders (:course_code, :course_title, etc.) keep
// user data completely separate from the SQL text.
// The database driver handles escaping internally — we never
// manually escape values for SQL.
//
// We wrap the execute() in try/catch to handle the one expected
// database error: a duplicate course_code (UNIQUE constraint
// violation, SQLSTATE 23000).
// Any other PDOException is re-thrown and handled by PHP.
// ---------------------------------------------------------------
try {
    $insertStmt = $pdo->prepare(
        "INSERT INTO courses (course_code, course_name, credits, department_id)
         VALUES (:course_code, :course_title, :credits, :dept_id)"
    );

    $insertStmt->execute([
        ':course_code'  => $course_code,
        ':course_title' => $course_title,
        ':credits'      => $credits,
        ':dept_id'      => $dept_id,
    ]);

    // -------------------------------------------------------
    // SECTION 8: Success redirect
    // The insert worked — send the user back to the form with
    // a success flag.  The form will show a green confirmation
    // message and be blank so they can add another course.
    // -------------------------------------------------------
    header("Location: add_course.php?success=1");
    exit;

} catch (PDOException $e) {

    // -------------------------------------------------------
    // SECTION 9: Graceful duplicate course_code handling
    //
    // MySQL SQLSTATE 23000 = Integrity constraint violation.
    // The most likely cause here is a duplicate course_code
    // because that column has a UNIQUE constraint in the schema.
    //
    // We give the user a clear, specific message rather than
    // a raw database error.
    //
    // For any other database error we show a generic message
    // and include the technical detail for the developer.
    // -------------------------------------------------------
    if ($e->getCode() === '23000') {
        // htmlspecialchars() applied here so the value is safe
        // whether the message is later URL-encoded, echoed, or logged.
        $errorMsg = "Course code \"" . htmlspecialchars($course_code, ENT_QUOTES, 'UTF-8') . "\" already exists. "
                  . "Each course must have a unique code.";
    } else {
        $errorMsg = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

    $redirectUrl = "add_course.php"
        . "?error="        . urlencode($errorMsg)
        . "&course_code="  . urlencode($course_code)
        . "&course_title=" . urlencode($course_title)
        . "&credits="      . urlencode((string) $credits)
        . "&dept_id="      . urlencode((string) $dept_id);

    header("Location: $redirectUrl");
    exit;
}
