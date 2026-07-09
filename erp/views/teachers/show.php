<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=teachers" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teacher Profile</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=teachers" class="hover:text-primary">Teachers</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium"><?php echo htmlspecialchars($teacher['name']); ?></span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Left Sidebar Profile -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
            <div class="p-6 text-center border-b border-bordercolor">
                <div class="relative inline-block">
                    <?php if(!empty($teacher['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($teacher['profile_photo']); ?>" class="w-24 h-24 rounded-full border-4 border-white shadow-md mx-auto object-cover">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full bg-indigo-100 text-primary border-4 border-white shadow-md mx-auto flex items-center justify-center font-bold text-3xl">
                            <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span class="absolute bottom-1 right-1 h-4 w-4 rounded-full border-2 border-white <?php echo $teacher['status'] == 'active' ? 'bg-success' : ($teacher['status'] == 'on_leave' ? 'bg-warning' : 'bg-danger'); ?>"></span>
                </div>
                <h2 class="mt-4 text-xl font-bold text-slate-800"><?php echo htmlspecialchars($teacher['name']); ?></h2>
                <p class="text-sm font-medium text-primary mt-1"><?php echo htmlspecialchars($teacher['designation_name'] ?? 'Designation Not Set'); ?></p>
                <p class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($teacher['department_name'] ?? 'Department Not Set'); ?></p>
                
                <div class="mt-6 flex justify-center space-x-2">
                    <a href="?module=teachers&action=edit&id=<?php echo $teacher['id']; ?>" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-sm font-medium transition-colors">
                        Edit Profile
                    </a>
                </div>
            </div>
            <div class="p-4 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Employee ID</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo htmlspecialchars($teacher['employee_id'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Phone</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Joined Date</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo !empty($teacher['joining_date']) ? date('M d, Y', strtotime($teacher['joining_date'])) : 'N/A'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Content Tabs -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden flex flex-col h-full">
            
            <!-- Tabs Navigation -->
            <div class="border-b border-bordercolor overflow-x-auto custom-scrollbar">
                <ul class="flex space-x-6 px-6" id="profileTabs">
                    <li>
                        <button class="py-4 text-sm font-medium text-primary border-b-2 border-primary tab-btn" data-target="overview">Overview</button>
                    </li>
                    <li>
                        <button class="py-4 text-sm font-medium text-slate-500 hover:text-slate-700 border-b-2 border-transparent tab-btn" data-target="academic">Academic & Load</button>
                    </li>
                </ul>
            </div>

            <!-- Tab Contents -->
            <div class="p-6 flex-1">
                <!-- Overview Tab -->
                <div id="overview" class="tab-content block">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Personal Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm text-slate-500">Gender</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo htmlspecialchars($teacher['gender'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Date of Birth</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo !empty($teacher['dob']) ? date('M d, Y', strtotime($teacher['dob'])) : 'N/A'; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Blood Group</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo htmlspecialchars($teacher['blood_group'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Employment Type</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo htmlspecialchars($teacher['employment_type_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-slate-500">Permanent Address</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo nl2br(htmlspecialchars($teacher['address'] ?? 'N/A')); ?></p>
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-t border-bordercolor pt-6">Emergency Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-slate-500">Contact Name</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo htmlspecialchars($teacher['emergency_contact_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Contact Phone</p>
                            <p class="font-medium text-slate-800 mt-1"><?php echo htmlspecialchars($teacher['emergency_contact_phone'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Academic Tab -->
                <div id="academic" class="tab-content hidden">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Assigned Subjects</h3>
                    <?php if(empty($academicLoad)): ?>
                        <div class="border border-dashed border-slate-300 rounded-xl p-8 flex flex-col items-center justify-center text-center bg-slate-50/50">
                            <i class="fas fa-book-reader text-3xl text-slate-400 mb-2"></i>
                            <p class="text-sm font-semibold text-slate-700">No subjects assigned</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <?php foreach($academicLoad as $subj): ?>
                                <div class="p-4 border border-bordercolor rounded-lg bg-slate-50">
                                    <p class="font-bold text-slate-800"><?php echo htmlspecialchars($subj['subject_name']); ?> <span class="text-xs text-slate-500 font-normal">(<?php echo htmlspecialchars($subj['subject_code']); ?>)</span></p>
                                    <p class="text-sm text-slate-600 mt-1"><?php echo htmlspecialchars($subj['course_name']); ?> (<?php echo htmlspecialchars($subj['course_code']); ?>)</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="text-lg font-bold text-slate-800 mb-4 border-t border-bordercolor pt-6">Assigned Classes</h3>
                    <?php if(empty($classes)): ?>
                        <p class="text-sm text-slate-500">No classes assigned.</p>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach($classes as $c): ?>
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm border border-blue-200"><?php echo htmlspecialchars($c['name']); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Simple Tab Switcher
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active classes
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('text-primary', 'border-primary');
                b.classList.add('text-slate-500', 'border-transparent');
            });
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            
            // Add active classes
            btn.classList.remove('text-slate-500', 'border-transparent');
            btn.classList.add('text-primary', 'border-primary');
            document.getElementById(btn.dataset.target).classList.remove('hidden');
        });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
