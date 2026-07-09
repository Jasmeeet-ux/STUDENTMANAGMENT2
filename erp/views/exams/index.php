<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Examination Management</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Exams</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=examinations&action=create" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-plus mr-2"></i> Create Exam
        </a>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center mr-4">
            <i class="fas fa-file-alt text-primary text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Exams</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['total'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center mr-4">
            <i class="fas fa-clock text-purple-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Upcoming</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['upcoming'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center mr-4">
            <i class="fas fa-spinner text-orange-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Ongoing</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['ongoing'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center mr-4">
            <i class="fas fa-check-circle text-success text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Completed</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['completed'] ?? 0; ?></h3>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end justify-between">
        <input type="hidden" name="module" value="examinations">
        <div class="flex flex-wrap items-end gap-4 flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Exam Name..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Class</label>
                <select name="class_id" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[150px]">
                    <option value="">All Classes</option>
                    <?php foreach($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['class_id']) && $_GET['class_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="Upcoming" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="Ongoing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="Completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <button type="submit" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                Apply Filters
            </button>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-bordercolor">
                <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                    <th class="p-4 w-10"><input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary"></th>
                    <th class="p-4">Exam Name</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Class & Course</th>
                    <th class="p-4">Dates</th>
                    <th class="p-4 text-center">Subjects</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bordercolor">
                <?php if (empty($exams)): ?>
                    <tr>
                        <td colspan="8" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-file-alt text-2xl text-slate-400"></i>
                                </div>
                                <p class="text-base font-semibold text-slate-700">No exams found</p>
                                <a href="?module=examinations&action=create" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-800">Create Exam</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($exams as $ex): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="p-4"><input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary"></td>
                            <td class="p-4">
                                <a href="?module=examinations&action=show&id=<?php echo $ex['id']; ?>" class="font-bold text-primary hover:underline text-sm"><?php echo htmlspecialchars($ex['name']); ?></a>
                            </td>
                            <td class="p-4">
                                <span class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($ex['exam_type']); ?></span>
                            </td>
                            <td class="p-4">
                                <p class="font-semibold text-slate-800 text-sm"><?php echo htmlspecialchars($ex['class_name']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($ex['course_name']); ?></p>
                            </td>
                            <td class="p-4">
                                <p class="text-sm text-slate-700"><?php echo $ex['start_date'] ? date('d M Y', strtotime($ex['start_date'])) : 'TBD'; ?></p>
                                <p class="text-xs text-slate-500">to <?php echo $ex['end_date'] ? date('d M Y', strtotime($ex['end_date'])) : 'TBD'; ?></p>
                            </td>
                            <td class="p-4 text-center">
                                <div class="inline-flex items-center justify-center px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-bold border border-blue-100">
                                    <?php echo $ex['subject_count']; ?>
                                </div>
                            </td>
                            <td class="p-4 text-sm">
                                <?php if($ex['status'] == 'Upcoming'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">Upcoming</span>
                                <?php elseif($ex['status'] == 'Ongoing'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">Ongoing</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Completed</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="?module=examinations&action=show&id=<?php echo $ex['id']; ?>" class="text-slate-400 hover:text-primary transition-colors" title="Manage Subjects & Marks">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                    <a href="?module=examinations&action=edit&id=<?php echo $ex['id']; ?>" class="text-slate-400 hover:text-accent transition-colors" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="?module=examinations&action=delete&id=<?php echo $ex['id']; ?>" class="text-slate-400 hover:text-danger transition-colors" title="Delete" onclick="return confirm('Are you sure you want to delete this exam?');">
                                        <i class="fas fa-trash"></i>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
