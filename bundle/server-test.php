<?php
// Basic PHP info and session test
session_start();

// Test 1: Basic PHP functioning
echo "<h2>PHP Test Results:</h2>";
echo "<p>PHP is working!</p>";

// Test 2: Session functioning
$_SESSION['test'] = 'Test value';
if (isset($_SESSION['test'])) {
    echo "<p>Sessions are working! Test value: " . htmlspecialchars($_SESSION['test']) . "</p>";
} else {
    echo "<p>Warning: Sessions are not working properly!</p>";
}

// Test 3: Database connection
require 'db.php';
echo "<p>Database connection: ";
if ($db && isDatabaseAvailable()) {
    echo "Success! Connected to database.</p>";
} else {
    echo "Failed! Check database configuration.</p>";
}

// Test 4: Directory permissions
$testDir = __DIR__;
echo "<p>Directory permissions:<br>";
echo "Current directory: " . htmlspecialchars($testDir) . "<br>";
echo "Writable: " . (is_writable($testDir) ? 'Yes' : 'No') . "</p>";

// Test 5: PHP Configuration
echo "<h3>Important PHP Settings:</h3>";
echo "<pre>";
echo "session.save_handler: " . ini_get('session.save_handler') . "\n";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.use_cookies: " . ini_get('session.use_cookies') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "</pre>";

// Test 6: Check PHP extensions
echo "<h3>Required PHP Extensions:</h3>";
$required_extensions = array('pdo', 'pdo_mysql', 'session');
foreach ($required_extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? 'Loaded' : 'Not loaded') . "<br>";
}
?>