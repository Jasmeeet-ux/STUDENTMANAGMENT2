<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

$reg_no = $_GET['reg_no'] ?? '';
if (!$reg_no) { header("Location: students_list.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM user_details WHERE reg_no = ? LIMIT 1");
$stmt->execute([$reg_no]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) { header("Location: students_list.php"); exit; }

$stmtU = $pdo->prepare("SELECT password FROM users WHERE reg_no = ? LIMIT 1");
$stmtU->execute([$reg_no]);
$userRow = $stmtU->fetch(PDO::FETCH_ASSOC);
$plain_password = $userRow['password'] ?? '';

// Fetch enrollments
$stmtE = $pdo->prepare("SELECT * FROM student_enrollments WHERE reg_no = ? ORDER BY id ASC");
$stmtE->execute([$reg_no]);
$enrollments = $stmtE->fetchAll(PDO::FETCH_ASSOC);

// If no enrollments in new table yet, build from user_details (backward compat)
if (empty($enrollments)) {
    $enrollments = [[
        'id'           => 0,
        'reg_no'       => $reg_no,
        'coursename'   => $student['coursename']   ?? '',
        'batch_no'     => $student['batch_no']     ?? '',
        'startingdate' => $student['startingdate'] ?? '',
        'completeddate'=> $student['completeddate']?? '',
        'addonvalue'   => $student['addonvalue']   ?? '',
    ]];
}

function fmt_time($t) { return $t ? date("g:i A", strtotime($t)) : ''; }
function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$batches = [];
try {
    $batches = $pdo->query("SELECT id, batch_name, timing_start, timing_end, day_type FROM batches ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $batches = []; }
$batches_json = json_encode($batches);

$courses_list = [
    'Advanced Digital Marketing with AI (ADMAI)',
    'Master Digital Marketing with AI & Automation (MDMAI)',
    'Graphic Design (Photo Editing) with AI (GDPAI)',
    'Graphic Design (Video Editing) with AI (GDVAI)',
    'Graphic Design (Photo & Video Editing) with AI & Generative AI Automation (GDPVAI)',
    'Advanced Data Analytics with AI (ADAAI)',
    'Master Data Analytics with Generative AI (MDAAI)',
    'UI/UX Design with AI (UIUXAI)',
    'Advanced Full Stack Web Design & Development with AI (AFSDDAI)',
    'Master Full Stack Web Design & Development with AI & AI Automation (MFSDDAI)',
    'AI Future Leaders Professional Certification (AIFL Pro)',
];

$success = $_SESSION['edit_success'] ?? ''; unset($_SESSION['edit_success']);
$error   = $_SESSION['edit_error']   ?? ''; unset($_SESSION['edit_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Student | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:#f8fafc;display:flex;}
.sidebar{width:245px;min-width:245px;background:#0f172a;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;overflow-y:auto;z-index:200;}
.sidebar::-webkit-scrollbar{width:3px;}
.sidebar::-webkit-scrollbar-thumb{background:#1e293b;border-radius:3px;}
.sb-brand{padding:22px 20px 16px;border-bottom:1px solid #1e293b;}
.sb-brand h2{font-size:15px;font-weight:800;color:#fff;}
.sb-brand p{font-size:11px;color:#475569;margin-top:3px;}
.sb-section{font-size:9.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:1.2px;padding:14px 20px 5px;}
.sidebar a{display:flex;align-items:center;gap:10px;color:#94a3b8;padding:9px 20px;text-decoration:none;font-size:13px;font-weight:500;transition:all 0.15s;border-left:3px solid transparent;}
.sidebar a:hover{background:#1e293b;color:#e2e8f0;border-left-color:#334155;}
.sidebar a.active{background:#1e3a8a;color:#fff;font-weight:700;border-left-color:#3b82f6;}
.sb-icon{font-size:15px;width:20px;text-align:center;}
.sb-bottom{margin-top:auto;border-top:1px solid #1e293b;padding:8px 0;}
.main{margin-left:245px;flex:1;height:100vh;overflow-y:auto;display:flex;flex-direction:column;}
.page-header{padding:20px 40px;background:#fff;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:10;display:flex;align-items:center;justify-content:space-between;}
.page-header-left h1{font-size:20px;font-weight:800;color:#0f172a;}
.page-header-left p{font-size:13px;color:#64748b;margin-top:3px;}
.back-btn{display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;}
.back-btn:hover{background:#e2e8f0;}
.alert{padding:12px 18px;border-radius:10px;margin:20px 40px 0;font-size:13.5px;font-weight:500;}
.alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;}
.alert-err{background:#fee2e2;border:1px solid #fecaca;color:#dc2626;}
.student-badge{margin:20px 40px 0;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:12px;}
.student-badge .badge-name{font-size:15px;font-weight:700;color:#1e40af;}
.student-badge .badge-reg{font-size:12px;color:#3b82f6;margin-top:2px;}
.form-wrapper{max-width:900px;padding:0 40px 60px;}
.section-row{display:flex;gap:40px;padding:36px 0;border-bottom:1px solid #e2e8f0;}
.section-row:last-of-type{border-bottom:none;}
.section-label{width:200px;flex-shrink:0;padding-top:4px;}
.section-label h3{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:4px;}
.section-label p{font-size:12px;color:#94a3b8;line-height:1.5;}
.section-fields{flex:1;display:flex;flex-direction:column;gap:18px;}
.fields-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.fields-row.single{grid-template-columns:1fr;}
.field{display:flex;flex-direction:column;gap:6px;}
label{font-size:12.5px;font-weight:600;color:#475569;}
.req{color:#ef4444;margin-left:2px;}
.field-note{font-size:11px;color:#94a3b8;}
input[type=text],input[type=tel],input[type=email],input[type=date],select{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#1e293b;background:#fff;transition:border-color 0.15s;outline:none;}
input::placeholder{color:#cbd5e1;}
input:focus,select:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,0.08);}
input[readonly]{background:#f8fafc;color:#64748b;cursor:not-allowed;}
select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5' stroke='%23999' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;background-size:18px;padding-right:36px;cursor:pointer;}
.enrollment-rows{display:flex;flex-direction:column;gap:14px;}
.enrollment-row{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;position:relative;}
.enrollment-row .row-num{font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;}
.enrollment-row .remove-row{position:absolute;top:12px;right:12px;background:#fee2e2;color:#dc2626;border:none;width:26px;height:26px;border-radius:6px;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;}
.enrollment-row .remove-row:hover{background:#fecaca;}
.add-enrollment-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#eff6ff;color:#2563eb;border:1.5px dashed #93c5fd;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;width:fit-content;}
.add-enrollment-btn:hover{background:#dbeafe;}
.save-bar{padding:18px 40px;background:#fff;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;position:sticky;bottom:0;}
.save-bar p{font-size:12px;color:#94a3b8;}
.save-bar p span{color:#ef4444;}
.save-bar-btns{display:flex;gap:10px;}
.cancel-btn{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:11px 22px;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-flex;align-items:center;}
.submit-btn{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;padding:11px 30px;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;box-shadow:0 4px 14px rgba(37,99,235,0.28);}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Admin Panel</h2><p>Culture of Internet</p></div>
  <div class="sb-section">Main</div>
  <a href="dashboard.php"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-section">Students</div>
  <a href="add_student.php"><span class="sb-icon">➕</span>Add Student</a>
  <a href="students_list.php" class="active"><span class="sb-icon">📋</span>Students List</a>
  <div class="sb-section">Batches &amp; Courses</div>
  <a href="Batch.php"><span class="sb-icon">🏫</span>Batches</a>
  <a href="view_batch.php"><span class="sb-icon">👁️</span>View Batch</a>
  <a href="courses.php"><span class="sb-icon">📚</span>Courses</a>
  <div class="sb-section">Sub Admins</div>
  <a href="add_sub_admin.php"><span class="sb-icon">👤</span>Add Sub Admin</a>
  <a href="sub_admins_list.php"><span class="sb-icon">👥</span>Sub Admins List</a>
  <div class="sb-bottom"><a href="logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="page-header">
    <div class="page-header-left"><h1>✏️ Edit Student</h1><p>Update student details below</p></div>
    <a href="students_list.php" class="back-btn">← Back to List</a>
  </div>

  <?php if($success): ?><div class="alert alert-ok">✅ <?= h($success) ?></div><?php endif; ?>
  <?php if($error):   ?><div class="alert alert-err">❌ <?= h($error) ?></div><?php endif; ?>

  <div class="student-badge">
    <div style="font-size:28px;">👤</div>
    <div><div class="badge-name"><?= h($student['name']) ?></div><div class="badge-reg">Reg: <?= h($student['reg_no']) ?></div></div>
  </div>

  <form action="update_student.php" method="POST">
  <input type="hidden" name="original_reg_no" value="<?= h($student['reg_no']) ?>">
  <div class="form-wrapper">

    <!-- PERSONAL INFO -->
    <div class="section-row">
      <div class="section-label"><h3>Personal Information</h3><p>Basic identity and educational background</p></div>
      <div class="section-fields">
        <div class="fields-row">
          <div class="field"><label>Student Name <span class="req">*</span></label><input type="text" name="name" value="<?= h($student['name']) ?>" required></div>
          <div class="field"><label>Gender <span class="req">*</span></label>
            <select name="gender" required>
              <option value="" disabled>Select gender</option>
              <?php foreach(['Male','Female','Other'] as $g): ?>
              <option value="<?= $g ?>" <?= $student['gender']===$g?'selected':'' ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="fields-row">
          <div class="field"><label>Date of Birth <span class="req">*</span></label><input type="date" name="dob" value="<?= h($student['dob']) ?>" required></div>
          <div class="field"><label>Qualification <span class="req">*</span></label>
            <select name="qualification" required>
              <option value="" disabled>Select qualification</option>
              <?php foreach(['K–12 Student (8th to 12th)','12th Pass','Pursuing Graduation (Non-IT)','Pursuing Graduation (IT)','Graduate','Postgraduate'] as $q): ?>
              <option value="<?= h($q) ?>" <?= $student['qualification']===$q?'selected':'' ?>><?= h($q) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="fields-row">
          <div class="field"><label>Registration No.</label><input type="text" value="<?= h($student['reg_no']) ?>" readonly><span class="field-note">Cannot be changed</span></div>
          <div class="field"><label>Password <span class="req">*</span></label><input type="text" name="password" value="<?= h($plain_password) ?>" required></div>
        </div>
      </div>
    </div>

    <!-- CONTACT INFO -->
    <div class="section-row">
      <div class="section-label"><h3>Contact Information</h3><p>Phone, WhatsApp and email address</p></div>
      <div class="section-fields">
        <div class="fields-row">
          <div class="field"><label>Phone Number <span class="req">*</span></label><input type="tel" name="phoneno" value="<?= h($student['phoneno']) ?>" pattern="[0-9]{10}" maxlength="10" required></div>
          <div class="field"><label>WhatsApp Number <span class="req">*</span></label><input type="tel" name="whatsapp" value="<?= h($student['whatsapp']) ?>" pattern="[0-9]{10}" maxlength="10" required></div>
        </div>
        <div class="fields-row single"><div class="field"><label>Email <span class="req">*</span></label><input type="email" name="gmail" value="<?= h($student['gmail']) ?>" required></div></div>
        <div class="fields-row single"><div class="field"><label>Address <span class="req">*</span></label><input type="text" name="address" value="<?= h($student['address']) ?>" required></div></div>
      </div>
    </div>

    <!-- ENROLLMENTS -->
    <div class="section-row">
      <div class="section-label"><h3>Course Enrollments</h3><p>Edit, add or remove course enrollments</p></div>
      <div class="section-fields">
        <div class="enrollment-rows" id="enrollmentRows">
          <?php foreach($enrollments as $i => $e): $n = $i+1; ?>
          <div class="enrollment-row" id="erow_<?= $n ?>">
            <div class="row-num">Course <?= $n ?></div>
            <?php if($n > 1): ?>
            <button type="button" class="remove-row" onclick="removeRow(<?= $n ?>)" title="Remove">✕</button>
            <?php endif; ?>
            <input type="hidden" name="enrollments[<?= $n ?>][id]" value="<?= (int)($e['id'] ?? 0) ?>">
            <div class="fields-row single" style="margin-bottom:12px;">
              <div class="field"><label>Course Name <span class="req">*</span></label>
                <select name="enrollments[<?= $n ?>][coursename]" required>
                  <option value="" disabled>Select course</option>
                  <?php foreach($courses_list as $c): ?>
                  <option value="<?= h($c) ?>" <?= ($e['coursename']===$c)?'selected':'' ?>><?= h($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="fields-row" style="margin-bottom:12px;">
              <div class="field"><label>Batch <span class="req">*</span></label>
                <select name="enrollments[<?= $n ?>][batch_no]" required>
                  <option value="" disabled>Select batch</option>
                  <?php foreach($batches as $b): ?>
                  <option value="<?= h($b['batch_name']) ?>" <?= ($e['batch_no']===$b['batch_name'])?'selected':'' ?>>
                    <?= h($b['batch_name']) ?><?= $b['timing_start'] ? ' | '.fmt_time($b['timing_start']).' – '.fmt_time($b['timing_end']) : '' ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field"><label>Add-on Value <span class="req">*</span></label><input type="text" name="enrollments[<?= $n ?>][addonvalue]" value="<?= h($e['addonvalue']) ?>" required></div>
            </div>
            <div class="fields-row">
              <div class="field"><label>Starting Date <span class="req">*</span></label><input type="date" name="enrollments[<?= $n ?>][startingdate]" value="<?= h($e['startingdate']) ?>" required></div>
              <div class="field"><label>Completion Date <span class="req">*</span></label><input type="date" name="enrollments[<?= $n ?>][completeddate]" value="<?= h($e['completeddate']) ?>" required></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="add-enrollment-btn" onclick="addEnrollmentRow()">➕ Add Another Course</button>
      </div>
    </div>

    <!-- PARENT INFO -->
    <div class="section-row">
      <div class="section-label"><h3>Parent Details</h3><p>Guardian name and contact number</p></div>
      <div class="section-fields">
        <div class="fields-row">
          <div class="field"><label>Parent Name <span class="req">*</span></label><input type="text" name="parentname" value="<?= h($student['parentname']) ?>" required></div>
          <div class="field"><label>Parent Contact No. <span class="req">*</span></label><input type="tel" name="parentsno" value="<?= h($student['parentsno']) ?>" pattern="[0-9]{10}" maxlength="10" required></div>
        </div>
      </div>
    </div>

  </div>
  <div class="save-bar">
    <p>All fields marked <span>*</span> are required</p>
    <div class="save-bar-btns">
      <a href="students_list.php" class="cancel-btn">Cancel</a>
      <button type="submit" class="submit-btn">💾 Save Changes</button>
    </div>
  </div>
  </form>
</div>

<script>
const allBatches = <?= $batches_json ?>;
const courses    = <?= json_encode($courses_list) ?>;
let rowCount = <?= count($enrollments) ?>;

function buildBatchOptions(sel) {
    let html = '<option value="" disabled>Select batch</option>';
    allBatches.forEach(b => {
        const s = b.batch_name === sel ? 'selected' : '';
        html += `<option value="${esc(b.batch_name)}" ${s}>${esc(b.batch_name)}</option>`;
    });
    return html;
}
function buildCourseOptions(sel) {
    let html = '<option value="" disabled>Select course</option>';
    courses.forEach(c => {
        html += `<option value="${esc(c)}" ${c===sel?'selected':''}>${esc(c)}</option>`;
    });
    return html;
}
function addEnrollmentRow() {
    rowCount++;
    const n = rowCount;
    const div = document.createElement('div');
    div.className = 'enrollment-row'; div.id = 'erow_' + n;
    div.innerHTML = `
        <div class="row-num">Course ${n}</div>
        <button type="button" class="remove-row" onclick="removeRow(${n})" title="Remove">✕</button>
        <input type="hidden" name="enrollments[${n}][id]" value="0">
        <div class="fields-row single" style="margin-bottom:12px;">
          <div class="field"><label>Course Name <span class="req">*</span></label>
            <select name="enrollments[${n}][coursename]" required>${buildCourseOptions('')}</select>
          </div>
        </div>
        <div class="fields-row" style="margin-bottom:12px;">
          <div class="field"><label>Batch <span class="req">*</span></label>
            <select name="enrollments[${n}][batch_no]" required>${buildBatchOptions('')}</select>
          </div>
          <div class="field"><label>Add-on Value <span class="req">*</span></label>
            <input type="text" name="enrollments[${n}][addonvalue]" placeholder="e.g. Photoshop" required>
          </div>
        </div>
        <div class="fields-row">
          <div class="field"><label>Starting Date <span class="req">*</span></label>
            <input type="date" name="enrollments[${n}][startingdate]" required>
          </div>
          <div class="field"><label>Completion Date <span class="req">*</span></label>
            <input type="date" name="enrollments[${n}][completeddate]" required>
          </div>
        </div>`;
    document.getElementById('enrollmentRows').appendChild(div);
    renumberRows();
}
function removeRow(n) { const el=document.getElementById('erow_'+n); if(el) el.remove(); renumberRows(); }
function renumberRows() {
    document.querySelectorAll('.enrollment-row').forEach((r,i)=>{
        const l=r.querySelector('.row-num'); if(l) l.textContent='Course '+(i+1);
    });
}
function esc(s){ const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
</script>
</body>
</html>