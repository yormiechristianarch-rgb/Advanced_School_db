<?php
/**
 * process_enroll_student.php
 * Form Task 3 — Process the Enroll Student form submission.
 *
 * This file performs TWO database writes inside a single transaction:
 *   Write 1 → INSERT into students
 *   Write 2 → INSERT into enrollments (uses student_id from Write 1)
 *
 * Transaction sequence (required by the assignment):
 *   $pdo->beginTransaction()   — start the transaction
 *   INSERT into students        — Write 1
 *   $pdo->lastInsertId()        — capture the new student_id
 *   INSERT into enrollments     — Write 2
 *   $pdo->commit()              — make both writes permanent
 *   $pdo->rollBack()            — called in catch{} if anything fails
 *
 * Produces NO HTML — only processes and redirects (PRG pattern).
 */

// ---------------------------------------------------------------
// SECTION 1: Database connection
// ---------------------------------------------------------------
require_once "config.php";

// ---------------------------------------------------------------
// SECTION 2: Method guard
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: enroll_student.php");
    exit;
}

// ---------------------------------------------------------------
// SECTION 3: Collect and sanitize POST values
//
// Text fields   → trim() strips accidental whitespace.
// class_id      → (int) cast converts any non-numeric input to 0,
//                 which fails the > 0 check in validation.
// reg_date      → trim() only; format is validated below.
// ---------------------------------------------------------------
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name']  ?? '');
$email      = trim($_POST['email']      ?? '');
$reg_date   = trim($_POST['reg_date']   ?? '');
$gender     = trim($_POST['gender']     ?? '');
$class_id   = (int) ($_POST['class_id'] ?? 0);

// ---------------------------------------------------------------
// SECTION 4: Server-side validation
//
// Every rule is checked before any database operation starts.
// Errors are collected into an array so the user sees all
// problems at once.
// ---------------------------------------------------------------
$errors = [];

// --- First Name ---
if ($first_name === '') {
    $errors[] = "First name is required.";
} elseif (strlen($first_name) > 100) {
    $errors[] = "First name must not exceed 100 characters.";
}

// --- Last Name ---
if ($last_name === '') {
    $errors[] = "Last name is required.";
} elseif (strlen($last_name) > 100) {
    $errors[] = "Last name must not exceed 100 characters.";
}

// --- Gender ---
$allowed_genders = ['Male', 'Female'];
if ($gender === '') {
    $errors[] = "Please select a gender.";
} elseif (!in_array($gender, $allowed_genders, true)) {
    $errors[] = "Gender must be Male or Female.";
}

// --- Email ---
// PHP's FILTER_VALIDATE_EMAIL checks for a structurally valid
// email address (local@domain.tld format).
// This does NOT check whether the address exists — it only
// confirms the format is legitimate.
if ($email === '') {
    $errors[] = "Email address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address (e.g. name@domain.com).";
} elseif (strlen($email) > 150) {
    $errors[] = "Email address must not exceed 150 characters.";
}

// --- Registration Date ---
// Validate that the date string is in YYYY-MM-DD format and
// represents a real calendar date.
// DateTime::createFromFormat returns false for invalid dates
// (e.g. 2024-02-30 does not exist).
if ($reg_date === '') {
    $errors[] = "Registration date is required.";
} else {
    $dateObj = DateTime::createFromFormat('Y-m-d', $reg_date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $reg_date) {
        $errors[] = "Registration date must be a valid date in YYYY-MM-DD format.";
    }
}

// --- Class ID ---
if ($class_id <= 0) {
    $errors[] = "Please select a class.";
}

// ---------------------------------------------------------------
// SECTION 5: Foreign key existence check — class_id
//
// Confirm the submitted class_id actually exists in the classes
// table before we use it as a foreign key in enrollments.
// A user could tamper with the form and submit any integer.
// ---------------------------------------------------------------
if ($class_id > 0) {
    $chkClass = $pdo->prepare(
        "SELECT class_id FROM classes WHERE class_id = :id"
    );
    $chkClass->execute([':id' => $class_id]);
    if (!$chkClass->fetchColumn()) {
        $errors[] = "The selected class does not exist in the database.";
    }
}

// ---------------------------------------------------------------
// SECTION 6: Early exit on validation failure
// Redirect back with all sticky values so the form re-populates.
// ---------------------------------------------------------------
if (!empty($errors)) {
    $errorMsg = implode(" | ", $errors);

    $redirectUrl = "enroll_student.php"
        . "?error="      . urlencode($errorMsg)
        . "&first_name=" . urlencode($first_name)
        . "&last_name="  . urlencode($last_name)
        . "&email="      . urlencode($email)
        . "&reg_date="   . urlencode($reg_date)
        . "&gender="     . urlencode($gender)
        . "&class_id="   . urlencode((string) $class_id);

    header("Location: $redirectUrl");
    exit;
}

