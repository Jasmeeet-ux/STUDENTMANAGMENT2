<?php ob_start(); ?>

<div class="mb-6">
    <div class="flex items-center space-x-3 mb-2">
        <a href="?module=teachers" class="text-slate-400 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Add New Teacher</h1>
    </div>
    <div class="text-sm text-slate-500 flex items-center ml-7">
        <a href="?module=dashboard" class="hover:text-primary">Dashboard</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <a href="?module=teachers" class="hover:text-primary">Teachers</a>
        <i class="fas fa-chevron-right text-[10px] mx-2"></i>
        <span class="text-slate-700 font-medium">Add New</span>
    </div>
</div>

<form action="?module=teachers&action=store" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                <input type="text" name="employee_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Department <span class="text-danger">*</span></label>
                <select name="department_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Department</option>
                    <?php foreach($departments as $d): ?>
                        <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Designation <span class="text-danger">*</span></label>
                <select name="designation_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Designation</option>
                    <?php foreach($designations as $d): ?>
                        <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Employment Type <span class="text-danger">*</span></label>
                <select name="employment_type_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Type</option>
                    <?php foreach($employment_types as $e): ?>
                        <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Date of Joining</label>
                <input type="date" name="joining_date" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Experience (Years)</label>
                <input type="number" name="experience_years" value="0" min="0" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Highest Qualification</label>
                <input type="text" name="qualification" placeholder="e.g. Ph.D. in Computer Science" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
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
                <input type="text" name="name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Phone Number <span class="text-danger">*</span></label>
                <input type="text" name="phone" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Gender</label>
                <select name="gender" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Date of Birth</label>
                <input type="date" name="dob" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Blood Group</label>
                <select name="blood_group" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="">Select Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Permanent Address</label>
                <textarea name="address" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Emergency Contact Name</label>
                <input type="text" name="emergency_contact_name" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Emergency Contact Phone</label>
                <input type="text" name="emergency_contact_phone" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
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
                <input type="text" name="username" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <p class="text-xs text-slate-500 mt-1">Minimum 8 characters</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Account Status</label>
                <select name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="on_leave">On Leave</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex justify-end space-x-3 sticky bottom-4 bg-white/90 backdrop-blur p-4 rounded-xl shadow-lg border border-bordercolor">
        <a href="?module=teachers" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">Cancel</a>
        <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-md shadow-primary/30 transition-all flex items-center">
            <i class="fas fa-save mr-2"></i> Create Teacher Profile
        </button>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
