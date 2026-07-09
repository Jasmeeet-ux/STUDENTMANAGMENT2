<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teacher Management</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Teachers</span>
        </div>
    </div>
    <div class="flex space-x-3">

        <a href="?module=teachers&action=create" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-plus mr-2"></i> Add Teacher
        </a>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center mr-4">
            <i class="fas fa-users text-primary text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Teachers</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['total'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center mr-4">
            <i class="fas fa-user-check text-success text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Active</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['active'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-yellow-50 flex items-center justify-center mr-4">
            <i class="fas fa-plane-departure text-warning text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">On Leave</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['on_leave'] ?? 0; ?></h3>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end justify-between">
        <input type="hidden" name="module" value="teachers">
        <div class="flex flex-wrap items-end gap-4 flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Name, ID, Email..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Department</label>
                <select name="department_id" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[150px]">
                    <option value="">All Departments</option>
                    <?php foreach($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_GET['department_id']) && $_GET['department_id'] == $dept['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="on_leave" <?php echo (isset($_GET['status']) && $_GET['status'] == 'on_leave') ? 'selected' : ''; ?>>On Leave</option>
                </select>
            </div>
            <button type="submit" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                Apply Filters
            </button>
            <?php if (!empty($_GET['search']) || !empty($_GET['department_id']) || !empty($_GET['status'])): ?>
                <a href="?module=teachers" class="px-4 py-2 text-danger hover:underline text-sm font-medium">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
    <div class="px-4 py-3 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <span class="text-sm font-medium text-slate-700">Bulk Actions:</span>
            <select class="text-sm border-slate-300 rounded focus:ring-primary focus:border-primary bg-white py-1 pl-2 pr-8">
                <option value="">Select Action...</option>
                <option value="activate">Mark Active</option>
                <option value="deactivate">Mark Inactive</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="px-3 py-1 bg-white border border-slate-300 text-slate-700 text-xs font-medium rounded hover:bg-slate-50">Apply</button>
        </div>
        <div>
            <button class="text-slate-500 hover:text-primary text-sm font-medium"><i class="fas fa-columns mr-1"></i> Columns</button>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead class="sticky top-0 bg-white">
                <tr class="border-b border-bordercolor text-slate-600 text-xs uppercase tracking-wider font-semibold">
                    <th class="p-4 w-10">
                        <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary">
                    </th>
                    <th class="p-4 cursor-pointer hover:text-primary">Teacher <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                    <th class="p-4 cursor-pointer hover:text-primary">Contact <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                    <th class="p-4 cursor-pointer hover:text-primary">Department <i class="fas fa-sort ml-1 text-slate-400"></i></th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bordercolor">
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-user-slash text-2xl text-slate-400"></i>
                                </div>
                                <p class="text-base font-semibold text-slate-700">No teachers found</p>
                                <p class="text-sm text-slate-500 mt-1">Try adjusting your filters or add a new teacher.</p>
                                <a href="?module=teachers&action=create" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium shadow-sm hover:bg-blue-800">Add New Teacher</a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="p-4">
                                <input type="checkbox" class="rounded border-slate-300 text-primary focus:ring-primary">
                            </td>
                            <td class="p-4">
                                <div class="flex items-center cursor-pointer" onclick="window.location.href='?module=teachers&action=show&id=<?php echo $teacher['id']; ?>'">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 text-primary flex items-center justify-center font-bold text-sm mr-3">
                                        <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800 text-sm hover:text-primary transition-colors"><?php echo htmlspecialchars($teacher['name']); ?></p>
                                        <p class="text-xs text-slate-500 font-medium">EMP: <?php echo htmlspecialchars($teacher['employee_id'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-slate-600 mb-1"><i class="fas fa-envelope text-slate-400 w-4"></i> <?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></div>
                                <div class="text-sm text-slate-600"><i class="fas fa-phone-alt text-slate-400 w-4"></i> <?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm font-medium text-slate-800"><?php echo htmlspecialchars($teacher['department_name'] ?? 'Unassigned'); ?></div>
                                <div class="text-xs text-slate-500"><?php echo htmlspecialchars($teacher['designation_name'] ?? '-'); ?></div>
                            </td>
                            <td class="p-4 text-sm">
                                <?php if($teacher['status'] == 'active'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Active</span>
                                <?php elseif($teacher['status'] == 'on_leave'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">On Leave</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="?module=teachers&action=show&id=<?php echo $teacher['id']; ?>" class="text-slate-400 hover:text-primary transition-colors" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?module=teachers&action=edit&id=<?php echo $teacher['id']; ?>" class="text-slate-400 hover:text-accent transition-colors" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="?module=teachers&action=delete&id=<?php echo $teacher['id']; ?>" class="text-slate-400 hover:text-danger transition-colors" title="Delete" onclick="return confirm('Are you sure you want to delete this teacher?');">
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
    
    <!-- Pagination -->
    <?php if (!empty($teachers)): ?>
    <div class="p-4 border-t border-bordercolor flex items-center justify-between bg-slate-50">
        <span class="text-sm text-slate-600">Showing <span class="font-semibold text-slate-800"><?php echo count($teachers); ?></span> results</span>
        <div class="flex space-x-1">
            <button class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-400 cursor-not-allowed text-sm"><i class="fas fa-chevron-left text-xs"></i></button>
            <button class="px-3 py-1 border border-primary rounded bg-primary text-white text-sm font-medium shadow-sm">1</button>
            <button class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 text-sm font-medium transition-colors">2</button>
            <button class="px-3 py-1 border border-slate-200 rounded bg-white text-slate-600 hover:bg-slate-50 text-sm"><i class="fas fa-chevron-right text-xs"></i></button>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
