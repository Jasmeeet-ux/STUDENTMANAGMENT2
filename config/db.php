<?php
$host = "127.0.0.1"; // Use the IP to ensure it hits the right port
$port = "3306";      // Default XAMPP port
$user = "root";
$pass = "";
$db   = "new_project";   

try {
    // We add ;port=$port to the DSN string below
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Echo this to test if it's working, then delete this line
    // echo "Connected successfully to port 3307!"; 
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>