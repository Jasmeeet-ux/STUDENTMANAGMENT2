<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1-9: B.Tech (4), 10-14: BBA,B.Com,etc (3), 15: B.Arch (5)
$courseDurations = [
    1 => 4, 2 => 4, 3 => 4, 4 => 4, 5 => 4, 6 => 4, 7 => 4, 8 => 4, 9 => 4,
    10 => 3, 11 => 3, 12 => 3, 13 => 3, 14 => 3,
    15 => 5
];

$years = ["1st Year", "2nd Year", "3rd Year", "4th Year", "5th Year"];

foreach ($courseDurations as $course_id => $duration) {
    
    // Get existing classes for this course
    $res = $conn->query("SELECT id FROM classes WHERE course_id = $course_id ORDER BY id ASC");
    $existing = [];
    while($row = $res->fetch_assoc()) {
        $existing[] = $row['id'];
    }
    
    $deptRes = $conn->query("SELECT department_id FROM courses WHERE id = $course_id");
    $dept_id = $deptRes->fetch_assoc()['department_id'] ?? rand(1,15);
    
    for ($i = 0; $i < $duration; $i++) {
        $name = $years[$i];
        $room = "Room " . rand(101, 599) . ['A','B','C'][rand(0,2)];
        $capacity = rand(4, 12) * 10; // 40 to 120
        
        $teacherRes = $conn->query("SELECT id FROM teachers WHERE department_id = $dept_id ORDER BY RAND() LIMIT 1");
        $teacher_id = $teacherRes->fetch_assoc()['id'] ?? rand(1, 100);
        
        if (isset($existing[$i])) {
            $class_id = $existing[$i];
            $conn->query("UPDATE classes SET name = '$name', room_number = '$room', capacity = $capacity, teacher_id = $teacher_id WHERE id = $class_id");
        } else {
            $conn->query("INSERT INTO classes (name, course_id, teacher_id, room_number, capacity) VALUES ('$name', $course_id, $teacher_id, '$room', $capacity)");
            $class_id = $conn->insert_id;
        }
        
        // Also let's ensure this class has at least 2 sections (e.g. Section A, Section B)
        $secRes = $conn->query("SELECT id FROM sections WHERE class_id = $class_id");
        if ($secRes->num_rows == 0) {
            $conn->query("INSERT INTO sections (class_id, name, teacher_id, room_number, capacity) VALUES ($class_id, 'Section A', $teacher_id, '$room', " . ($capacity/2) . ")");
            
            $teacherRes2 = $conn->query("SELECT id FROM teachers WHERE department_id = $dept_id ORDER BY RAND() LIMIT 1");
            $teacher_id2 = $teacherRes2->fetch_assoc()['id'] ?? rand(1, 100);
            $conn->query("INSERT INTO sections (class_id, name, teacher_id, room_number, capacity) VALUES ($class_id, 'Section B', $teacher_id2, '$room', " . ($capacity/2) . ")");
        }
    }
    
    // Delete any extra classes beyond the duration
    for ($i = $duration; $i < count($existing); $i++) {
        $class_id = $existing[$i];
        $conn->query("DELETE FROM sections WHERE class_id = $class_id");
        $conn->query("DELETE FROM classes WHERE id = $class_id");
    }
}

echo "Class management optimized with realistic academic years and sections!\n";
