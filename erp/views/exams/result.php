<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center no-print">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Student Result</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Result</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <button onclick="window.print()" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-file-pdf mr-2"></i> Export PDF / Print
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-4xl mx-auto print-container">
    <!-- Header -->
    <div class="p-8 border-b border-bordercolor text-center bg-slate-50 print-bg-white">
        <h1 class="text-3xl font-bold text-slate-800 uppercase tracking-wider mb-2">EduERP Institute</h1>
        <h2 class="text-lg font-semibold text-slate-600 mb-1"><?php echo htmlspecialchars($exam['name']); ?></h2>
        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($exam['exam_type']); ?> Result</p>
    </div>
    
    <!-- Student Details -->
    <div class="p-8 border-b border-bordercolor">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-slate-500 font-medium mb-1">Student Name</p>
                <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($student['name']); ?></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-500 font-medium mb-1">Registration No</p>
                <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($student['reg_no']); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Marks Table -->
    <div class="p-8">
        <table class="w-full text-left border-collapse border border-slate-200">
            <thead>
                <tr class="bg-slate-100 print-bg-light">
                    <th class="p-3 border border-slate-200 font-bold text-slate-700">Subject Code</th>
                    <th class="p-3 border border-slate-200 font-bold text-slate-700">Subject Name</th>
                    <th class="p-3 border border-slate-200 font-bold text-slate-700 text-center">Max Marks</th>
                    <th class="p-3 border border-slate-200 font-bold text-slate-700 text-center">Marks Obtained</th>
                    <th class="p-3 border border-slate-200 font-bold text-slate-700 text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_obtained = 0;
                $total_max = 0;
                $all_pass = true;
                
                if (empty($results)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">No results published for this student.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($results as $res): 
                        $max = $res['internal_max_marks'] + $res['external_max_marks'];
                        $obtained = $res['total_marks'];
                        $total_max += $max;
                        $total_obtained += $obtained;
                        
                        if ($res['is_absent'] || $obtained < $res['passing_marks']) {
                            $all_pass = false;
                        }
                    ?>
                        <tr>
                            <td class="p-3 border border-slate-200 font-medium text-slate-700"><?php echo htmlspecialchars($res['subject_code']); ?></td>
                            <td class="p-3 border border-slate-200 font-bold text-slate-800"><?php echo htmlspecialchars($res['subject_name']); ?></td>
                            <td class="p-3 border border-slate-200 text-center font-medium text-slate-600"><?php echo floatval($max); ?></td>
                            <td class="p-3 border border-slate-200 text-center font-bold text-slate-800">
                                <?php if ($res['is_absent']): ?>
                                    <span class="text-danger">ABS</span>
                                <?php else: ?>
                                    <?php echo floatval($obtained); ?>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 border border-slate-200 text-center font-bold <?php echo $res['grade'] == 'F' ? 'text-danger' : 'text-success'; ?>"><?php echo htmlspecialchars($res['grade']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($results)): ?>
            <tfoot>
                <tr class="bg-slate-50 print-bg-light">
                    <td colspan="2" class="p-3 border border-slate-200 font-bold text-right text-slate-800 uppercase">Total</td>
                    <td class="p-3 border border-slate-200 text-center font-bold text-slate-800"><?php echo $total_max; ?></td>
                    <td class="p-3 border border-slate-200 text-center font-bold text-slate-800"><?php echo $total_obtained; ?></td>
                    <td class="p-3 border border-slate-200 text-center font-bold <?php echo $all_pass ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $all_pass ? 'PASS' : 'FAIL'; ?>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
        
        <?php if (!empty($results)): 
            $percentage = $total_max > 0 ? round(($total_obtained / $total_max) * 100, 2) : 0;
            // Simple GPA calculation (4.0 scale mapped from percentage for demo)
            $gpa = 0;
            if ($percentage >= 90) $gpa = 4.0;
            elseif ($percentage >= 80) $gpa = 3.5;
            elseif ($percentage >= 70) $gpa = 3.0;
            elseif ($percentage >= 60) $gpa = 2.5;
            elseif ($percentage >= 50) $gpa = 2.0;
            elseif ($percentage >= 40) $gpa = 1.0;
        ?>
        <div class="mt-8 grid grid-cols-2 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 print-no-bg">
                <p class="text-sm font-semibold text-blue-800 mb-1">Percentage</p>
                <p class="text-2xl font-bold text-blue-900"><?php echo $percentage; ?>%</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-100 print-no-bg">
                <p class="text-sm font-semibold text-purple-800 mb-1">Estimated GPA</p>
                <p class="text-2xl font-bold text-purple-900"><?php echo number_format($gpa, 2); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Signatures -->
    <div class="p-8 mt-12 grid grid-cols-2 gap-4 text-center">
        <div>
            <div class="border-b border-slate-400 w-48 mx-auto mb-2"></div>
            <p class="text-sm font-bold text-slate-700">Class Teacher</p>
        </div>
        <div>
            <div class="border-b border-slate-400 w-48 mx-auto mb-2"></div>
            <p class="text-sm font-bold text-slate-700">Principal / Head</p>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar { display: none !important; }
        .top-header { display: none !important; }
        .print-bg-white { background-color: white !important; }
        .print-bg-light { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
        .print-no-bg { background-color: transparent !important; border-color: #cbd5e1 !important; }
        body { padding: 0 !important; margin: 0 !important; background: white !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        .print-container { border: none !important; box-shadow: none !important; max-width: 100% !important; }
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
