<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Department Management</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Departments</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=departments&action=create" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-plus mr-2"></i> Add Department
        </a>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center mr-4">
            <i class="fas fa-building text-primary text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Departments</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['total'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center mr-4">
            <i class="fas fa-check-circle text-success text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Active</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['active'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center mr-4">
            <i class="fas fa-book-open text-purple-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Courses</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['courses'] ?? 0; ?></h3>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end justify-between">
        <input type="hidden" name="module" value="departments">
        <div class="flex flex-wrap items-end gap-4 flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Department Name..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
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
                    <th class="p-4">Department Name</th>
                    <th class="p-4 text-center">Courses</th>
                    <th class="p-4 text-center">Teachers</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bordercolor">
                <?php if (empty($departments)): ?>
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-building text-2xl text-slate-400"></i>
                                </div>
                                <p class="text-base font-semibold text-slate-700">No departments found</p>
                                <a href="?module=departments&action=create" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-800">Add New Department</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($departments as $dept): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="p-4"><input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary"></td>
                            <td class="p-4">
                                <span class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($dept['name']); ?></span>
                                <?php if(!empty($dept['description'])): ?>
                                    <div class="text-xs text-slate-500 w-48 truncate" title="<?php echo htmlspecialchars($dept['description']); ?>"><?php echo htmlspecialchars($dept['description']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-bold text-primary"><?php echo $dept['course_count']; ?></span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-bold text-slate-600"><?php echo $dept['teacher_count']; ?></span>
                            </td>
                            <td class="p-4 text-sm">
                                <?php if($dept['status'] == 'active'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Active</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="?module=departments&action=edit&id=<?php echo $dept['id']; ?>" class="text-slate-400 hover:text-accent transition-colors" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="?module=departments&action=delete&id=<?php echo $dept['id']; ?>" class="text-slate-400 hover:text-danger transition-colors" title="Delete" onclick="return confirm('Are you sure you want to delete this department?');">
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
