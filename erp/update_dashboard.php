<?php
$f = 'C:/xampp/htdocs/STUDENTLOGIN/erp/views/dashboard/index.php';
$c = file_get_contents($f);
$c = str_replace('p-4 hover:', 'p-6 hover:', $c);
$c = str_replace('text-xs font-bold text-slate-500 mb-1', 'text-sm font-bold text-slate-500 mb-2', $c);
$c = str_replace('text-2xl font-bold', 'text-3xl font-bold', $c);
file_put_contents($f, $c);
echo 'Updated dashboard classes!';
