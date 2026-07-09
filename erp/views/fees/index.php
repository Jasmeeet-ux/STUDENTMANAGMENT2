<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Fee Management</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Student Fees</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=fees&action=structures" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-layer-group mr-2"></i> Fee Structures
        </a>
    </div>
</div>

<!-- Dashboard Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center mr-4">
            <i class="fas fa-file-invoice-dollar text-primary text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Invoices</p>
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['total_invoices'] ?? 0; ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-green-50 flex items-center justify-center mr-4">
            <i class="fas fa-money-bill-wave text-success text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Collected</p>
            <h3 class="text-xl font-bold text-slate-800">₹<?php echo number_format($stats['total_collected'] ?? 0, 2); ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center mr-4">
            <i class="fas fa-exclamation-circle text-orange-500 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Pending</p>
            <h3 class="text-xl font-bold text-slate-800">₹<?php echo number_format($stats['total_pending'] ?? 0, 2); ?></h3>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor flex items-center">
        <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center mr-4">
            <i class="fas fa-gavel text-purple-600 text-xl"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Fines Total</p>
            <h3 class="text-xl font-bold text-slate-800">₹<?php echo number_format($stats['fine_collected'] ?? 0, 2); ?></h3>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-bordercolor mb-6">
    <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-end justify-between">
        <input type="hidden" name="module" value="fees">
        <div class="flex flex-wrap items-end gap-4 flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Search Student</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Name or Reg No..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg text-sm px-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary bg-white min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Partial" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Partial') ? 'selected' : ''; ?>>Partial</option>
                    <option value="Paid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
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
                    <th class="p-4">Student</th>
                    <th class="p-4">Fee Details</th>
                    <th class="p-4 text-right">Total Due</th>
                    <th class="p-4 text-right">Paid</th>
                    <th class="p-4 text-right">Balance</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bordercolor">
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-file-invoice text-2xl text-slate-400"></i>
                                </div>
                                <p class="text-base font-semibold text-slate-700">No invoices found</p>
                                <p class="text-sm text-slate-500 mt-1">Create Fee Structures to auto-generate invoices.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): 
                        $total = $inv['total_amount'] + $inv['fine_amount'];
                        $paid = $inv['paid_amount'];
                        $balance = $total - $paid;
                    ?>
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="p-4">
                                <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($inv['student_name']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($inv['reg_no']); ?></p>
                            </td>
                            <td class="p-4">
                                <p class="font-semibold text-slate-700 text-sm"><?php echo htmlspecialchars($inv['fee_type']); ?></p>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($inv['course_name']); ?> | Due: <?php echo date('d M Y', strtotime($inv['due_date'])); ?></p>
                            </td>
                            <td class="p-4 text-right font-bold text-slate-800 text-sm">
                                ₹<?php echo number_format($total, 2); ?>
                                <?php if($inv['fine_amount'] > 0): ?>
                                    <br><span class="text-xs text-danger font-normal">+ ₹<?php echo number_format($inv['fine_amount'], 2); ?> fine</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right font-bold text-success text-sm">
                                ₹<?php echo number_format($paid, 2); ?>
                            </td>
                            <td class="p-4 text-right font-bold text-orange-600 text-sm">
                                ₹<?php echo number_format($balance, 2); ?>
                            </td>
                            <td class="p-4 text-center">
                                <?php if($inv['status'] == 'Paid'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">Paid</span>
                                <?php elseif($inv['status'] == 'Partial'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Partial</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <a href="?module=fees&action=invoice&id=<?php echo $inv['id']; ?>" class="bg-white border border-slate-300 text-slate-700 hover:text-primary hover:border-primary px-3 py-1.5 rounded text-xs font-bold transition-colors">
                                    Manage <i class="fas fa-arrow-right ml-1"></i>
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
