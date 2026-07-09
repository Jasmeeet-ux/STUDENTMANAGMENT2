<?php
// seed_teacher_profiles.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$firstNames = ['Aarav', 'Vihaan', 'Aditya', 'Arjun', 'Sai', 'Kabir', 'Ananya', 'Diya', 'Aditi', 'Riya', 'Myra'];
$lastNames = ['Sharma', 'Singh', 'Kumar', 'Patel', 'Gupta', 'Verma', 'Reddy', 'Rao', 'Yadav'];
$bloodGroups = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
$qualifications = ['Ph.D in Computer Science', 'M.Tech in Engineering', 'M.Sc in Applied Mathematics', 'Ph.D in Physics', 'MBA in Finance'];
$genders = ['Male', 'Female'];

$res = $conn->query("SELECT id FROM teachers");
while ($row = $res->fetch_assoc()) {
    $tid = $row['id'];
    
    $phone = '98' . rand(10000000, 99999999);
    $designation_id = rand(1, 3);
    $emp_type_id = rand(1, 3);
    $gender = $genders[array_rand($genders)];
    $blood_group = $bloodGroups[array_rand($bloodGroups)];
    
    $dob_ts = strtotime("19" . rand(70, 95) . "-" . rand(1, 12) . "-" . rand(1, 28));
    $dob = date('Y-m-d', $dob_ts);
    
    $join_ts = strtotime("20" . rand(15, 24) . "-" . rand(1, 12) . "-" . rand(1, 28));
    $joining_date = date('Y-m-d', $join_ts);
    
    $address = rand(10, 999) . " " . $lastNames[array_rand($lastNames)] . " Street, New Delhi, India";
    
    $emg_name = $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
    $emg_phone = '99' . rand(10000000, 99999999);
    
    $qual = $qualifications[array_rand($qualifications)];
    $exp = rand(2, 25);
    
    $sql = "UPDATE teachers SET 
        phone = '$phone',
        designation_id = $designation_id,
        employment_type_id = $emp_type_id,
        gender = '$gender',
        blood_group = '$blood_group',
        dob = '$dob',
        joining_date = '$joining_date',
        address = '$address',
        emergency_contact_name = '$emg_name',
        emergency_contact_phone = '$emg_phone',
        qualification = '$qual',
        experience_years = $exp
        WHERE id = $tid";
        
    $conn->query($sql);
}

echo "Teacher profiles seeded successfully!\n";
