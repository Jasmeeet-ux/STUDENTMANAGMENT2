<?php
$directory = 'C:/xampp/htdocs/STUDENTLOGIN/erp/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$files = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);
        
        $pattern = '/<button type="submit" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">(\s*)Apply Filters(\s*)<\/button>/s';
        
        $replacement = '<button type="submit" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">$1Apply Filters$2</button>';
        
        $newContent = preg_replace($pattern, $replacement, $content, -1, $count);
        
        if ($count > 0) {
            file_put_contents($path, $newContent);
            echo "Updated: $path\n";
        }
    }
}
echo "Done.";
