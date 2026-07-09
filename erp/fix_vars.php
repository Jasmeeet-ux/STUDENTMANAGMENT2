<?php
$files = [
    'C:/xampp/htdocs/STUDENTLOGIN/erp/views/fees/index.php',
    'C:/xampp/htdocs/STUDENTLOGIN/erp/views/dashboard/index.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Fix the syntax errors caused by replacing ' $' with ' ₹'
        // '₹stats' -> '$stats'
        // We use the hex representation of ₹ to be safe if encoding is weird, but we can just use ₹ directly since the file was written with it.
        $content = preg_replace('/₹([a-zA-Z_]+)/', '$$1', $content);
        
        file_put_contents($file, $content);
        echo "Fixed variables in $file\n";
    }
}
