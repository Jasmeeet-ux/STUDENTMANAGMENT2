<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Leave Details</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=leaves" class="hover:text-primary">Leaves</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium">Application #<?php echo $leave['id']; ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Details -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="p-6 bg-slate-50 border-b border-bordercolor flex justify-between items-center">
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-full flex items-center justify-center text-lg font-bold mr-4 <?php echo $leave['user_type'] == 'teacher' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                        <?php echo substr($leave['applicant_name'], 0, 1); ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($leave['applicant_name']); ?></h2>
                        <p class="text-sm text-slate-500 font-medium uppercase tracking-wider"><?php echo htmlspecialchars($leave['user_type']); ?> &bull; <?php echo htmlspecialchars($leave['applicant_identifier']); ?></p>
                    </div>
                </div>
                <div>
                    <?php if($leave['status'] == 'Approved'): ?>
                        <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-green-100 text-green-800 border border-green-200"><i class="fas fa-check mr-1"></i> Approved</span>
                    <?php elseif($leave['status'] == 'Rejected'): ?>
                        <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-red-100 text-red-800 border border-red-200"><i class="fas fa-times mr-1"></i> Rejected</span>
                    <?php else: ?>
                        <span class="px-3 py-1.5 rounded-full text-sm font-bold bg-orange-100 text-orange-800 border border-orange-200"><i class="fas fa-clock mr-1"></i> Pending Review</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Leave Type</p>
                        <p class="font-bold text-slate-800 text-lg"><?php echo htmlspecialchars($leave['leave_type']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Duration</p>
                        <?php 
                            $start = new DateTime($leave['start_date']);
                            $end = new DateTime($leave['end_date']);
                            $days = $end->diff($start)->format("%a") + 1;
                        ?>
                        <p class="font-bold text-slate-800 text-lg"><?php echo $days; ?> Day(s)</p>
                        <p class="text-sm text-slate-600"><?php echo $start->format('d M Y'); ?> to <?php echo $end->format('d M Y'); ?></p>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Reason for Leave</p>
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 text-slate-700 text-sm whitespace-pre-wrap leading-relaxed">
                        <?php echo htmlspecialchars($leave['reason']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Action / Status -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h2 class="text-base font-bold text-slate-800">Application Action</h2>
            </div>
            
            <div class="p-6">
                <?php if ($leave['status'] == 'Pending'): ?>
                    <form action="?module=leaves&action=process" method="POST" class="space-y-4">
                        <input type="hidden" name="id" value="<?php echo $leave['id']; ?>">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Action <span class="text-danger">*</span></label>
                            <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 bg-white">
                                <option value="Approved">Approve Leave</option>
                                <option value="Rejected">Reject Leave</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Remarks (Optional)</label>
                            <textarea name="admin_remarks" rows="3" placeholder="Provide a reason for rejection or approval notes..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>
                        
                        <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-blue-800 shadow-sm transition-colors mt-2">
                            Process Application
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center p-4">
                        <div class="h-16 w-16 rounded-full mx-auto flex items-center justify-center mb-3 <?php echo $leave['status'] == 'Approved' ? 'bg-green-100 text-green-500' : 'bg-red-100 text-red-500'; ?>">
                            <i class="fas <?php echo $leave['status'] == 'Approved' ? 'fa-check' : 'fa-times'; ?> text-3xl"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg mb-1">Application <?php echo $leave['status']; ?></h3>
                        <p class="text-sm text-slate-500 mb-4">Processed on <?php echo date('d M Y', strtotime($leave['updated_at'])); ?></p>
                        
                        <?php if(!empty($leave['admin_remarks'])): ?>
                            <div class="text-left bg-slate-50 p-3 rounded border border-slate-200">
                                <p class="text-xs font-bold text-slate-500 uppercase mb-1">Admin Remarks</p>
                                <p class="text-sm text-slate-700"><?php echo htmlspecialchars($leave['admin_remarks']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
