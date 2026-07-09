<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

if (!$courseId) {
    die("<h2 style='text-align:center;margin-top:100px;'>Invalid course ID.</h2>");
}

// Check if user completed the entire course
$stmt = $pdo->prepare("
    SELECT 
        c.id, c.title, c.created_by,
        COUNT(l.id) AS total_lessons,
        COUNT(CASE WHEN p.completed = 1 THEN 1 END) AS completed_lessons,
        MAX(p.updated_at) AS completion_date
    FROM courses c
    JOIN lessons l ON c.id = l.course_id
    LEFT JOIN progress p ON p.lesson_id = l.id AND p.user_id = ?
    WHERE c.id = ?
    GROUP BY c.id
    HAVING completed_lessons = total_lessons
");
$stmt->execute([$userId, $courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("<h2 style='text-align:center;margin-top:100px;'>❌ You haven’t completed this course yet.</h2>");
}

$user = getCurrentUser();
$completionDate = date('F d, Y', strtotime($course['completion_date']));
$certificateId = 'COI-' . strtoupper(substr(md5($userId . $courseId . $course['completion_date']), 0, 8));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate of Completion - <?= htmlspecialchars($course['title']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  background: #f0f4ff;
  font-family: 'Poppins', sans-serif;
}
.certificate {
  max-width: 900px;
  margin: 50px auto;
  background: white;
  border: 10px solid #2563eb;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(37,99,235,0.15);
  padding: 50px 70px;
  text-align: center;
  position: relative;
}
.certificate::before {
  content: "";
  position: absolute;
  inset: 10px;
  border: 2px dashed #93c5fd;
  border-radius: 15px;
}
.logo {
  width: 90px;
  margin-bottom: 20px;
}
.signature {
  border-top: 2px solid #2563eb;
  width: 200px;
  margin: 30px auto 0;
  font-size: 0.9rem;
  color: #2563eb;
}
.download-btn {
  display: inline-block;
  margin-top: 40px;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  color: white;
  padding: 12px 30px;
  border-radius: 8px;
  text-decoration: none;
  transition: 0.3s;
}
.download-btn:hover {
  background: linear-gradient(135deg, #1d4ed8, #2563eb);
  transform: scale(1.03);
}
.watermark {
  position: absolute;
  top: 45%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-25deg);
  font-size: 70px;
  color: rgba(37,99,235,0.06);
  font-weight: bold;
  white-space: nowrap;
}
@media print {
  .download-btn { display: none; }
  body { background: white; }
}
</style>
</head>
<body>

<div class="certificate">
  <div class="watermark">Culture of Internet</div>
  <img src="/assets/images/logo.png" alt="Logo" class="logo">
  <h1 class="text-4xl font-bold text-blue-800 mb-2">Certificate of Completion</h1>
  <p class="text-gray-700 mb-6 text-lg">This is to certify that</p>

  <h2 class="text-4xl font-bold text-blue-600 mb-3">
      <?= htmlspecialchars($user['name'] ?? $user['first_name'] ?? 'Student') ?>
  </h2>

  <p class="text-gray-700 text-lg mb-6">has successfully completed the course</p>
  <h3 class="text-2xl font-semibold text-gray-800 mb-6">
      “<?= htmlspecialchars($course['title']) ?>”
  </h3>

  <p class="text-gray-500 mb-8">with dedication and excellence</p>

  <div class="grid grid-cols-2 gap-8 text-sm text-gray-600 mt-8 mb-6">
    <div><strong>Credential ID:</strong> <?= $certificateId ?></div>
    <div><strong>Completion Date:</strong> <?= $completionDate ?></div>
  </div>

  <div class="signature">
    Authorized Signature<br>
    <span class="text-gray-400 text-xs">Culture of Internet Academy</span>
  </div>

  <a href="#" onclick="window.print()" class="download-btn">
    <i class="fa fa-download mr-2"></i> Download / Print Certificate
  </a>
</div>

</body>
</html>
