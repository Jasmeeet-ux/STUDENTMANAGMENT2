<?php
$files = [
    'C:/xampp/htdocs/STUDENTLOGIN/erp/views/fees/index.php',
    'C:/xampp/htdocs/STUDENTLOGIN/erp/views/dashboard/index.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // We only want to replace $ when it's used as a currency symbol in HTML, e.g. ">$<", " +$<", "title=\"$<", " $<?php", ">$"
        $content = preg_replace('/(\>)\$/', '$1₹', $content);
        $content = preg_replace('/(\s)\$/', '$1₹', $content);
        $content = preg_replace('/(\+\$)/', '+₹', $content);
        $content = preg_replace('/title="\$</', 'title="₹<', $content);
        $content = preg_replace('/(\<br\>\<span[^\>]*\>)\+ \$/', '$1+ ₹', $content);
        
        // Let's also fix the <li> issue in dashboard/index.php that just happened.
        if (strpos($file, 'dashboard') !== false) {
            $content = str_replace('<div class="flex justify-between items-center p-3 border border-bordercolor rounded-lg hover:bg-slate-50 transition-colors">', '<li class="py-2 flex justify-between items-center">', $content);
            $content = str_replace("</div>\n                    <?php endforeach;", "</li>\n                    <?php endforeach;", $content);
        }
        
        file_put_contents($file, $content);
        echo "Updated currency in $file\n";
    }
}
