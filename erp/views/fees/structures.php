<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Fee Structures</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=fees" class="hover:text-primary">Fees</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Structures</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Create Structure -->
    <div class="lg:col-span-1">
        <form action="?module=fees&action=createStructure" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Create Fee Structure</h2>
                <p class="text-xs text-slate-500 mt-0.5">Define a fee and auto-generate invoices.</p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Course <span class="text-danger">*</span></label>
                    <select name="course_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 bg-white">
                        <option value="">-- Select Course --</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name'] . ' (' . $c['course_code'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Fee Type / Title <span class="text-danger">*</span></label>
                    <input type="text" name="fee_type" required placeholder="e.g. Tuition Fee Term 1" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Academic Year <span class="text-danger">*</span></label>
                    <input type="text" name="academic_year" required placeholder="e.g. 2026-2027" value="2026-2027" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" name="amount" required step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 font-mono">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors flex justify-center items-center">
                        <i class="fas fa-plus-circle mr-2"></i> Create & Generate Invoices
                    </button>
                    <p class="text-[10px] text-slate-500 text-center mt-2">This will automatically assign the fee to all active students in the selected course.</p>
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Structures List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden h-full flex flex-col">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
                <h2 class="text-base font-bold text-slate-800">Existing Structures</h2>
                <form action="" method="GET" class="flex space-x-2">
                    <input type="hidden" name="module" value="fees">
                    <input type="hidden" name="action" value="structures">
                    <select name="course_id" class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-primary/20" onchange="this.form.submit()">
                        <option value="">All Courses</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['course_id']) && $_GET['course_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white border-b border-bordercolor sticky top-0">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-4">Fee Details</th>
                            <th class="p-4">Course</th>
                            <th class="p-4 text-right">Amount</th>
                            <th class="p-4 text-center">Due Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bordercolor">
                        <?php if (empty($structures)): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-500">
                                    <p class="text-sm font-medium">No fee structures defined.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($structures as $s): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($s['fee_type']); ?></p>
                                        <p class="text-xs text-slate-500">Year: <?php echo htmlspecialchars($s['academic_year']); ?></p>
                                    </td>
                                    <td class="p-4 text-sm font-medium text-slate-700">
                                        <?php echo htmlspecialchars($s['course_name']); ?>
                                    </td>
                                    <td class="p-4 text-right text-sm font-bold text-slate-800">
                                        ₹<?php echo number_format($s['amount'], 2); ?>
                                    </td>
                                    <td class="p-4 text-center text-sm font-medium text-slate-600">
                                        <?php echo date('d M Y', strtotime($s['due_date'])); ?>
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