// ---------------------------------------------------------------
// SECTION 7: Database transaction
//
// WHY A TRANSACTION IS REQUIRED HERE:
//
// This operation writes to TWO tables in sequence:
//   1. INSERT into students       → creates the student record
//   2. INSERT into enrollments    → links that student to a class
//
// The enrollment INSERT depends on the student_id that is only
// known AFTER the student INSERT succeeds (via lastInsertId()).
//
// Without a transaction, if Write 1 succeeds but Write 2 fails
// (e.g. the class is already full, a network error occurs, or
// the server crashes), we end up with a student in the database
// who is not enrolled in anything.  That is an inconsistent
// state — a student record without an enrollment.
//
// With a transaction:
//   - beginTransaction() tells MySQL to hold both changes in a
//     temporary buffer instead of writing to disk immediately.
//   - If both INSERTs succeed → commit() makes them permanent.
//   - If anything throws an exception → rollBack() discards the
//     student INSERT as if it never happened.
//
// The database is always left in a consistent state:
//   either BOTH records exist, or NEITHER exists.
//
// This is the ACID principle: Atomicity — the two writes are
// treated as a single indivisible unit.
// ---------------------------------------------------------------
try {

    // -----------------------------------------------------------
    // Step 1: Start the transaction
    // All subsequent SQL runs inside this transaction context.
    // -----------------------------------------------------------
    $pdo->beginTransaction();

    // -----------------------------------------------------------
    // Step 2: Build a unique registration number
    // The students table has a NOT NULL UNIQUE reg_number column.
    // We generate one from the current timestamp so it is always
    // unique without needing a separate counter table.
    // Format: STU-YYYY-<timestamp_microseconds>
    // -----------------------------------------------------------
    $reg_number = 'STU-' . date('Y') . '-' . substr(str_replace('.', '', microtime(true)), -6);

    // -----------------------------------------------------------
    // Step 3: INSERT the student record (Write 1)
    //
    // Named placeholders — no user value is concatenated into SQL.
    // The prepared statement is compiled once; execute() binds
    // the values at run time.
    // -----------------------------------------------------------
    $studentStmt = $pdo->prepare(
        "INSERT INTO students
            (reg_number, first_name, last_name, email, gender, admission_date)
         VALUES
            (:reg_number, :first_name, :last_name, :email, :gender, :admission_date)"
    );

    $studentStmt->execute([
        ':reg_number'    => $reg_number,
        ':first_name'    => $first_name,
        ':last_name'     => $last_name,
        ':email'         => $email,
        ':gender'        => $gender,
        ':admission_date'=> $reg_date,
    ]);

    // -----------------------------------------------------------
    // Step 4: Capture the auto-generated student_id
    //
    // lastInsertId() returns the AUTO_INCREMENT primary key
    // assigned to the row we just inserted.
    // This is the only correct way to get the new ID — do NOT
    // query for it by email because another concurrent request
    // could have inserted a different row between our INSERT
    // and that SELECT.
    // lastInsertId() is connection-scoped and transaction-safe.
    // -----------------------------------------------------------
    $new_student_id = (int) $pdo->lastInsertId();

    // Safety check — should never be 0 if the INSERT succeeded
    if ($new_student_id === 0) {
        throw new RuntimeException("Failed to retrieve the new student ID.");
    }

    // -----------------------------------------------------------
    // Step 5: INSERT the enrollment record (Write 2)
    //
    // This uses the $new_student_id we captured in Step 4.
    // The UNIQUE constraint on (student_id, class_id) in the
    // enrollments table means SQLSTATE 23000 fires if this
    // student was somehow already enrolled in this class.
    // -----------------------------------------------------------
    $enrollStmt = $pdo->prepare(
        "INSERT INTO enrollments
            (student_id, class_id, enrollment_date, status)
         VALUES
            (:student_id, :class_id, :enrollment_date, :status)"
    );

    $enrollStmt->execute([
        ':student_id'      => $new_student_id,
        ':class_id'        => $class_id,
        ':enrollment_date' => $reg_date,
        ':status'          => 'Active',
    ]);

    // -----------------------------------------------------------
    // Step 6: COMMIT — make both writes permanent
    //
    // Only reached if BOTH INSERTs completed without error.
    // After commit(), the rows are visible to other connections
    // and are durable (written to disk).
    // -----------------------------------------------------------
    $pdo->commit();

    // Redirect to the form with a success flag
    header("Location: enroll_student.php?success=1");
    exit;

} catch (PDOException $e) {

    // -----------------------------------------------------------
    // Step 7: ROLLBACK — discard both writes
    //
    // Any PDOException thrown by either INSERT (or by any other
    // DB operation inside the try block) lands here.
    //
    // rollBack() reverses every change made since beginTransaction()
    // so the students table is left exactly as it was before this
    // request.  The student record from Write 1 is gone as if it
    // was never inserted.
    //
    // We then determine the user-friendly error message:
    //   SQLSTATE 23000 on the student INSERT   → duplicate email
    //   SQLSTATE 23000 on the enrollment INSERT → duplicate enrollment
    //   Anything else                           → generic DB error
    // -----------------------------------------------------------

    // Only roll back if a transaction is actually active.
    // (If beginTransaction() itself threw, there is nothing to roll back.)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Determine specific vs generic error message
    if ($e->getCode() === '23000') {
        // htmlspecialchars() applied at the source — $email is user-supplied input.
        $errorMsg = "A student with the email address \""
                  . htmlspecialchars($email, ENT_QUOTES, 'UTF-8')
                  . "\" is already registered. "
                  . "Please use a different email address.";
    } else {
        $errorMsg = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

    $redirectUrl = "enroll_student.php"
        . "?error="      . urlencode($errorMsg)
        . "&first_name=" . urlencode($first_name)
        . "&last_name="  . urlencode($last_name)
        . "&email="      . urlencode($email)
        . "&reg_date="   . urlencode($reg_date)
        . "&gender="     . urlencode($gender)
        . "&class_id="   . urlencode((string) $class_id);

    header("Location: $redirectUrl");
    exit;

} catch (RuntimeException $e) {

    // Catch the safety check exception from Step 4
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $redirectUrl = "enroll_student.php"
        . "?error=" . urlencode($e->getMessage())
        . "&first_name=" . urlencode($first_name)
        . "&last_name="  . urlencode($last_name)
        . "&email="      . urlencode($email)
        . "&reg_date="   . urlencode($reg_date)
        . "&gender="     . urlencode($gender)
        . "&class_id="   . urlencode((string) $class_id);

    header("Location: $redirectUrl");
    exit;
}
