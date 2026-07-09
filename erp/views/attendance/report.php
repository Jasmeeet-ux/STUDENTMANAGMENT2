<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Monthly Attendance Report</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=attendance" class="hover:text-primary">Attendance</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Monthly Report</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <button onclick="window.print()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-print mr-2"></i> Print Report
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6 no-print">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end" id="filterForm">
        <input type="hidden" name="module" value="attendance">
        <input type="hidden" name="action" value="report">
        
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Month</label>
            <select name="month" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                <?php 
                for ($m=1; $m<=12; $m++) {
                    $m_str = str_pad($m, 2, "0", STR_PAD_LEFT);
                    $m_name = date("F", mktime(0, 0, 0, $m, 10));
                    $selected = ($month == $m_str) ? 'selected' : '';
                    echo "<option value='$m_str' $selected>$m_name</option>";
                }
                ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Year</label>
            <select name="year" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
                <?php 
                $current_y = date('Y');
                for ($y = $current_y - 2; $y <= $current_y + 1; $y++) {
                    $selected = ($year == $y) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Section <span class="text-danger">*</span></label>
            <select name="section_id" required class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[200px]" onchange="document.getElementById('filterForm').submit();">
                <option value="">-- Select Section --</option>
                <?php foreach($sections as $sec): ?>
                    <option value="<?php echo $sec['id']; ?>" <?php echo ($section_id == $sec['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sec['course_name'] . ' > ' . $sec['class_name'] . ' > ' . $sec['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($section_id): ?>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Subject (Optional)</label>
            <select name="subject_id" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[150px]" onchange="document.getElementById('filterForm').submit();">
                <option value="">Overall Class Attendance</option>
                <?php foreach($subjects as $sub): ?>
                    <option value="<?php echo $sub['id']; ?>" <?php echo ($subject_id == $sub['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sub['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="ml-auto">
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors">
                Generate Report
            </button>
        </div>
    </form>
</div>

<!-- Report Table -->
<?php if ($section_id): ?>
    <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
        <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 print-bg-white">
            <h2 class="text-base font-bold text-slate-800">
                Attendance Report for <?php echo date("F Y", mktime(0, 0, 0, (int)$month, 10, (int)$year)); ?>
                <?php if($subject_id) echo " (Subject-wise)"; else echo " (Class-wise)"; ?>
            </h2>
        </div>
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left border-collapse border border-slate-200 text-xs">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="p-2 border border-slate-200 font-bold text-slate-700 min-w-[150px]">Student Name</th>
                        <th class="p-2 border border-slate-200 font-bold text-slate-700 text-center">P</th>
                        <th class="p-2 border border-slate-200 font-bold text-slate-700 text-center">A</th>
                        <th class="p-2 border border-slate-200 font-bold text-slate-700 text-center">%</th>
                        <?php for($d = 1; $d <= $days_in_month; $d++): ?>
                            <th class="p-1 border border-slate-200 font-bold text-slate-700 text-center w-6"><?php echo $d; ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($report_data)): ?>
                        <tr>
                            <td colspan="<?php echo $days_in_month + 4; ?>" class="p-8 text-center text-slate-500">
                                No attendance records found for this period.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($report_data as $student_id => $data): 
                            $total = array_sum($data['stats']);
                            $percentage = $total > 0 ? round(($data['stats']['Present'] / $total) * 100) : 0;
                        ?>
                            <tr class="hover:bg-slate-50">
                                <td class="p-2 border border-slate-200 font-medium text-slate-800">
                                    <?php echo htmlspecialchars($data['name']); ?>
                                    <div class="text-[10px] text-slate-500"><?php echo htmlspecialchars($data['reg_no']); ?></div>
                                </td>
                                <td class="p-2 border border-slate-200 text-center font-bold text-green-600"><?php echo $data['stats']['Present']; ?></td>
                                <td class="p-2 border border-slate-200 text-center font-bold text-red-600"><?php echo $data['stats']['Absent']; ?></td>
                                <td class="p-2 border border-slate-200 text-center font-bold <?php echo $percentage < 75 ? 'text-danger' : 'text-primary'; ?>"><?php echo $percentage; ?>%</td>
                                
                                <?php for($d = 1; $d <= $days_in_month; $d++): 
                                    $date_str = sprintf("%04d-%02d-%02d", $year, $month, $d);
                                    $status = $data['attendance'][$date_str] ?? '-';
                                    
                                    $color_class = 'text-slate-300';
                                    $text = '-';
                                    if ($status == 'Present') { $color_class = 'text-green-600 font-bold bg-green-50'; $text = 'P'; }
                                    if ($status == 'Absent') { $color_class = 'text-red-600 font-bold bg-red-50'; $text = 'A'; }
                                    if ($status == 'Late') { $color_class = 'text-orange-500 font-bold bg-orange-50'; $text = 'L'; }
                                    if ($status == 'Leave') { $color_class = 'text-purple-600 font-bold bg-purple-50'; $text = 'Lv'; }
                                ?>
                                    <td class="p-1 border border-slate-200 text-center <?php echo $color_class; ?>"><?php echo $text; ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            .top-header { display: none !important; }
            .print-bg-white { background-color: white !important; }
            body { padding: 0 !important; margin: 0 !important; background: white !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            table { font-size: 10pt !important; }
        }
    </style>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-bordercolor p-12 text-center no-print">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
            <i class="fas fa-file-invoice text-2xl text-slate-400"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-1">Generate Monthly Report</h3>
        <p class="text-sm text-slate-500">Select a section and month from the filters above to view the attendance report.</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
