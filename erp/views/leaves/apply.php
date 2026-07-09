<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=leaves" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Apply for Leave</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=leaves" class="hover:text-primary">Leaves</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium">Apply</span>
    </div>
</div>

<form action="?module=leaves&action=store" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-3xl mx-auto">
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
        <i class="fas fa-calendar-plus text-primary mr-3 text-lg"></i>
        <div>
            <h2 class="text-base font-bold text-slate-800">Leave Application Form</h2>
            <p class="text-xs text-slate-500 mt-0.5">Submit a new leave request for a student or teacher.</p>
        </div>
    </div>
    
    <div class="p-6 space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-blue-50 border border-blue-100 rounded-lg">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">User Type <span class="text-danger">*</span></label>
                <select name="user_type" id="user_type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>
            
            <div id="student_select_div">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Select Student <span class="text-danger">*</span></label>
                <select name="applicant_id" id="student_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">-- Select Student --</option>
                    <?php foreach($students as $st): ?>
                        <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['name'] . ' (' . $st['reg_no'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="teacher_select_div" class="hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Select Teacher <span class="text-danger">*</span></label>
                <select name="applicant_id" id="teacher_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white" disabled>
                    <option value="">-- Select Teacher --</option>
                    <?php foreach($teachers as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Leave Type <span class="text-danger">*</span></label>
                <select name="leave_type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="Sick Leave">Sick Leave</option>
                    <option value="Casual Leave">Casual Leave</option>
                    <option value="Emergency Leave">Emergency Leave</option>
                    <option value="Medical Leave">Medical Leave</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Reason for Leave <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4" required placeholder="Please provide detailed reason..." class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-end space-x-3">
        <a href="?module=leaves" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
        <button type="submit" class="px-5 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center">
            <i class="fas fa-paper-plane mr-2"></i> Submit Application
        </button>
    </div>
</form>

<script>
document.getElementById('user_type').addEventListener('change', function() {
    const isStudent = this.value === 'student';
    const sDiv = document.getElementById('student_select_div');
    const sSelect = document.getElementById('student_id');
    const tDiv = document.getElementById('teacher_select_div');
    const tSelect = document.getElementById('teacher_id');
    
    if (isStudent) {
        sDiv.classList.remove('hidden');
        sSelect.disabled = false;
        tDiv.classList.add('hidden');
        tSelect.disabled = true;
    } else {
        sDiv.classList.add('hidden');
        sSelect.disabled = true;
        tDiv.classList.remove('hidden');
        tSelect.disabled = false;
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
