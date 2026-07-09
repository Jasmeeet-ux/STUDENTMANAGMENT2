<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Leave Management</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Leaves</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=leaves&action=apply" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-plus mr-2"></i> Apply Leave
        </a>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center mr-3">
            <i class="fas fa-list-alt text-slate-500"></i>
        </div>
        <div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Total</p>
            <h3 class="text-xl font-bold text-slate-800"><?php echo $stats['total']; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-10 w-10 rounded-full bg-orange-50 flex items-center justify-center mr-3 relative">
            <i class="fas fa-clock text-orange-500"></i>
            <?php if($stats['pending'] > 0): ?>
                <span class="absolute top-0 right-0 h-3 w-3 rounded-full bg-danger border-2 border-white"></span>
            <?php endif; ?>
        </div>
        <div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Pending</p>
            <h3 class="text-xl font-bold text-slate-800"><?php echo $stats['pending']; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-10 w-10 rounded-full bg-green-50 flex items-center justify-center mr-3">
            <i class="fas fa-check text-success"></i>
        </div>
        <div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Approved</p>
            <h3 class="text-xl font-bold text-slate-800"><?php echo $stats['approved']; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-10 w-10 rounded-full bg-red-50 flex items-center justify-center mr-3">
            <i class="fas fa-times text-danger"></i>
        </div>
        <div>
            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Rejected</p>
            <h3 class="text-xl font-bold text-slate-800"><?php echo $stats['rejected']; ?></h3>
        </div>
    </div>
    <div class="bg-primary text-white p-4 rounded-xl shadow-sm border border-blue-600 flex items-center">
        <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center mr-3">
            <i class="fas fa-calendar-day text-white"></i>
        </div>
        <div>
            <p class="text-[10px] text-blue-200 font-bold uppercase tracking-wider">On Leave Today</p>
            <h3 class="text-xl font-bold text-white"><?php echo $stats['today_leaves']; ?></h3>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end justify-between">
        <input type="hidden" name="module" value="leaves">
        <div class="flex flex-wrap items-end gap-4 flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Name or Type..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">User Type</label>
                <select name="user_type" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[120px]">
                    <option value="">All Users</option>
                    <option value="student" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] == 'student') ? 'selected' : ''; ?>>Students</option>
                    <option value="teacher" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] == 'teacher') ? 'selected' : ''; ?>>Teachers</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
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
                    <th class="p-4">Applicant</th>
                    <th class="p-4">Leave Type</th>
                    <th class="p-4">Duration</th>
                    <th class="p-4">Days</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bordercolor">
                <?php if (empty($leaves)): ?>
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-sign-out-alt text-2xl text-slate-400"></i>
                                </div>
                                <p class="text-base font-semibold text-slate-700">No leave applications found</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaves as $l): 
                        $start = new DateTime($l['start_date']);
                        $end = new DateTime($l['end_date']);
                        $days = $end->diff($start)->format("%a") + 1; // +1 to include both start and end days
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors group <?php echo $l['status'] == 'Pending' ? 'bg-orange-50/30' : ''; ?>">
                            <td class="p-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold mr-3 <?php echo $l['user_type'] == 'teacher' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                        <?php echo substr($l['applicant_name'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($l['applicant_name']); ?></p>
                                        <p class="text-[11px] text-slate-500 font-medium uppercase"><?php echo htmlspecialchars($l['user_type']); ?> &bull; <?php echo htmlspecialchars($l['applicant_identifier']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-slate-700 text-sm"><?php echo htmlspecialchars($l['leave_type']); ?></span>
                            </td>
                            <td class="p-4">
                                <p class="text-sm font-bold text-slate-800"><?php echo date('d M Y', strtotime($l['start_date'])); ?></p>
                                <p class="text-xs text-slate-500">to <?php echo date('d M Y', strtotime($l['end_date'])); ?></p>
                            </td>
                            <td class="p-4 text-sm font-bold text-slate-700">
                                <?php echo $days; ?> Day(s)
                            </td>
                            <td class="p-4 text-center">
                                <?php if($l['status'] == 'Approved'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200"><i class="fas fa-check mr-1"></i> Approved</span>
                                <?php elseif($l['status'] == 'Rejected'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200"><i class="fas fa-times mr-1"></i> Rejected</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200"><i class="fas fa-clock mr-1"></i> Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <a href="?module=leaves&action=show&id=<?php echo $l['id']; ?>" class="bg-white border border-slate-300 text-slate-700 hover:text-primary hover:border-primary px-3 py-1.5 rounded text-xs font-bold transition-colors">
                                    <?php echo $l['status'] == 'Pending' ? 'Review Application' : 'View Details'; ?> <i class="fas fa-arrow-right ml-1"></i>
                                </a>
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
