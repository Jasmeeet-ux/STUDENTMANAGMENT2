<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=sections" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Section</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=sections" class="hover:text-primary">Sections</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium"><?php echo htmlspecialchars($section['name']); ?></span>
    </div>
</div>

<form action="?module=sections&action=update" method="POST" class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden max-w-3xl">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($section['id']); ?>">
    
    <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
        <i class="fas fa-layer-group text-primary mr-3 text-lg"></i>
        <div>
            <h2 class="text-base font-bold text-slate-800">Section Information</h2>
            <p class="text-xs text-slate-500 mt-0.5">Update the details for this section.</p>
        </div>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Section Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($section['name']); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Class <span class="text-danger">*</span></label>
            <select name="class_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Class</option>
                <?php foreach($classes as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($section['class_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['class_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Class Teacher (Optional)</label>
            <select name="teacher_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="">Select Class Teacher</option>
                <?php foreach($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo ($section['teacher_id'] == $t['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Room Number</label>
            <input type="text" name="room_number" value="<?php echo htmlspecialchars($section['room_number']); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Student Capacity</label>
            <input type="number" name="capacity" value="<?php echo htmlspecialchars($section['capacity']); ?>" min="1" max="200" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                <option value="active" <?php echo ($section['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($section['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>

    <div class="px-6 py-4 bg-slate-50 border-t border-bordercolor flex justify-between items-center">
        <a href="?module=sections&action=delete&id=<?php echo htmlspecialchars($section['id']); ?>" class="text-danger hover:text-red-800 text-sm font-medium transition-colors" onclick="return confirm('Are you sure you want to delete this section?');">
            <i class="fas fa-trash-alt mr-1"></i> Delete Section
        </a>
        <div class="flex space-x-3">
            <a href="?module=sections" class="px-5 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-100 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center">
                <i class="fas fa-check mr-2"></i> Update Section
            </button>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
