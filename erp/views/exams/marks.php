<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Enter Marks</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=examinations" class="hover:text-primary">Exams</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=examinations&action=show&id=<?php echo $exam_subject['exam_id']; ?>" class="hover:text-primary"><?php echo htmlspecialchars($exam_subject['exam_name']); ?></a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Marks</span>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
        <i class="fas fa-book text-primary mr-3 text-lg"></i>
        <div>
            <h2 class="text-base font-bold text-slate-800"><?php echo htmlspecialchars($exam_subject['subject_name'] . ' (' . $exam_subject['subject_code'] . ')'); ?></h2>
            <p class="text-xs text-slate-500 mt-0.5">Max Internal: <?php echo floatval($exam_subject['internal_max_marks']); ?> | Max External: <?php echo floatval($exam_subject['external_max_marks']); ?> | Passing: <?php echo floatval($exam_subject['passing_marks']); ?></p>
        </div>
    </div>
    
    <form action="?module=examinations&action=saveMarks" method="POST">
        <input type="hidden" name="exam_subject_id" value="<?php echo $exam_subject['id']; ?>">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-white border-b border-bordercolor">
                    <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="p-4 w-12 text-center">S.No</th>
                        <th class="p-4">Reg No</th>
                        <th class="p-4">Student Name</th>
                        <th class="p-4">Section</th>
                        <th class="p-4 text-center w-32">Internal Marks</th>
                        <th class="p-4 text-center w-32">External Marks</th>
                        <th class="p-4 text-center w-24">Absent</th>
                        <th class="p-4 text-center">Result Preview</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bordercolor">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-500">
                                <p class="text-sm font-medium">No students enrolled in this class's sections.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $sn = 1; foreach ($students as $st): 
                            $sid = $st['id'];
                            $m = $existing_marks[$sid] ?? null;
                            $int_val = $m !== null ? floatval($m['internal_marks']) : '';
                            $ext_val = $m !== null ? floatval($m['external_marks']) : '';
                            $is_absent = $m !== null && $m['is_absent'] ? 'checked' : '';
                            $total = $m !== null ? floatval($m['total_marks']) : 0;
                            $grade = $m !== null ? $m['grade'] : '-';
                            $pass = $total >= $exam_subject['passing_marks'] ? true : false;
                        ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 text-center text-slate-500 font-medium"><?php echo $sn++; ?></td>
                                <td class="p-4 font-bold text-slate-700 text-sm"><?php echo htmlspecialchars($st['reg_no']); ?></td>
                                <td class="p-4 font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($st['name']); ?></td>
                                <td class="p-4 text-sm text-slate-600"><?php echo htmlspecialchars($st['section_name']); ?></td>
                                <td class="p-4">
                                    <input type="number" name="marks[<?php echo $sid; ?>][internal_marks]" value="<?php echo $int_val; ?>" step="0.01" min="0" max="<?php echo $exam_subject['internal_max_marks']; ?>" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-primary focus:border-primary text-center">
                                </td>
                                <td class="p-4">
                                    <input type="number" name="marks[<?php echo $sid; ?>][external_marks]" value="<?php echo $ext_val; ?>" step="0.01" min="0" max="<?php echo $exam_subject['external_max_marks']; ?>" class="w-full px-2 py-1.5 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-primary focus:border-primary text-center">
                                </td>
                                <td class="p-4 text-center">
                                    <input type="checkbox" name="marks[<?php echo $sid; ?>][is_absent]" value="1" <?php echo $is_absent; ?> class="rounded border-slate-300 text-danger focus:ring-danger w-5 h-5">
                                </td>
                                <td class="p-4 text-center text-sm font-bold">
                                    <?php if ($m !== null): ?>
                                        <?php if ($m['is_absent']): ?>
                                            <span class="text-danger">ABSENT</span>
                                        <?php else: ?>
                                            <span class="<?php echo $pass ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $total; ?> (<?php echo $grade; ?>)
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($students)): ?>
        <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-end space-x-3">
            <a href="?module=examinations&action=show&id=<?php echo $exam_subject['exam_id']; ?>" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg text-sm font-bold shadow-md hover:bg-blue-800 transition-all flex items-center">
                <i class="fas fa-save mr-2"></i> Save Marks
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
