<?php
/**
 * config.php
 *
 * Central database configuration and PDO connection for SIMS.
 *
 * USAGE IN OTHER FILES:
 *   require_once 'config.php';       // from the project root
 *   require_once '../config.php';    // from a subdirectory
 *
 * After including this file, every page has access to $pdo.
 * Never write a new PDO() call anywhere else in the project.
 */

// ---------------------------------------------------------------
// Connection parameters
// ---------------------------------------------------------------
$host     = "localhost";
$dbname   = "advanced_school_db";
$username = "root";
$password = "";
$charset  = "utf8mb4";

// ---------------------------------------------------------------
// Data Source Name (DSN)
// Tells PDO which driver to use, which host, database and charset.
// ---------------------------------------------------------------
$port     = 3307;
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";

// ---------------------------------------------------------------
// PDO options
//   ERRMODE_EXCEPTION  – any database error throws a PDOException
//                        instead of silently returning false.
//   FETCH_ASSOC        – query results return associative arrays
//                        (column name as key) instead of numbered.
//   EMULATE_PREPARES   – false means PDO uses real prepared
//                        statements, giving proper type binding
//                        and stronger SQL-injection protection.
// ---------------------------------------------------------------
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ---------------------------------------------------------------
// Create the PDO connection
// If the connection fails, execution stops here with a clear
// development-friendly message.  The $pdo variable is available
// to every file that includes config.php.
// ---------------------------------------------------------------
try {
    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    // Stop execution — do not let the page continue without a DB.
    // htmlspecialchars() prevents any special characters in the
    // error message from being rendered as HTML.
    die(
        '<div style="'
            . 'font-family:Arial,sans-serif;'
            . 'background:#fef2f2;'
            . 'color:#991b1b;'
            . 'border:1px solid #fca5a5;'
            . 'border-radius:6px;'
            . 'padding:20px 24px;'
            . 'margin:40px auto;'
            . 'max-width:600px;'
        . '">'
        . '<strong>Database connection failed.</strong><br><br>'
        . 'Please check that:<br>'
        . '<ul style="margin:10px 0 0 18px;">'
        . '<li>XAMPP MySQL service is running</li>'
        . '<li>The database <em>advanced_school_db</em> has been created</li>'
        . '<li>Username is <em>root</em> and password is empty</li>'
        . '</ul>'
        . '<br><code style="font-size:0.85rem;">'
        . htmlspecialchars($e->getMessage())
        . '</code>'
        . '</div>'
    );
}
