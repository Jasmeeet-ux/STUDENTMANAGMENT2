<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }
if (empty($_POST)) { header("Location: students_list.php"); exit; }

$original_reg_no = trim($_POST['original_reg_no'] ?? '');
if (!$original_reg_no) { header("Location: students_list.php"); exit; }

$required = ['name','gender','phoneno','whatsapp','gmail','address','qualification','dob','password','parentname','parentsno'];
$missing = [];
foreach ($required as $f) { if (empty(trim($_POST[$f] ?? ''))) $missing[] = $f; }
if (!empty($missing)) {
    $_SESSION['edit_error'] = "Missing fields: " . implode(', ', $missing);
    header("Location: edit_student.php?reg_no=" . urlencode($original_reg_no));
    exit;
}

$enrollments = $_POST['enrollments'] ?? [];
$valid = [];
foreach ($enrollments as $e) {
    $c = trim($e['coursename'] ?? ''); $b = trim($e['batch_no'] ?? '');
    $s = trim($e['startingdate'] ?? ''); $cd = trim($e['completeddate'] ?? '');
    if ($c && $b && $s && $cd) {
        $valid[] = ['id'=>(int)($e['id']??0), 'coursename'=>$c, 'batch_no'=>$b,
                    'startingdate'=>$s, 'completeddate'=>$cd, 'addonvalue'=>trim($e['addonvalue']??'')];
    }
}
if (empty($valid)) {
    $_SESSION['edit_error'] = "Please add at least one valid course enrollment.";
    header("Location: edit_student.php?reg_no=" . urlencode($original_reg_no));
    exit;
}

try {
    $pdo->beginTransaction();

    // Update user_details (personal info + primary enrollment)
    $primary = $valid[0];
    $pdo->prepare("UPDATE user_details SET
        name=?, gender=?, phoneno=?, whatsapp=?, gmail=?, address=?,
        qualification=?, dob=?, coursename=?, startingdate=?, completeddate=?,
        addonvalue=?, parentname=?, parentsno=?, batch_no=?
        WHERE reg_no=?")
    ->execute([
        trim($_POST['name']), trim($_POST['gender']),
        trim($_POST['phoneno']), trim($_POST['whatsapp']),
        trim($_POST['gmail']), trim($_POST['address']),
        trim($_POST['qualification']), trim($_POST['dob']),
        $primary['coursename'], $primary['startingdate'],
        $primary['completeddate'], $primary['addonvalue'],
        trim($_POST['parentname']), trim($_POST['parentsno']),
        $primary['batch_no'], $original_reg_no
    ]);

    // Update users table
    $pdo->prepare("UPDATE users SET name=?, email=?, password=? WHERE reg_no=?")
    ->execute([trim($_POST['name']), trim($_POST['gmail']), trim($_POST['password']), $original_reg_no]);

    // Sync enrollments — delete all then re-insert
    $pdo->prepare("DELETE FROM student_enrollments WHERE reg_no=?")->execute([$original_reg_no]);
    $stmt_e = $pdo->prepare("INSERT INTO student_enrollments (reg_no, coursename, batch_no, startingdate, completeddate, addonvalue) VALUES (?,?,?,?,?,?)");
    foreach ($valid as $e) {
        $stmt_e->execute([$original_reg_no, $e['coursename'], $e['batch_no'], $e['startingdate'], $e['completeddate'], $e['addonvalue']]);
    }

    $pdo->commit();
    $_SESSION['edit_success'] = "Student updated successfully!";
    header("Location: students_list.php?updated=1");
    exit;

} catch(Exception $ex) {
    $pdo->rollBack();
    $_SESSION['edit_error'] = "Error: " . $ex->getMessage();
    header("Location: edit_student.php?reg_no=" . urlencode($original_reg_no));
    exit;
}
?>