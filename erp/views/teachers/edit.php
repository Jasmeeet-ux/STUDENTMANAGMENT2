<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=teachers" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Teacher</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=teachers" class="hover:text-primary">Teachers</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium">Edit Profile</span>
    </div>
</div>

<form action="?module=teachers&action=update" method="POST" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($teacher['id'] ?? ''); ?>">
    
    <!-- Section 1: Professional Information -->
    <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
        <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
            <i class="fas fa-briefcase text-primary mr-3 text-lg"></i>
            <div>
                <h2 class="text-base font-bold text-slate-800">Professional Information</h2>
                <p class="text-xs text-slate-500 mt-0.5">Employment and academic details</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Employee ID <span class="text-danger">*</span></label>
                <input type="text" name="employee_id" value="<?php echo htmlspecialchars($teacher['employee_id'] ?? ''); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Department <span class="text-danger">*</span></label>
                <select name="department_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Department</option>
                    <?php foreach($departments as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo ($teacher['department_id'] == $d['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Designation <span class="text-danger">*</span></label>
                <select name="designation_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Designation</option>
                    <?php foreach($designations as $d): ?>
                        <option value="<?php echo $d['id']; ?>" <?php echo ($teacher['designation_id'] == $d['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Employment Type <span class="text-danger">*</span></label>
                <select name="employment_type_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Type</option>
                    <?php foreach($employment_types as $e): ?>
                        <option value="<?php echo $e['id']; ?>" <?php echo ($teacher['employment_type_id'] == $e['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($e['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Date of Joining</label>
                <input type="date" name="joining_date" value="<?php echo htmlspecialchars($teacher['joining_date'] ?? ''); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Experience (Years)</label>
                <input type="number" name="experience_years" value="<?php echo htmlspecialchars($teacher['experience_years'] ?? '0'); ?>" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Highest Qualification</label>
                <input type="text" name="qualification" value="<?php echo htmlspecialchars($teacher['qualification'] ?? ''); ?>" placeholder="e.g. Ph.D. in Computer Science" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
        </div>
    </div>

    <!-- Section 2: Personal Information -->
    <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
        <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
            <i class="fas fa-user text-primary mr-3 text-lg"></i>
            <div>
                <h2 class="text-base font-bold text-slate-800">Personal Information</h2>
                <p class="text-xs text-slate-500 mt-0.5">Contact and demographic details</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name'] ?? ''); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email'] ?? ''); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Phone Number <span class="text-danger">*</span></label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Gender</label>
                <select name="gender" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo ($teacher['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($teacher['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($teacher['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Date of Birth</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($teacher['dob'] ?? ''); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Blood Group</label>
                <select name="blood_group" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Group</option>
                    <?php 
                    $bgs = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
                    foreach($bgs as $bg): ?>
                        <option value="<?php echo $bg; ?>" <?php echo ($teacher['blood_group'] == $bg) ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Permanent Address</label>
                <textarea name="address" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"><?php echo htmlspecialchars($teacher['address'] ?? ''); ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Emergency Contact Name</label>
                <input type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($teacher['emergency_contact_name'] ?? ''); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Emergency Contact Phone</label>
                <input type="text" name="emergency_contact_phone" value="<?php echo htmlspecialchars($teacher['emergency_contact_phone'] ?? ''); ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
        </div>
    </div>

    <!-- Section 3: Account Credentials -->
    <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden">
        <div class="px-6 py-4 border-b border-bordercolor bg-slate-50 flex items-center">
            <i class="fas fa-lock text-primary mr-3 text-lg"></i>
            <div>
                <h2 class="text-base font-bold text-slate-800">Account Credentials</h2>
                <p class="text-xs text-slate-500 mt-0.5">System access information</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($teacher['username'] ?? ''); ?>" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 cursor-not-allowed" readonly>
                <p class="text-xs text-slate-400 mt-1">Username cannot be changed.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">New Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Account Status</label>
                <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="active" <?php echo ($teacher['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($teacher['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="on_leave" <?php echo ($teacher['status'] == 'on_leave') ? 'selected' : ''; ?>>On Leave</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center sticky bottom-4 bg-white/90 backdrop-blur p-4 rounded-xl shadow-lg border border-bordercolor z-10">
        <a href="?module=teachers&action=delete&id=<?php echo htmlspecialchars($teacher['id'] ?? ''); ?>" class="text-danger hover:text-red-800 text-sm font-medium transition-colors" onclick="return confirm('Are you sure you want to delete this teacher?');">
            <i class="fas fa-trash-alt mr-1"></i> Soft Delete
        </a>
        <div class="flex space-x-3">
            <a href="?module=teachers&action=show&id=<?php echo $teacher['id']; ?>" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md shadow-primary/30 transition-all flex items-center">
                <i class="fas fa-save mr-2"></i> Update Teacher
            </button>
        </div>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
