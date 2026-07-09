<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Invoice Details</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=fees" class="hover:text-primary">Fees</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">INV-<?php echo str_pad($invoice['id'], 5, '0', STR_PAD_LEFT); ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Left Column: Summary & Payment -->
    <div class="xl:col-span-1 space-y-6">
        <!-- Invoice Summary Card -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="p-6 bg-slate-800 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-bold"><?php echo htmlspecialchars($invoice['student_name']); ?></h2>
                            <p class="text-slate-300 text-sm opacity-90"><?php echo htmlspecialchars($invoice['reg_no']); ?></p>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                <?php echo $invoice['status'] == 'Paid' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 
                                      ($invoice['status'] == 'Partial' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/30' : 
                                      'bg-orange-500/20 text-orange-300 border border-orange-500/30'); ?>">
                                <?php echo $invoice['status']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-6 border-t border-slate-700 pt-4">
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Total Due</p>
                        <h3 class="text-3xl font-bold text-white mb-2">
                            $<?php echo number_format(($invoice['total_amount'] + $invoice['fine_amount']) - $invoice['paid_amount'], 2); ?>
                        </h3>
                    </div>
                </div>
                <!-- Decorative pattern -->
                <div class="absolute -bottom-10 -right-10 opacity-10">
                    <i class="fas fa-file-invoice-dollar text-9xl"></i>
                </div>
            </div>
            
            <div class="p-6 space-y-3 text-sm">
                <div class="flex justify-between pb-3 border-b border-slate-100">
                    <span class="text-slate-500">Base Amount</span>
                    <span class="font-bold text-slate-700">$<?php echo number_format($invoice['total_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between pb-3 border-b border-slate-100">
                    <span class="text-slate-500">Fine Applied</span>
                    <span class="font-bold text-danger">$<?php echo number_format($invoice['fine_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between pb-3 border-b border-slate-100">
                    <span class="text-slate-500">Paid Amount</span>
                    <span class="font-bold text-success">$<?php echo number_format($invoice['paid_amount'], 2); ?></span>
                </div>
                <div class="flex justify-between pt-2">
                    <span class="font-semibold text-slate-700">Fee Type</span>
                    <span class="text-slate-600 text-right"><?php echo htmlspecialchars($invoice['fee_type']); ?></span>
                </div>
                <div class="flex justify-between pb-2 border-b border-slate-100">
                    <span class="font-semibold text-slate-700">Course</span>
                    <span class="text-slate-600 text-right"><?php echo htmlspecialchars($invoice['course_name']); ?></span>
                </div>
            </div>
        </div>

        <?php if ($invoice['status'] != 'Paid'): ?>
        <!-- Record Payment Form -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
                <i class="fas fa-credit-card text-success mr-2 text-lg"></i>
                <h2 class="text-base font-bold text-slate-800">Record Payment</h2>
            </div>
            <div class="p-6">
                <form action="?module=fees&action=pay" method="POST" class="space-y-4">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" required step="0.01" max="<?php echo ($invoice['total_amount'] + $invoice['fine_amount']) - $invoice['paid_amount']; ?>" placeholder="0.00" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 font-mono">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 bg-white">
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Reference No (Optional)</label>
                        <input type="text" name="reference_no" placeholder="Txn ID or Receipt No" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks</label>
                        <textarea name="remarks" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20"></textarea>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-success text-white rounded-lg text-sm font-bold hover:bg-green-700 shadow-sm transition-colors mt-2">
                        Process Payment
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Add Fine Form -->
        <div class="bg-white rounded-xl shadow-sm border border-danger/30 overflow-hidden">
            <div class="px-6 py-3 border-b border-danger/20 bg-red-50 flex items-center">
                <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                <h2 class="text-sm font-bold text-danger">Apply Late Fine</h2>
            </div>
            <div class="p-4">
                <form action="?module=fees&action=addFine" method="POST" class="flex gap-2">
                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                    <input type="number" name="amount" required step="0.01" min="0.01" placeholder="Amount" class="flex-1 px-3 py-1.5 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-danger focus:border-danger font-mono">
                    <button type="submit" class="px-3 py-1.5 bg-danger text-white rounded text-sm font-bold hover:bg-red-800 transition-colors">
                        Add Fine
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Payment History -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden h-full flex flex-col">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Payment History</h2>
                <p class="text-xs text-slate-500 mt-0.5">Log of all transactions for this invoice.</p>
            </div>
            
            <div class="p-0 flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white border-b border-bordercolor sticky top-0">
                        <tr class="text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="p-4">Date</th>
                            <th class="p-4">Method</th>
                            <th class="p-4">Ref No</th>
                            <th class="p-4 text-right">Amount</th>
                            <th class="p-4 text-center">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bordercolor">
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-500">
                                    <div class="h-12 w-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-history text-slate-400"></i>
                                    </div>
                                    <p class="text-sm font-medium">No payments recorded yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-4">
                                        <p class="font-bold text-slate-800 text-sm"><?php echo date('d M Y', strtotime($p['payment_date'])); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo date('h:i A', strtotime($p['payment_date'])); ?></p>
                                    </td>
                                    <td class="p-4 text-sm font-medium text-slate-700">
                                        <?php echo htmlspecialchars($p['payment_method']); ?>
                                    </td>
                                    <td class="p-4 text-sm text-slate-500">
                                        <?php echo $p['reference_no'] ? htmlspecialchars($p['reference_no']) : '-'; ?>
                                    </td>
                                    <td class="p-4 text-right font-bold text-success text-sm">
                                        $<?php echo number_format($p['amount'], 2); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <a href="?module=fees&action=receipt&id=<?php echo $p['id']; ?>" class="text-primary hover:text-blue-800 text-sm font-medium transition-colors" title="Print Receipt">
                                            <i class="fas fa-print"></i> Print
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
