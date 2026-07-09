<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Fix section room numbers and capacities based on parent class
$conn->query("
    UPDATE sections s 
    JOIN classes c ON s.class_id = c.id 
    SET s.room_number = c.room_number, s.capacity = (c.capacity / 2)
");

// 2. Clear existing section_students mapping
$conn->query("TRUNCATE TABLE section_students");

// 3. Optional: we could delete all existing students and start fresh, but let's just keep them and add new ones.
// Getting starting reg_no index
$res = $conn->query("SELECT MAX(id) as m FROM users");
$startId = ($res->fetch_assoc()['m'] ?? 0) + 1;

$indianNames = [
    'Aarav', 'Vihaan', 'Vivaan', 'Ananya', 'Diya', 'Advik', 'Kabir', 'Anaya',
    'Aarohi', 'Shruti', 'Neha', 'Rohan', 'Aditya', 'Arjun', 'Karan', 'Priya',
    'Rahul', 'Sneha', 'Riya', 'Amit', 'Pooja', 'Vikram', 'Siddharth', 'Megha',
    'Varun', 'Nisha', 'Aakash', 'Kriti', 'Sameer', 'Tanvi', 'Rishabh', 'Isha',
    'Manish', 'Kavya', 'Gaurav', 'Anjali', 'Deepak', 'Sonal', 'Vishal', 'Swati'
];
$indianSurnames = [
    'Sharma', 'Verma', 'Gupta', 'Malhotra', 'Singh', 'Patel', 'Reddy', 'Rao',
    'Joshi', 'Chauhan', 'Thakur', 'Yadav', 'Mishra', 'Pandey', 'Tiwari', 'Das',
    'Bose', 'Chatterjee', 'Banerjee', 'Nair', 'Menon', 'Iyer', 'Pillai', 'Kumar'
];

$password = password_hash('password123', PASSWORD_DEFAULT);

$sectionsRes = $conn->query("SELECT id FROM sections");
$sections = [];
while ($row = $sectionsRes->fetch_assoc()) {
    $sections[] = $row['id'];
}

$studentCounter = $startId;

$conn->begin_transaction();

foreach ($sections as $sec_id) {
    // 30 students per section
    for ($i = 0; $i < 30; $i++) {
        $firstName = $indianNames[array_rand($indianNames)];
        $lastName = $indianSurnames[array_rand($indianSurnames)];
        $name = $firstName . ' ' . $lastName;
        $email = strtolower($firstName) . '.' . strtolower($lastName) . $studentCounter . '@student.edu';
        $regNo = 'STU-' . date('Y') . '-' . str_pad($studentCounter, 5, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, reg_no, email, password, role) VALUES (?, ?, ?, ?, 'student')");
        $stmt->bind_param("ssss", $name, $regNo, $email, $password);
        $stmt->execute();
        $student_id = $conn->insert_id;
        
        $stmt2 = $conn->prepare("INSERT INTO section_students (section_id, student_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $sec_id, $student_id);
        $stmt2->execute();
        
        $studentCounter++;
    }
}

$conn->commit();

echo "Successfully synced section rooms and capacities, and seeded exactly 30 authentic Indian students into every section!\n";
