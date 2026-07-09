<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=assignments" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create Assignment</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=assignments" class="hover:text-primary">Assignments</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium">Create New</span>
    </div>
</div>

<form action="?module=assignments&action=store" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-4xl">
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
        <i class="fas fa-tasks text-primary mr-3 text-lg"></i>
        <div>
            <h2 class="text-base font-bold text-slate-800">Assignment Details</h2>
            <p class="text-xs text-slate-500 mt-0.5">Fill in the assignment information and attach it to a class.</p>
        </div>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" required placeholder="e.g. Chapter 3 Questions" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
            <textarea name="description" rows="4" placeholder="Provide instructions for the students..." class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Class <span class="text-danger">*</span></label>
            <select name="class_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Class</option>
                <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['class_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Subject <span class="text-danger">*</span></label>
            <select name="subject_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Subject</option>
                <?php foreach($subjects as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name'] . ' (' . $s['code'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Teacher <span class="text-danger">*</span></label>
            <select name="teacher_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Teacher</option>
                <?php foreach($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Due Date <span class="text-danger">*</span></label>
            <input type="datetime-local" name="due_date" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Max Marks</label>
            <input type="number" name="max_marks" value="10.00" step="0.01" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="Active">Active</option>
                <option value="Closed">Closed</option>
            </select>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-end space-x-3">
        <a href="?module=assignments" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
        <button type="submit" class="px-5 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center">
            <i class="fas fa-save mr-2"></i> Create Assignment
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
