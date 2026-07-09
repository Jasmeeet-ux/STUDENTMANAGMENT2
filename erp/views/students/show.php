<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Student Profile</h1>
        <div class="text-sm text-slate-500 mt-1 flex items-center">
            <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <a href="?module=students" class="hover:text-primary">Students</a>
            <i class="fas fa-chevron-right text-[10px] mx-2"></i>
            <span class="text-slate-700 font-medium"><?php echo htmlspecialchars($student['name']); ?></span>
        </div>
    </div>
    <div class="flex space-x-3">
        <a href="?module=students" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Profile Card -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="p-6 text-center border-b border-bordercolor">
                <div class="h-24 w-24 mx-auto rounded-full bg-blue-100 text-primary flex items-center justify-center font-bold text-3xl mb-4">
                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                </div>
                <h2 class="text-xl font-bold text-slate-800"><?php echo htmlspecialchars($student['name']); ?></h2>
                <p class="text-slate-500 font-medium mt-1">Student • <?php echo htmlspecialchars($student['reg_no']); ?></p>
            </div>
            
            <div class="p-6">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Contact Information</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-slate-400 mt-1 w-6 text-center"></i>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Email Address</p>
                            <p class="text-sm text-slate-800"><?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Academic Placement</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-slate-500 font-medium mb-1">Class</p>
                        <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($student['class_name'] ?? 'Not Assigned'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium mb-1">Section</p>
                        <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($student['section_name'] ?? 'Not Assigned'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="px-6 py-4 border-b border-bordercolor bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Recent Activity</h3>
            </div>
            <div class="p-6 text-center text-slate-500">
                <i class="fas fa-history text-3xl text-slate-300 mb-3 block"></i>
                <p>No recent activity recorded for this student.</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
