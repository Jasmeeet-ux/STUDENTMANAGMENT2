<?php
require_once 'db.php';

echo "<pre>";

echo "1. This file: " . __FILE__ . "\n";
$upload_dir = __DIR__ . '/../../uploads/student_assignments/';
echo "2. Upload dir: " . realpath($upload_dir) . "\n";
echo "3. Dir exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "\n";

if (!is_dir($upload_dir)) {
    $made = @mkdir($upload_dir, 0755, true);
    echo "4. mkdir: " . ($made ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "4. Dir already exists\n";
}

echo "5. Writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "\n";

$test = file_put_contents($upload_dir . 'test.txt', 'ok');
echo "6. Write test: " . ($test !== false ? 'SUCCESS' : 'FAILED') . "\n";
if ($test !== false) unlink($upload_dir . 'test.txt');

echo "</pre>";
?>

<form method="POST" enctype="multipart/form-data" style="padding:20px;font-family:sans-serif;">
    <p>Koi bhi file select karo aur Upload Test karo:</p><br>
    <input type="file" name="testfile">
    <button type="submit">Test Upload</button>
</form>

<?php
if (!empty($_FILES['testfile']['name'])) {
    echo "<pre>";
    echo "File name: " . $_FILES['testfile']['name'] . "\n";
    echo "Error code: " . $_FILES['testfile']['error'] . "\n";
    echo "Tmp path: " . $_FILES['testfile']['tmp_name'] . "\n";
    echo "Tmp exists: " . (file_exists($_FILES['testfile']['tmp_name']) ? 'YES' : 'NO') . "\n";
    $dest = $upload_dir . 'test_' . time() . '_' . $_FILES['testfile']['name'];
    echo "Destination: " . $dest . "\n";
    $result = move_uploaded_file($_FILES['testfile']['tmp_name'], $dest);
    echo "Upload result: " . ($result ? 'SUCCESS!' : 'FAILED') . "\n";
    echo "</pre>";
}
?>