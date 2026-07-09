<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Assignment Submissions</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=assignments" class="hover:text-primary">Assignments</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Submissions</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=assignments&action=edit&id=<?php echo $assignment['id']; ?>" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <i class="fas fa-pen mr-2"></i> Edit Assignment
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Assignment Info -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Assignment Details</h2>
            </div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-primary mb-2"><?php echo htmlspecialchars($assignment['title']); ?></h3>
                <p class="text-sm text-slate-600 mb-4 whitespace-pre-wrap"><?php echo htmlspecialchars($assignment['description']); ?></p>
                
                <div class="space-y-3 text-sm border-t border-slate-100 pt-4">
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Subject:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($assignment['subject_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Class:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($assignment['class_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Teacher:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($assignment['teacher_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Max Marks:</span>
                        <span class="text-slate-800 font-bold"><?php echo floatval($assignment['max_marks']); ?></span>
                    </div>
                    <?php 
                        $due = new DateTime($assignment['due_date']);
                        $now = new DateTime();
                        $is_past = $now > $due;
                    ?>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Due Date:</span>
                        <span class="font-bold <?php echo $is_past ? 'text-danger' : 'text-slate-800'; ?>"><?php echo $due->format('d M Y, h:i A'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Status:</span>
                        <span class="font-bold <?php echo $assignment['status'] == 'Active' ? 'text-green-600' : 'text-slate-500'; ?>"><?php echo htmlspecialchars($assignment['status']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Student Submissions & Grading -->
    <div class="lg:col-span-2">
        <form action="?module=assignments&action=saveGrades" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden h-full flex flex-col">
            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
            
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Student Submissions</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Grade submissions and provide feedback.</p>
                </div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-800 shadow-sm transition-colors">
                    Save Grades
                </button>
            </div>
            
            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white border-b border-bordercolor sticky top-0">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-4 w-10 text-center">S.No</th>
                            <th class="p-4">Student</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center w-24">Marks</th>
                            <th class="p-4">Feedback</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bordercolor">
                        <?php if (empty($submissions)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    <p class="text-sm font-medium">No students found in this class.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $sn = 1; foreach ($submissions as $sub): 
                                $sid = $sub['student_id'];
                                $marks = $sub['marks_obtained'] !== null ? floatval($sub['marks_obtained']) : '';
                                $feedback = $sub['feedback'] ?? '';
                                $status = $sub['sub_status'] ?? 'Pending';
                                
                                $statusClass = 'bg-slate-100 text-slate-800 border-slate-200'; // Pending
                                if ($status == 'Submitted') $statusClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                if ($status == 'Late') $statusClass = 'bg-orange-100 text-orange-800 border-orange-200';
                                if ($status == 'Graded') $statusClass = 'bg-green-100 text-green-800 border-green-200';
                            ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4 text-center text-slate-500 font-medium"><?php echo $sn++; ?></td>
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($sub['student_name']); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($sub['reg_no']); ?> | <?php echo htmlspecialchars($sub['section_name']); ?></p>
                                    </td>
                                    <td class="p-4 text-center">
                                        <select name="grades[<?php echo $sid; ?>][status]" class="px-2 py-1 rounded text-xs font-bold border focus:outline-none <?php echo $statusClass; ?>">
                                            <option value="Pending" <?php echo $status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Submitted" <?php echo $status == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                                            <option value="Late" <?php echo $status == 'Late' ? 'selected' : ''; ?>>Late</option>
                                            <option value="Graded" <?php echo $status == 'Graded' ? 'selected' : ''; ?>>Graded</option>
                                        </select>
                                    </td>
                                    <td class="p-4">
                                        <input type="number" name="grades[<?php echo $sid; ?>][marks_obtained]" value="<?php echo $marks; ?>" step="0.01" min="0" max="<?php echo $assignment['max_marks']; ?>" class="w-full px-2 py-1 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-primary focus:border-primary text-center">
                                    </td>
                                    <td class="p-4">
                                        <input type="text" name="grades[<?php echo $sid; ?>][feedback]" value="<?php echo htmlspecialchars($feedback); ?>" placeholder="Add feedback..." class="w-full px-3 py-1 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-primary focus:border-primary">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
