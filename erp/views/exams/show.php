<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Exam Details: <?php echo htmlspecialchars($exam['name']); ?></h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=examinations" class="hover:text-primary">Exams</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Details</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=examinations&action=edit&id=<?php echo $exam['id']; ?>" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <i class="fas fa-pen mr-2"></i> Edit Exam
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Info & Add Subject -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Exam Info -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Overview</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Class:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($exam['class_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Course:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($exam['course_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Type:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($exam['exam_type']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Status:</span>
                        <span class="font-bold <?php echo $exam['status'] == 'Upcoming' ? 'text-purple-600' : ($exam['status'] == 'Ongoing' ? 'text-orange-500' : 'text-green-600'); ?>"><?php echo htmlspecialchars($exam['status']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Subject -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Add Subject to Exam</h2>
            </div>
            <div class="p-6">
                <form action="?module=examinations&action=addSubject" method="POST" class="space-y-4">
                    <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 bg-white">
                            <option value="">-- Select Subject --</option>
                            <?php foreach($available_subjects as $sub): ?>
                                <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name'] . ' (' . $sub['code'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Exam Date</label>
                        <input type="date" name="exam_date" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Internal Max</label>
                            <input type="number" name="internal_max_marks" value="0" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">External Max</label>
                            <input type="number" name="external_max_marks" value="100" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Passing Marks</label>
                        <input type="number" name="passing_marks" value="40" step="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors">
                        Add Subject
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Exam Subjects -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden h-full flex flex-col">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Exam Subjects & Marks Entry</h2>
                <p class="text-xs text-slate-500 mt-0.5">Manage subjects and enter marks for students.</p>
            </div>
            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white border-b border-bordercolor sticky top-0">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-4">Subject</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 text-center">Int. Max</th>
                            <th class="p-4 text-center">Ext. Max</th>
                            <th class="p-4 text-center">Pass</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bordercolor">
                        <?php if (empty($exam_subjects)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    <p class="text-sm font-medium">No subjects added to this exam yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($exam_subjects as $es): ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($es['subject_name']); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($es['subject_code']); ?></p>
                                    </td>
                                    <td class="p-4 text-sm text-slate-700">
                                        <?php echo $es['exam_date'] ? date('d M Y', strtotime($es['exam_date'])) : '-'; ?>
                                    </td>
                                    <td class="p-4 text-center text-sm font-medium text-slate-600"><?php echo floatval($es['internal_max_marks']); ?></td>
                                    <td class="p-4 text-center text-sm font-medium text-slate-600"><?php echo floatval($es['external_max_marks']); ?></td>
                                    <td class="p-4 text-center text-sm font-medium text-slate-600"><?php echo floatval($es['passing_marks']); ?></td>
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="?module=examinations&action=marks&exam_subject_id=<?php echo $es['id']; ?>" class="px-3 py-1 bg-green-50 text-green-700 border border-green-200 rounded text-xs font-bold hover:bg-green-100 transition-colors" title="Enter Marks">
                                                Enter Marks
                                            </a>
                                            <a href="?module=examinations&action=removeSubject&id=<?php echo $es['id']; ?>&exam_id=<?php echo $exam['id']; ?>" class="text-danger hover:text-red-800 text-sm transition-colors" onclick="return confirm('Remove subject from exam?');" title="Remove">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
