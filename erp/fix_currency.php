<?php
$files = [
    'C:/xampp/htdocs/STUDENTLOGIN/erp/views/fees/index.php',
    'C:/xampp/htdocs/STUDENTLOGIN/erp/views/dashboard/index.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Fix the syntax errors caused by replacing ' $' with ' ₹'
        // 'as ₹s' -> 'as $s'
        $content = preg_replace('/as ₹([a-zA-Z_]+)/', 'as $$1', $content);
        // '=> ₹act' -> '=> $act'
        $content = preg_replace('/=> ₹([a-zA-Z_]+)/', '=> $$1', $content);
        
        file_put_contents($file, $content);
        echo "Fixed syntax in $file\n";
    }
}
