<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$realisticSubjects = [
    1 => [ // B.Tech CSE
        'Data Structures and Algorithms', 'Operating Systems', 'Database Management Systems', 'Computer Networks', 
        'Theory of Computation', 'Compiler Design', 'Software Engineering', 'Artificial Intelligence', 
        'Machine Learning', 'Computer Architecture', 'Web Technologies', 'Cryptography and Network Security'
    ],
    2 => [ // B.Tech IT
        'Information Security', 'Web Development', 'Cloud Computing', 'Internet of Things', 
        'Data Warehousing and Mining', 'E-Commerce', 'Mobile Application Development', 'Human Computer Interaction',
        'Software Testing', 'Big Data Analytics', 'Information Retrieval', 'Multimedia Systems'
    ],
    3 => [ // B.Tech ME
        'Thermodynamics', 'Fluid Mechanics', 'Strength of Materials', 'Manufacturing Processes',
        'Kinematics of Machinery', 'Heat and Mass Transfer', 'Machine Design', 'Automobile Engineering',
        'Refrigeration and Air Conditioning', 'Robotics', 'CAD/CAM', 'Industrial Engineering'
    ],
    4 => [ // B.Tech CE
        'Structural Analysis', 'Geotechnical Engineering', 'Transportation Engineering', 'Environmental Engineering',
        'Surveying', 'Fluid Mechanics', 'Concrete Technology', 'Hydrology', 
        'Construction Management', 'Steel Structures', 'Irrigation Engineering', 'Highway Engineering'
    ],
    5 => [ // B.Tech EE
        'Electrical Machines', 'Power Systems', 'Control Systems', 'Power Electronics',
        'Network Theory', 'Electromagnetic Fields', 'Measurements and Instrumentation', 'Signals and Systems',
        'High Voltage Engineering', 'Renewable Energy Sources', 'Electrical Drives', 'Microprocessors'
    ],
    6 => [ // B.Tech ECE
        'Analog Electronics', 'Digital Electronics', 'Signals and Systems', 'Communication Systems',
        'Microprocessors and Microcontrollers', 'VLSI Design', 'Digital Signal Processing', 'Antennas and Wave Propagation',
        'Optical Communication', 'Wireless Communication', 'Embedded Systems', 'Control Systems'
    ],
    7 => [ // B.Tech Bio
        'Biochemistry', 'Microbiology', 'Molecular Biology', 'Genetics',
        'Bioprocess Engineering', 'Bioinformatics', 'Immunology', 'Cell Biology',
        'Plant Biotechnology', 'Animal Biotechnology', 'Enzyme Technology', 'Biochemical Thermodynamics'
    ],
    8 => [ // B.Tech Chem
        'Chemical Reaction Engineering', 'Mass Transfer Operations', 'Heat Transfer Operations', 'Fluid Mechanics',
        'Process Dynamics and Control', 'Thermodynamics', 'Plant Design and Economics', 'Polymer Technology',
        'Petroleum Refining', 'Transport Phenomena', 'Chemical Technology', 'Environmental Engineering'
    ],
    9 => [ // B.Tech Aero
        'Aerodynamics', 'Aircraft Structures', 'Flight Mechanics', 'Propulsion',
        'Spacecraft Dynamics', 'Avionics', 'Computational Fluid Dynamics', 'Aircraft Design',
        'Wind Tunnel Testing', 'Aeroelasticity', 'Rocket Propulsion', 'Helicopter Dynamics'
    ],
    10 => [ // BBA
        'Principles of Management', 'Financial Accounting', 'Business Economics', 'Marketing Management',
        'Human Resource Management', 'Business Communication', 'Organizational Behavior', 'Business Law',
        'Financial Management', 'Operations Research', 'Strategic Management', 'International Business'
    ],
    11 => [ // B.Com
        'Financial Accounting', 'Business Law', 'Corporate Accounting', 'Cost Accounting',
        'Income Tax Law and Practice', 'Business Statistics', 'Auditing', 'Financial Management',
        'Management Accounting', 'Business Environment', 'E-Commerce', 'Entrepreneurship'
    ],
    12 => [ // B.A. Arts
        'English Literature', 'History of India', 'Political Theory', 'Sociology',
        'Psychology', 'Economics', 'Public Administration', 'Geography',
        'Philosophy', 'Cultural Studies', 'World History', 'International Relations'
    ],
    13 => [ // B.Sc Science
        'Mechanics and Properties of Matter', 'Inorganic Chemistry', 'Calculus', 'Botany',
        'Zoology', 'Electromagnetism', 'Organic Chemistry', 'Linear Algebra',
        'Physical Chemistry', 'Quantum Mechanics', 'Genetics', 'Statistics'
    ],
    14 => [ // L.L.B.
        'Jurisprudence', 'Constitutional Law', 'Law of Contracts', 'Criminal Law',
        'Family Law', 'Property Law', 'Company Law', 'Labor Law',
        'Environmental Law', 'Taxation Law', 'Administrative Law', 'Human Rights Law'
    ],
    15 => [ // B.Arch
        'Architectural Design', 'Building Materials and Construction', 'History of Architecture', 'Structural Design',
        'Building Services', 'Town Planning', 'Landscape Architecture', 'Interior Design',
        'Estimation and Costing', 'Professional Practice', 'Sustainable Architecture', 'Urban Design'
    ]
];

foreach ($realisticSubjects as $course_id => $subjects) {
    // 1. Update existing subjects to have realistic names
    $res = $conn->query("SELECT id FROM subjects WHERE course_id = $course_id ORDER BY id ASC");
    $existing = [];
    while ($row = $res->fetch_assoc()) {
        $existing[] = $row['id'];
    }
    
    $index = 0;
    foreach ($existing as $subj_id) {
        if (isset($subjects[$index])) {
            $name = $subjects[$index];
            $code = strtoupper(substr(str_replace(' ', '', $name), 0, 3)) . rand(100, 999);
            $conn->query("UPDATE subjects SET name = '$name', code = '$code' WHERE id = $subj_id");
            $index++;
        }
    }
    
    // 2. Insert the remaining subjects
    while ($index < count($subjects)) {
        $name = $subjects[$index];
        $code = strtoupper(substr(str_replace(' ', '', $name), 0, 3)) . rand(100, 999);
        
        // Assign a random teacher from the same department
        // First get department of course
        $deptRes = $conn->query("SELECT department_id FROM courses WHERE id = $course_id");
        $dept_id = $deptRes->fetch_assoc()['department_id'] ?? rand(1,15);
        
        $teacherRes = $conn->query("SELECT id FROM teachers WHERE department_id = $dept_id ORDER BY RAND() LIMIT 1");
        $teacher_id = $teacherRes->fetch_assoc()['id'] ?? rand(1, 100);
        
        $conn->query("INSERT INTO subjects (name, code, course_id, teacher_id) VALUES ('$name', '$code', $course_id, $teacher_id)");
        $index++;
    }
}

echo "Realistic subjects populated successfully for all courses!\n";
