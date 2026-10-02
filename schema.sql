-- ================================================================
-- schema.sql
-- Student Information Management System (SIMS)
-- Database : advanced_school_db
-- Engine   : InnoDB  |  Charset: utf8mb4
-- ================================================================

-- ----------------------------------------------------------------
-- DATABASE
-- ----------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS advanced_school_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE advanced_school_db;

-- ----------------------------------------------------------------
-- DROP ALL TABLES (reverse dependency order) so reimport is clean
-- ----------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS classrooms;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS instructor_specializations;
DROP TABLE IF EXISTS office_details;
DROP TABLE IF EXISTS instructors;
DROP TABLE IF EXISTS departments;

SET FOREIGN_KEY_CHECKS = 1;


-- ================================================================
-- TABLE 1: departments
-- ================================================================
CREATE TABLE departments (
    department_id    INT          NOT NULL AUTO_INCREMENT,
    dept_code        VARCHAR(10)  NOT NULL,
    dept_name        VARCHAR(100) NOT NULL,
    hod_name         VARCHAR(150) DEFAULT NULL COMMENT 'Head of Department (optional)',
    established_year YEAR         DEFAULT NULL,

    CONSTRAINT pk_departments PRIMARY KEY (department_id),
    CONSTRAINT uq_dept_code   UNIQUE (dept_code),
    CONSTRAINT uq_dept_name   UNIQUE (dept_name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Root entity - academic departments';


-- ================================================================
-- TABLE 2: instructors
-- ================================================================
CREATE TABLE instructors (
    instructor_id   INT          NOT NULL AUTO_INCREMENT,
    department_id   INT          NOT NULL,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(150) NOT NULL,
    phone           VARCHAR(20)  DEFAULT NULL,
    employee_number VARCHAR(20)  NOT NULL,
    hire_date       DATE         DEFAULT NULL,

    CONSTRAINT pk_instructors      PRIMARY KEY (instructor_id),
    CONSTRAINT uq_instructor_email UNIQUE (email),
    CONSTRAINT uq_employee_number  UNIQUE (employee_number),
    CONSTRAINT fk_instructor_dept
        FOREIGN KEY (department_id)
        REFERENCES departments(department_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Teaching staff - belongs to one department';


-- ================================================================
-- TABLE 3: office_details
-- ================================================================
CREATE TABLE office_details (
    office_id       INT          NOT NULL AUTO_INCREMENT,
    instructor_id   INT          NOT NULL,
    building        VARCHAR(100) NOT NULL,
    office_number   VARCHAR(20)  NOT NULL,
    floor_number    TINYINT      DEFAULT NULL,
    phone_extension VARCHAR(10)  DEFAULT NULL,

    CONSTRAINT pk_office_details    PRIMARY KEY (office_id),
    CONSTRAINT uq_office_instructor UNIQUE (instructor_id),
    CONSTRAINT fk_office_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='1:1 extension of instructors - physical office info';


-- ================================================================
-- TABLE 4: instructor_specializations
-- ================================================================
CREATE TABLE instructor_specializations (
    specialization_id INT          NOT NULL AUTO_INCREMENT,
    instructor_id     INT          NOT NULL,
    specialization    VARCHAR(150) NOT NULL,
    certified         TINYINT(1)   NOT NULL DEFAULT 0
                          COMMENT '1 = formally certified, 0 = self-declared',

    CONSTRAINT pk_specializations PRIMARY KEY (specialization_id),
    CONSTRAINT uq_instructor_spec  UNIQUE (instructor_id, specialization),
    CONSTRAINT fk_spec_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='1:M extension of instructors - subject specializations';


-- ================================================================
-- TABLE 5: courses
-- ================================================================
CREATE TABLE courses (
    course_id     INT          NOT NULL AUTO_INCREMENT,
    department_id INT          NOT NULL,
    course_code   VARCHAR(20)  NOT NULL,
    course_name   VARCHAR(200) NOT NULL,
    description   TEXT         DEFAULT NULL,
    credits       TINYINT      NOT NULL DEFAULT 3,
    level         VARCHAR(20)  NOT NULL DEFAULT 'Undergraduate'
                      COMMENT 'e.g. Undergraduate, Postgraduate',

    CONSTRAINT pk_courses     PRIMARY KEY (course_id),
    CONSTRAINT uq_course_code UNIQUE (course_code),
    CONSTRAINT fk_course_dept
        FOREIGN KEY (department_id)
        REFERENCES departments(department_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Course catalogue - owned by a department';


-- ================================================================
-- TABLE 6: classrooms
-- ================================================================
CREATE TABLE classrooms (
    classroom_id  INT          NOT NULL AUTO_INCREMENT,
    room_number   VARCHAR(20)  NOT NULL,
    building      VARCHAR(100) NOT NULL,
    capacity      SMALLINT     NOT NULL DEFAULT 30,
    has_projector TINYINT(1)   NOT NULL DEFAULT 0,
    has_lab       TINYINT(1)   NOT NULL DEFAULT 0,

    CONSTRAINT pk_classrooms  PRIMARY KEY (classroom_id),
    CONSTRAINT uq_room_number UNIQUE (room_number)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Physical teaching rooms';


-- ================================================================
-- TABLE 7: classes
-- ================================================================
CREATE TABLE classes (
    class_id      INT        NOT NULL AUTO_INCREMENT,
    course_id     INT        NOT NULL,
    instructor_id INT        NOT NULL,
    classroom_id  INT        DEFAULT NULL COMMENT 'NULL for online classes',
    semester      VARCHAR(20) NOT NULL    COMMENT 'e.g. Semester 1, Semester 2',
    academic_year YEAR        NOT NULL,
    schedule_day  VARCHAR(50) NOT NULL    COMMENT 'e.g. Monday / Wednesday',
    start_time    TIME        NOT NULL,
    end_time      TIME        NOT NULL,
    max_students  SMALLINT    NOT NULL DEFAULT 40,

    CONSTRAINT pk_classes        PRIMARY KEY (class_id),
    CONSTRAINT uq_class_offering UNIQUE (course_id, instructor_id, semester, academic_year),
    CONSTRAINT fk_class_course
        FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_class_instructor
        FOREIGN KEY (instructor_id)
        REFERENCES instructors(instructor_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_class_classroom
        FOREIGN KEY (classroom_id)
        REFERENCES classrooms(classroom_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Scheduled class offering - ties course + instructor + room';


-- ================================================================
-- TABLE 8: students
-- ================================================================
CREATE TABLE students (
    student_id     INT          NOT NULL AUTO_INCREMENT,
    reg_number     VARCHAR(20)  NOT NULL,
    first_name     VARCHAR(100) NOT NULL,
    last_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    phone          VARCHAR(20)  DEFAULT NULL,
    date_of_birth  DATE         DEFAULT NULL,
    gender         VARCHAR(10)  DEFAULT NULL COMMENT 'e.g. Male, Female, Other',
    admission_date DATE         NOT NULL,

    CONSTRAINT pk_students      PRIMARY KEY (student_id),
    CONSTRAINT uq_student_email UNIQUE (email),
    CONSTRAINT uq_reg_number    UNIQUE (reg_number)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Student records - enrolled through the enrollments table';


-- ================================================================
-- TABLE 9: enrollments
-- ================================================================
CREATE TABLE enrollments (
    enrollment_id   INT        NOT NULL AUTO_INCREMENT,
    student_id      INT        NOT NULL,
    class_id        INT        NOT NULL,
    enrollment_date DATE       NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'Active'
                        COMMENT 'Active | Withdrawn | Completed',

    CONSTRAINT pk_enrollments  PRIMARY KEY (enrollment_id),
    CONSTRAINT uq_enrollment   UNIQUE (student_id, class_id),
    CONSTRAINT fk_enroll_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_enroll_class
        FOREIGN KEY (class_id)
        REFERENCES classes(class_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='M:M junction - students enrolled in classes';


-- ================================================================
-- TABLE 10: grades
-- ================================================================
CREATE TABLE grades (
    grade_id       INT           NOT NULL AUTO_INCREMENT,
    enrollment_id  INT           NOT NULL,
    marks_obtained DECIMAL(5,2)  NOT NULL  COMMENT 'e.g. 78.50',
    total_marks    DECIMAL(5,2)  NOT NULL DEFAULT 100.00,
    letter_grade   VARCHAR(5)    DEFAULT NULL COMMENT 'e.g. A, B+, C',
    remarks        VARCHAR(255)  DEFAULT NULL,
    graded_date    DATE          NOT NULL,

    CONSTRAINT pk_grades           PRIMARY KEY (grade_id),
    CONSTRAINT uq_grade_enrollment UNIQUE (enrollment_id),
    CONSTRAINT fk_grade_enrollment
        FOREIGN KEY (enrollment_id)
        REFERENCES enrollments(enrollment_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='1:1 extension of enrollments - final grades';


-- ================================================================
-- SEED DATA  (dependency order — do not reorder)
-- ================================================================

-- 1. departments
INSERT INTO departments (dept_code, dept_name, hod_name, established_year) VALUES
    ('CS',   'Computer Science',        'Prof. James Otieno', 2001),
    ('MATH', 'Mathematics',             'Dr. Grace Wanjiku',  1998),
    ('ENG',  'English & Communication', 'Ms. Fatuma Baraka',  2005);

-- 2. instructors  (CS=1, MATH=2, ENG=3)
INSERT INTO instructors (department_id, first_name, last_name, email, phone, employee_number, hire_date) VALUES
    (1, 'Kevin',  'Mutua',   'k.mutua@school.ac.ke',   '0711000001', 'EMP-001', '2015-03-01'),
    (1, 'Amina',  'Hassan',  'a.hassan@school.ac.ke',  '0711000002', 'EMP-002', '2018-07-15'),
    (2, 'George', 'Kamau',   'g.kamau@school.ac.ke',   '0711000003', 'EMP-003', '2012-01-10');

-- 3. office_details  (Kevin=1, George=3)
INSERT INTO office_details (instructor_id, building, office_number, floor_number, phone_extension) VALUES
    (1, 'Science Block',  'OF-101', 1, '301'),
    (3, 'Maths Building', 'OF-202', 2, '415');

-- 4. instructor_specializations
INSERT INTO instructor_specializations (instructor_id, specialization, certified) VALUES
    (1, 'Database Systems',        1),
    (1, 'Web Development',         1),
    (2, 'Artificial Intelligence', 1),
    (3, 'Linear Algebra',          1),
    (3, 'Statistics',              0);

-- 5. courses  (CS=1, MATH=2, ENG=3)
INSERT INTO courses (department_id, course_code, course_name, description, credits, level) VALUES
    (1, 'CS101', 'Introduction to Computing',  'Fundamentals of computer science.',       3, 'Undergraduate'),
    (1, 'CS302', 'Database Systems',           'Relational databases and SQL.',           3, 'Undergraduate'),
    (2, 'MA201', 'Calculus I',                 'Limits, derivatives, and integrals.',     4, 'Undergraduate'),
    (3, 'EN101', 'Communication Skills',       'Academic writing and oral presentation.', 2, 'Undergraduate');

-- 6. classrooms
INSERT INTO classrooms (room_number, building, capacity, has_projector, has_lab) VALUES
    ('ROOM-101', 'Main Block',     45, 1, 0),
    ('LAB-A',    'Science Block',  30, 1, 1),
    ('ROOM-202', 'Maths Building', 50, 1, 0);

-- 7. classes  (CS101=1,CS302=2,MA201=3,EN101=4 | Kevin=1,Amina=2,George=3 | ROOM-101=1,LAB-A=2,ROOM-202=3)
INSERT INTO classes (course_id, instructor_id, classroom_id, semester, academic_year, schedule_day, start_time, end_time, max_students) VALUES
    (1, 1, 1, 'Semester 1', 2026, 'Monday / Wednesday', '08:00:00', '10:00:00', 45),
    (2, 1, 2, 'Semester 1', 2026, 'Tuesday / Thursday', '10:00:00', '12:00:00', 30),
    (3, 3, 3, 'Semester 1', 2026, 'Monday / Friday',    '11:00:00', '13:00:00', 50),
    (4, 2, 1, 'Semester 2', 2026, 'Wednesday',          '14:00:00', '16:00:00', 45);

-- 8. students
INSERT INTO students (reg_number, first_name, last_name, email, phone, date_of_birth, gender, admission_date) VALUES
    ('STU-2024-001', 'Alice', 'Mwangi',   'alice.mwangi@school.ac.ke',   '0712345678', '2003-04-15', 'Female', '2024-09-01'),
    ('STU-2024-002', 'Brian', 'Odhiambo', 'brian.odhiambo@school.ac.ke', '0723456789', '2002-11-22', 'Male',   '2024-09-01'),
    ('STU-2024-003', 'Carol', 'Wanjiku',  'carol.wanjiku@school.ac.ke',  '0734567890', '2004-01-08', 'Female', '2024-09-01');

-- 9. enrollments  (Alice=1,Brian=2,Carol=3 | CS101 class=1,CS302=2,MA201=3,EN101=4)
INSERT INTO enrollments (student_id, class_id, enrollment_date, status) VALUES
    (1, 1, '2024-09-05', 'Active'),
    (1, 3, '2024-09-05', 'Active'),
    (2, 1, '2024-09-05', 'Active'),
    (2, 2, '2024-09-06', 'Active'),
    (3, 3, '2024-09-05', 'Active'),
    (3, 4, '2024-09-06', 'Active');

-- 10. grades  (enrollment_id 1–6 match insertion order above)
INSERT INTO grades (enrollment_id, marks_obtained, total_marks, letter_grade, remarks, graded_date) VALUES
    (1, 82.00, 100.00, 'A',  'Excellent',   '2024-12-15'),
    (3, 74.50, 100.00, 'B+', 'Good work',   '2024-12-15'),
    (4, 91.00, 100.00, 'A+', 'Outstanding', '2024-12-15');


-- ================================================================
-- TABLE 11: admins  (added for admin authentication system)
-- ================================================================
CREATE TABLE IF NOT EXISTS admins (
    admin_id    INT          NOT NULL AUTO_INCREMENT,
    username    VARCHAR(60)  NOT NULL,
    password    VARCHAR(255) NOT NULL  COMMENT 'bcrypt hash via password_hash()',
    full_name   VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_admins       PRIMARY KEY (admin_id),
    CONSTRAINT uq_admin_user   UNIQUE (username),
    CONSTRAINT uq_admin_email  UNIQUE (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Administrator accounts — passwords stored as bcrypt hashes';

-- ----------------------------------------------------------------
-- Seed admin account
-- Username : admin
-- Password : Admin@1234   (bcrypt hash below — change after first login)
-- ----------------------------------------------------------------
INSERT IGNORE INTO admins (username, password, full_name, email) VALUES (
    'admin',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'System Administrator',
    'admin@school.ac.ke'
);
-- NOTE: The hash above corresponds to the string "Admin@1234".
-- Log in with: username=admin  password=Admin@1234
-- Then change the password immediately via Admin > Settings.
