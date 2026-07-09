<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Daily Attendance</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Mark Attendance</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=attendance&action=report" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-chart-bar mr-2"></i> Monthly Report
        </a>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center mr-4">
            <i class="fas fa-clipboard-check text-primary text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Marked Today</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['total_marked_today'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center mr-4">
            <i class="fas fa-user-check text-success text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Present Today</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['present_today'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-red-50 flex items-center justify-center mr-4">
            <i class="fas fa-user-times text-danger text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Absent Today</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['absent_today'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center mr-4">
            <i class="fas fa-percentage text-purple-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Attendance % (Today)</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['attendance_percentage'] ?? 0; ?>%</h3>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end" id="filterForm">
        <input type="hidden" name="module" value="attendance">
        
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Date <span class="text-danger">*</span></label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" max="<?php echo date('Y-m-d'); ?>" required class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white">
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
                <option value="">Class Attendance (Overall)</option>
                <?php foreach($subjects as $sub): ?>
                    <option value="<?php echo $sub['id']; ?>" <?php echo ($subject_id == $sub['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sub['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="ml-auto flex space-x-2">
            <button type="submit" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                Load Students
            </button>
        </div>
    </form>
</div>

<!-- Attendance Marking Table -->
<?php if ($section_id): ?>
    <form method="POST" action="?module=attendance&action=store">
        <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">
        <input type="hidden" name="subject_id" value="<?php echo htmlspecialchars($subject_id); ?>">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
        
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-800">
                    Marking Attendance for <?php echo date('d M Y', strtotime($date)); ?>
                    <?php if($subject_id) echo " (Subject-wise)"; else echo " (Class-wise)"; ?>
                </h2>
                <div class="flex space-x-2">
                    <button type="button" onclick="markAll('Present')" class="px-3 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded border border-green-200 hover:bg-green-100">Mark All Present</button>
                    <button type="submit" class="px-4 py-1.5 bg-primary text-white text-sm font-bold rounded-lg hover:bg-blue-800 shadow-sm transition-colors">
                        <i class="fas fa-save mr-1"></i> Save Attendance
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white border-b border-bordercolor">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-4 w-16 text-center">S.No</th>
                            <th class="p-4">Reg No</th>
                            <th class="p-4">Student Name</th>
                            <th class="p-4 w-96 text-center">Status</th>
                            <th class="p-4">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bordercolor">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-500">
                                    <p class="text-base font-semibold text-slate-700">No students found in this section.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $sn = 1; foreach ($students as $st): 
                                $st_id = $st['id'];
                                $current_status = $attendance_record[$st_id]['status'] ?? 'Present'; // Default Present
                                $current_remarks = $attendance_record[$st_id]['remarks'] ?? '';
                            ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-center text-slate-500 font-medium"><?php echo $sn++; ?></td>
                                    <td class="p-4 font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($st['reg_no']); ?></td>
                                    <td class="p-4 font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($st['name']); ?></td>
                                    <td class="p-4 text-center">
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <label class="cursor-pointer border border-slate-300 rounded-l-lg px-3 py-1.5 text-sm font-medium transition-colors hover:bg-slate-50 <?php echo $current_status == 'Present' ? 'bg-green-100 text-green-800 border-green-500 z-10' : 'text-slate-600 bg-white'; ?>">
                                                <input type="radio" name="attendance[<?php echo $st_id; ?>][status]" value="Present" class="sr-only status-radio" <?php echo $current_status == 'Present' ? 'checked' : ''; ?> onchange="updateRadioStyles(this)">
                                                Present
                                            </label>
                                            <label class="cursor-pointer border-t border-b border-r border-slate-300 px-3 py-1.5 text-sm font-medium transition-colors hover:bg-slate-50 <?php echo $current_status == 'Absent' ? 'bg-red-100 text-red-800 border-red-500 z-10' : 'text-slate-600 bg-white'; ?>">
                                                <input type="radio" name="attendance[<?php echo $st_id; ?>][status]" value="Absent" class="sr-only status-radio" <?php echo $current_status == 'Absent' ? 'checked' : ''; ?> onchange="updateRadioStyles(this)">
                                                Absent
                                            </label>
                                            <label class="cursor-pointer border-t border-b border-r border-slate-300 px-3 py-1.5 text-sm font-medium transition-colors hover:bg-slate-50 <?php echo $current_status == 'Late' ? 'bg-orange-100 text-orange-800 border-orange-500 z-10' : 'text-slate-600 bg-white'; ?>">
                                                <input type="radio" name="attendance[<?php echo $st_id; ?>][status]" value="Late" class="sr-only status-radio" <?php echo $current_status == 'Late' ? 'checked' : ''; ?> onchange="updateRadioStyles(this)">
                                                Late
                                            </label>
                                            <label class="cursor-pointer border-t border-b border-r border-slate-300 rounded-r-lg px-3 py-1.5 text-sm font-medium transition-colors hover:bg-slate-50 <?php echo $current_status == 'Leave' ? 'bg-purple-100 text-purple-800 border-purple-500 z-10' : 'text-slate-600 bg-white'; ?>">
                                                <input type="radio" name="attendance[<?php echo $st_id; ?>][status]" value="Leave" class="sr-only status-radio" <?php echo $current_status == 'Leave' ? 'checked' : ''; ?> onchange="updateRadioStyles(this)">
                                                Leave
                                            </label>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <input type="text" name="attendance[<?php echo $st_id; ?>][remarks]" value="<?php echo htmlspecialchars($current_remarks); ?>" placeholder="Remarks..." class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-primary focus:border-primary">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($students)): ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-end">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg text-sm font-bold shadow-md hover:bg-blue-800 transition-all flex items-center">
                    <i class="fas fa-save mr-2"></i> Save Attendance
                </button>
            </div>
            <?php endif; ?>
        </div>
    </form>
    
    <script>
    function updateRadioStyles(radio) {
        const group = radio.closest('.inline-flex');
        const labels = group.querySelectorAll('label');
        
        labels.forEach(label => {
            const input = label.querySelector('input');
            const val = input.value;
            
            // Reset styles
            label.className = `cursor-pointer border border-slate-300 px-3 py-1.5 text-sm font-medium transition-colors hover:bg-slate-50 text-slate-600 bg-white`;
            
            // Add border radius back depending on position
            if (val === 'Present') label.classList.add('rounded-l-lg');
            if (val === 'Leave') label.classList.add('rounded-r-lg');
            if (val !== 'Present') label.classList.replace('border', 'border-t');
            if (val !== 'Present') label.classList.add('border-b', 'border-r');
            
            if (input.checked) {
                label.classList.remove('text-slate-600', 'bg-white', 'border-slate-300');
                if (val === 'Present') label.classList.add('bg-green-100', 'text-green-800', 'border-green-500', 'z-10');
                if (val === 'Absent') label.classList.add('bg-red-100', 'text-red-800', 'border-red-500', 'z-10');
                if (val === 'Late') label.classList.add('bg-orange-100', 'text-orange-800', 'border-orange-500', 'z-10');
                if (val === 'Leave') label.classList.add('bg-purple-100', 'text-purple-800', 'border-purple-500', 'z-10');
            }
        });
    }
    
    function markAll(status) {
        document.querySelectorAll(`input[value="${status}"]`).forEach(radio => {
            radio.checked = true;
            updateRadioStyles(radio);
        });
    }
    </script>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-bordercolor p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
            <i class="fas fa-hand-pointer text-2xl text-slate-400"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800 mb-1">Select a Section</h3>
        <p class="text-sm text-slate-500">Please select a date and section from the filters above to load the student list and mark attendance.</p>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
