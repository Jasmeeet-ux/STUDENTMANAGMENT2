<?php
$f = 'C:/xampp/htdocs/STUDENTLOGIN/erp/views/dashboard/index.php';
$c = file_get_contents($f);

// 1. Reduce top margin of dashboard title
$c = str_replace('mb-6 flex justify-between items-center', 'mb-3 flex justify-between items-center', $c);

// 2. Reduce gap and margin of metric cards
$c = str_replace('gap-3 mb-4', 'gap-3 mb-3', $c);
$c = str_replace('mb-4 items-start', 'mb-3 items-start', $c);

// 3. Reduce chart heights and paddings
$c = str_replace('min-h-[250px]', 'min-h-[200px]', $c);
$c = str_replace('h-32', 'h-24', $c);
$c = str_replace('px-4 py-2', 'px-3 py-1.5', $c);
$c = str_replace('p-3', 'p-2', $c);
$c = str_replace('gap-6 mb-8 items-start', 'gap-4 mb-3 items-start', $c); // Just in case it didn't match before

// 4. Shrink the pie chart legend text further
$c = str_replace("font: { size: 10 }", "font: { size: 9 }", $c);
$c = str_replace("font: { size: 11 }", "font: { size: 9 }", $c);

// 5. Increase metric card font slightly so it's readable
$c = str_replace('text-xl font-bold', 'text-2xl font-bold', $c);

file_put_contents($f, $c);
echo 'Made things fit perfectly!';
