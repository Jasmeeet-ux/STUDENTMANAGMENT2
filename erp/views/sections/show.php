<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manage Section: <?php echo htmlspecialchars($section['name']); ?></h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=sections" class="hover:text-primary">Sections</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium"><?php echo htmlspecialchars($section['name']); ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Section Details & Subjects -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Section Info -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Overview</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Class:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($section['class_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Course:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($section['course_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Class Teacher:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($section['teacher_name'] ?? 'None'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Room Number:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($section['room_number']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Capacity:</span>
                        <span class="text-slate-800 font-bold"><?php echo htmlspecialchars($section['capacity']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Subjects -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Assigned Subjects</h2>
            </div>
            <div class="p-6">
                <form action="?module=sections&action=assignSubject" method="POST" class="mb-4 space-y-3">
                    <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                    <select name="subject_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 bg-white">
                        <option value="">-- Select Subject --</option>
                        <?php foreach($available_subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="teacher_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 bg-white">
                        <option value="">-- Assign Teacher (Optional) --</option>
                        <?php foreach($teachers as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors">
                        Add Subject
                    </button>
                </form>

                <div class="space-y-3">
                    <?php if (empty($assigned_subjects)): ?>
                        <p class="text-xs text-slate-500 text-center py-2">No subjects assigned.</p>
                    <?php else: ?>
                        <?php foreach ($assigned_subjects as $sub): ?>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded border border-slate-200">
                                <div>
                                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($sub['name']); ?></p>
                                    <p class="text-xs text-slate-500">Teacher: <?php echo htmlspecialchars($sub['teacher_name'] ?? 'Not Assigned'); ?></p>
                                </div>
                                <a href="?module=sections&action=removeSubject&section_id=<?php echo $section['id']; ?>&subject_id=<?php echo $sub['id']; ?>" class="text-danger hover:text-red-800" onclick="return confirm('Remove this subject?');">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Students -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden h-full flex flex-col">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-800">Students (<?php echo count($assigned_students); ?>)</h2>
                
                <form action="?module=sections&action=assignStudent" method="POST" class="flex space-x-2">
                    <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                    <select name="student_id" required class="px-3 py-1.5 border border-slate-300 rounded text-sm focus:ring-2 focus:ring-primary/20 bg-white min-w-[200px]">
                        <option value="">-- Add Student --</option>
                        <?php foreach($available_students as $st): ?>
                            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'] . ' (' . $st['reg_no'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-primary text-white rounded text-sm font-medium hover:bg-blue-800 transition-colors">
                        Add
                    </button>
                </form>
            </div>
            <div class="p-0 flex-1 overflow-y-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white border-b border-bordercolor sticky top-0">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-4">Reg No</th>
                            <th class="p-4">Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bordercolor">
                        <?php if (empty($assigned_students)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-500">
                                    <p class="text-sm font-medium">No students in this section yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assigned_students as $st): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4">
                                        <span class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($st['reg_no']); ?></span>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($st['name']); ?></p>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-sm text-slate-600"><?php echo htmlspecialchars($st['email']); ?></p>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="?module=sections&action=removeStudent&section_id=<?php echo $section['id']; ?>&student_id=<?php echo $st['id']; ?>" class="text-danger hover:text-red-800 text-sm transition-colors" onclick="return confirm('Remove student from section?');">
                                            Remove
                                        </a>
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
