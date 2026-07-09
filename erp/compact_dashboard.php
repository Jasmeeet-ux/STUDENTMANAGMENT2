<?php
$f = 'C:/xampp/htdocs/STUDENTLOGIN/erp/views/dashboard/index.php';
$c = file_get_contents($f);

// 1. Grid from 4 back to 6 cols, and reduce gap/margins
$c = str_replace('grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8', 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-4', $c);
$c = str_replace('gap-6 mb-8 items-start', 'gap-4 mb-4 items-start', $c);

// 2. Reduce card paddings and text sizes
$c = str_replace('p-6 hover:', 'p-3 hover:', $c);
$c = str_replace('text-sm font-bold text-slate-500 mb-2', 'text-[11px] font-bold text-slate-500 mb-1', $c);
$c = str_replace('text-3xl font-bold', 'text-xl font-bold', $c);

// 3. Reduce chart heights
$c = str_replace('min-h-[400px]', 'min-h-[250px]', $c);
$c = str_replace('h-48', 'h-32', $c);
$c = str_replace('px-5 py-4', 'px-4 py-2', $c);
$c = str_replace('p-5', 'p-3', $c);

file_put_contents($f, $c);
echo 'Updated dashboard spacing to fit single screen!';
