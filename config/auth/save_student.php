<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    header("Location: add_student.php");
    exit;
}

// ── Validate core fields ──
$required_core = ['name','gender','phoneno','whatsapp','gmail','address','qualification','dob','reg_no','password','parentname','parentsno'];
$missing = [];
foreach ($required_core as $f) {
    if (empty(trim($_POST[$f] ?? ''))) $missing[] = $f;
}
if (!empty($missing)) {
    $_SESSION['add_error'] = "Missing required fields: " . implode(', ', $missing);
    header("Location: add_student.php");
    exit;
}

// ── Validate enrollments ──
$enrollments = $_POST['enrollments'] ?? [];
if (empty($enrollments)) {
    $_SESSION['add_error'] = "Please add at least one course enrollment.";
    header("Location: add_student.php");
    exit;
}

$valid_enrollments = [];
foreach ($enrollments as $idx => $e) {
    $coursename    = trim($e['coursename']    ?? '');
    $batch_no      = trim($e['batch_no']      ?? '');
    $addonvalue    = trim($e['addonvalue']    ?? '');
    $startingdate  = trim($e['startingdate']  ?? '');
    $completeddate = trim($e['completeddate'] ?? '');
    if ($coursename && $batch_no && $startingdate && $completeddate) {
        $valid_enrollments[] = compact('coursename','batch_no','addonvalue','startingdate','completeddate');
    }
}
if (empty($valid_enrollments)) {
    $_SESSION['add_error'] = "Please fill in all course enrollment fields.";
    header("Location: add_student.php");
    exit;
}

$reg_no = trim($_POST['reg_no']);

// ── Check duplicate reg_no ──
$check = $pdo->prepare("SELECT COUNT(*) FROM user_details WHERE reg_no = ?");
$check->execute([$reg_no]);
if ($check->fetchColumn() > 0) {
    $_SESSION['add_error'] = "Registration number '$reg_no' already exists.";
    header("Location: add_student.php");
    exit;
}

// ── Use first enrollment as primary (for user_details backward compat) ──
$primary = $valid_enrollments[0];

try {
    $pdo->beginTransaction();

    // Insert into user_details (primary enrollment — backward compatible)
    $pdo->prepare("INSERT INTO user_details
        (name, gender, phoneno, whatsapp, gmail, address, qualification, dob,
         coursename, startingdate, completeddate, addonvalue, parentname, parentsno, reg_no, batch_no)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
    ->execute([
        trim($_POST['name']),    trim($_POST['gender']),
        trim($_POST['phoneno']), trim($_POST['whatsapp']),
        trim($_POST['gmail']),   trim($_POST['address']),
        trim($_POST['qualification']), trim($_POST['dob']),
        $primary['coursename'],  $primary['startingdate'],
        $primary['completeddate'], $primary['addonvalue'],
        trim($_POST['parentname']), trim($_POST['parentsno']),
        $reg_no, $primary['batch_no']
    ]);

    // Insert into users (for login)
    $hashed_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (name, reg_no, email, password) VALUES (?,?,?,?)")
    ->execute([trim($_POST['name']), $reg_no, trim($_POST['gmail']), $hashed_password]);

    // Insert ALL enrollments into student_enrollments
    $stmt_enroll = $pdo->prepare("INSERT INTO student_enrollments
        (reg_no, coursename, batch_no, startingdate, completeddate, addonvalue)
        VALUES (?,?,?,?,?,?)");
    foreach ($valid_enrollments as $e) {
        $stmt_enroll->execute([
            $reg_no, $e['coursename'], $e['batch_no'],
            $e['startingdate'], $e['completeddate'], $e['addonvalue']
        ]);
    }

    $pdo->commit();

    $_SESSION['add_success'] = "Student '{$_POST['name']}' added successfully with " . count($valid_enrollments) . " course(s)!";
    header("Location: add_student.php");
    exit;

} catch(Exception $ex) {
    $pdo->rollBack();
    $_SESSION['add_error'] = "Database error: " . $ex->getMessage();
    header("Location: add_student.php");
    exit;
}
?>