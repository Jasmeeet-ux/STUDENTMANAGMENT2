<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=examinations" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Exam</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=examinations" class="hover:text-primary">Exams</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium"><?php echo htmlspecialchars($exam['name']); ?></span>
    </div>
</div>

<form action="?module=examinations&action=update" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-3xl">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($exam['id']); ?>">
    
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
        <i class="fas fa-file-alt text-primary mr-3 text-lg"></i>
        <div>
            <h2 class="text-base font-bold text-slate-800">Exam Details</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update the parameters for this exam.</p>
        </div>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Exam Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($exam['name']); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Exam Type <span class="text-danger">*</span></label>
            <select name="exam_type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="Mid Semester" <?php echo $exam['exam_type'] == 'Mid Semester' ? 'selected' : ''; ?>>Mid Semester</option>
                <option value="Final Semester" <?php echo $exam['exam_type'] == 'Final Semester' ? 'selected' : ''; ?>>Final Semester</option>
                <option value="Practical Exam" <?php echo $exam['exam_type'] == 'Practical Exam' ? 'selected' : ''; ?>>Practical Exam</option>
                <option value="Other" <?php echo $exam['exam_type'] == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Class <span class="text-danger">*</span></label>
            <select name="class_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Class</option>
                <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $exam['class_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['class_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Start Date</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($exam['start_date']); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">End Date</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($exam['end_date']); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="Upcoming" <?php echo $exam['status'] == 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                <option value="Ongoing" <?php echo $exam['status'] == 'Ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                <option value="Completed" <?php echo $exam['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-between items-center">
        <a href="?module=examinations&action=delete&id=<?php echo $exam['id']; ?>" class="text-danger hover:text-red-800 text-sm font-medium transition-colors" onclick="return confirm('Are you sure you want to delete this exam?');">
            <i class="fas fa-trash-alt mr-1"></i> Delete
        </a>
        <div class="flex space-x-3">
            <a href="?module=examinations" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center">
                <i class="fas fa-check mr-2"></i> Update Exam
            </button>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
