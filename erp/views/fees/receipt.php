<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center no-print">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Payment Receipt</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=fees" class="hover:text-primary">Fees</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=fees&action=invoice&id=<?php echo $receipt['invoice_id']; ?>" class="hover:text-primary">INV-<?php echo str_pad($receipt['invoice_id'], 5, '0', STR_PAD_LEFT); ?></a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Receipt</span>
        </div>
    </div>
    <div class="flex space-x-3">
        <button onclick="window.print()" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-bordercolor max-w-3xl mx-auto print-container">
    <!-- Header -->
    <div class="p-8 border-b border-bordercolor flex justify-between items-start print-bg-white">
        <div>
            <h1 class="text-3xl font-bold text-primary uppercase tracking-wider mb-1">EduERP</h1>
            <p class="text-sm text-slate-500">123 Education Lane, Learning City</p>
            <p class="text-sm text-slate-500">contact@eduerp.com | +1 234 567 8900</p>
        </div>
        <div class="text-right">
            <h2 class="text-3xl font-bold text-slate-300 uppercase tracking-widest mb-1">Receipt</h2>
            <p class="text-sm font-bold text-slate-700">RCPT-<?php echo str_pad($receipt['id'], 6, '0', STR_PAD_LEFT); ?></p>
            <p class="text-sm text-slate-500">Date: <?php echo date('d M Y', strtotime($receipt['payment_date'])); ?></p>
        </div>
    </div>
    
    <!-- Billing Info -->
    <div class="p-8 border-b border-bordercolor grid grid-cols-2 gap-8 print-bg-white">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Billed To</p>
            <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($receipt['student_name']); ?></p>
            <p class="text-sm text-slate-600 mb-1">Reg No: <span class="font-bold"><?php echo htmlspecialchars($receipt['reg_no']); ?></span></p>
            <p class="text-sm text-slate-600">Course: <?php echo htmlspecialchars($receipt['course_name']); ?></p>
        </div>
        <div class="text-right">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Payment Details</p>
            <p class="text-sm text-slate-600 mb-1">Method: <span class="font-bold text-slate-800"><?php echo htmlspecialchars($receipt['payment_method']); ?></span></p>
            <?php if($receipt['reference_no']): ?>
                <p class="text-sm text-slate-600">Ref No: <span class="font-bold text-slate-800"><?php echo htmlspecialchars($receipt['reference_no']); ?></span></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Item Table -->
    <div class="p-8 print-bg-white">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-slate-200">
                    <th class="py-3 px-2 font-bold text-slate-700 uppercase text-xs tracking-wider">Description</th>
                    <th class="py-3 px-2 font-bold text-slate-700 uppercase text-xs tracking-wider text-right w-32">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr>
                    <td class="py-4 px-2">
                        <p class="font-bold text-slate-800"><?php echo htmlspecialchars($receipt['fee_type']); ?></p>
                        <p class="text-sm text-slate-500">Academic Year: <?php echo htmlspecialchars($receipt['academic_year']); ?></p>
                        <?php if($receipt['remarks']): ?>
                            <p class="text-xs text-slate-400 mt-1 italic">Note: <?php echo htmlspecialchars($receipt['remarks']); ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="py-4 px-2 text-right font-bold text-slate-800 text-lg">
                        $<?php echo number_format($receipt['amount'], 2); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="mt-12 flex justify-end">
            <div class="w-64">
                <div class="flex justify-between py-2 border-b-2 border-slate-800">
                    <span class="font-bold text-slate-700 uppercase tracking-wider">Total Paid</span>
                    <span class="font-bold text-success text-2xl">$<?php echo number_format($receipt['amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="bg-slate-50 p-6 text-center border-t border-bordercolor print-bg-light rounded-b-xl">
        <p class="text-sm font-bold text-slate-600 mb-1">Thank you for your payment!</p>
        <p class="text-xs text-slate-400">This is a computer-generated receipt and does not require a physical signature.</p>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .sidebar { display: none !important; }
        .top-header { display: none !important; }
        .print-bg-white { background-color: white !important; }
        .print-bg-light { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
        body { padding: 0 !important; margin: 0 !important; background: white !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        .print-container { border: none !important; box-shadow: none !important; max-width: 100% !important; margin: 0 !important; }
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
