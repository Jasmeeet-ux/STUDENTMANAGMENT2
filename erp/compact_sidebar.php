<?php
$f = 'C:/xampp/htdocs/STUDENTLOGIN/erp/views/layouts/app.php';
$c = file_get_contents($f);

$c = str_replace('px-6 py-3 text-base font-semibold', 'px-6 py-2.5 text-sm font-semibold', $c);
$c = str_replace('p-6 animate-fade-in-up', 'p-4 animate-fade-in-up', $c); // reduce main padding
$c = str_replace('py-4', 'py-2', $c); // reduce sidebar padding

file_put_contents($f, $c);
echo 'Updated layout spacing!';
