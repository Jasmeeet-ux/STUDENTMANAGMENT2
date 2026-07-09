<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=courses" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Course</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=courses" class="hover:text-primary">Courses</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium"><?php echo htmlspecialchars($course['code']); ?></span>
    </div>
</div>

<form action="?module=courses&action=update" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-3xl">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($course['id']); ?>">
    
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-book-open text-primary mr-3 text-lg"></i>
            <div>
                <h2 class="text-base font-bold text-slate-800">Course Details</h2>
                <p class="text-xs text-slate-500 mt-0.5">Update the information for this course.</p>
            </div>
        </div>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Course Code <span class="text-danger">*</span></label>
            <input type="text" name="code" value="<?php echo htmlspecialchars($course['code']); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary uppercase">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Course Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Department <span class="text-danger">*</span></label>
            <select name="department_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Department</option>
                <?php foreach($departments as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($course['department_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Credits <span class="text-danger">*</span></label>
            <input type="number" name="credits" value="<?php echo htmlspecialchars($course['credits']); ?>" required min="1" max="10" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="active" <?php echo ($course['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($course['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-between items-center">
        <a href="?module=courses&action=delete&id=<?php echo htmlspecialchars($course['id']); ?>" class="text-danger hover:text-red-800 text-sm font-medium transition-colors" onclick="return confirm('Are you sure you want to delete this course?');">
            <i class="fas fa-trash-alt mr-1"></i> Delete Course
        </a>
        <div class="flex space-x-3">
            <a href="?module=courses" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center">
                <i class="fas fa-check mr-2"></i> Update Course
            </button>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
