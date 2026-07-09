<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=subjects" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Add New Subject</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=subjects" class="hover:text-primary">Subjects</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium">Add New</span>
    </div>
</div>

<form action="?module=subjects&action=store" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-3xl">
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
        <i class="fas fa-book text-primary mr-3 text-lg"></i>
        <div>
            <h2 class="text-base font-bold text-slate-800">Subject Information</h2>
            <p class="text-xs text-slate-500 mt-0.5">Define a subject and assign it to a course and teacher.</p>
        </div>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Subject Code <span class="text-danger">*</span></label>
            <input type="text" name="code" required placeholder="e.g. PHY101" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary uppercase">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Subject Name <span class="text-danger">*</span></label>
            <input type="text" name="name" required placeholder="e.g. Engineering Physics" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Course <span class="text-danger">*</span></label>
            <select name="course_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Course</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['code'] . ' - ' . $c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Assigned Teacher (Optional)</label>
            <select name="teacher_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Teacher</option>
                <?php foreach($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Credits</label>
            <input type="number" name="credits" value="3" min="1" max="10" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-end space-x-3">
        <a href="?module=subjects" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
        <button type="submit" class="px-5 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center">
            <i class="fas fa-save mr-2"></i> Save Subject
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
