<?php ob_start(); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-3 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Enterprise Overview</h1>
        <p class="text-sm text-slate-500 mt-1">Real-time metrics and system analytics.</p>
    </div>
    <div class="flex space-x-3">
        <!-- Removed Export Report button -->
    </div>
</div>

<!-- TOP SUMMARY CARDS (12 METRICS) -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2 mb-3">
    <!-- Row 1 -->
    <a href="?module=students" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-blue-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Students</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['total_students']); ?></h3>
            <i class="fas fa-user-graduate text-blue-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=teachers" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-indigo-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Teachers</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['total_teachers']); ?></h3>
            <i class="fas fa-chalkboard-teacher text-indigo-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=departments" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-purple-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Departments</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['total_departments']); ?></h3>
            <i class="fas fa-building text-purple-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=courses" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-pink-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Courses</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['total_courses']); ?></h3>
            <i class="fas fa-book text-pink-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=subjects" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-orange-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Subjects</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['total_subjects']); ?></h3>
            <i class="fas fa-book-open text-orange-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=classes" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-teal-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Classes/Secs</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['total_classes']; ?> <span class="text-sm text-slate-400">/ <?php echo $stats['total_sections']; ?></span></h3>
            <i class="fas fa-layer-group text-teal-500 mb-1"></i>
        </div>
    </a>

    <!-- Row 2 -->
    <a href="?module=attendance" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-green-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Attendance</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo $stats['attendance_today_percent']; ?>%</h3>
            <i class="fas fa-calendar-check text-green-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=exams" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-red-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Exams (Up)</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['upcoming_exams']); ?></h3>
            <i class="fas fa-file-signature text-red-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=assignments" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-yellow-400 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Assignments</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['pending_assignments']); ?></h3>
            <i class="fas fa-tasks text-yellow-600 mb-1"></i>
        </div>
    </a>
    <a href="?module=leaves" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-amber-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Leave Reqs</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800"><?php echo number_format($stats['pending_leaves']); ?></h3>
            <i class="fas fa-sign-out-alt text-amber-500 mb-1"></i>
        </div>
    </a>
    <?php 
    function format_inr($amount) {
        if ($amount >= 10000000) {
            return round($amount / 10000000, 2) . 'Cr';
        } elseif ($amount >= 100000) {
            return round($amount / 100000, 2) . 'L';
        } elseif ($amount >= 1000) {
            return round($amount / 1000, 2) . 'k';
        }
        return number_format($amount);
    }
    ?>
    <a href="?module=fees" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-emerald-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Collected</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-slate-800" title="₹<?php echo number_format($stats['fee_collected']); ?>">₹<?php echo format_inr($stats['fee_collected']); ?></h3>
            <i class="fas fa-wallet text-emerald-500 mb-1"></i>
        </div>
    </a>
    <a href="?module=fees" class="block bg-white rounded-xl shadow-sm border border-bordercolor p-2 hover:shadow-md hover:border-orange-300 transition-all cursor-pointer">
        <p class="text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Pending Fee</p>
        <div class="flex justify-between items-end">
            <h3 class="text-2xl font-bold text-orange-500" title="₹<?php echo number_format($stats['fee_pending']); ?>">₹<?php echo format_inr($stats['fee_pending']); ?></h3>
            <i class="fas fa-hand-holding-usd text-orange-500 mb-1"></i>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-3 items-start">
    <!-- 12-Month Attendance Trend (Takes up 2 columns) -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden flex flex-col h-full">
        <div class="px-3 py-1.5 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider"><i class="fas fa-chart-line text-primary mr-2"></i> 12-Month Attendance Trend</h3>
        </div>
        <div class="p-2 flex-1 relative min-h-[200px]">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <!-- Right column: Academic & Fees -->
    <div class="flex flex-col gap-6">
        <!-- Fees Breakdown Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden flex flex-col">
            <div class="px-3 py-1.5 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider"><i class="fas fa-chart-pie text-emerald-500 mr-2"></i> Revenue Breakdown</h3>
            </div>
            <div class="p-2 flex flex-col items-center">
                <div class="relative h-24 w-full flex justify-center mb-4">
                    <canvas id="feeDistChart"></canvas>
                </div>
                <div class="w-full grid grid-cols-2 gap-2 text-center border-t border-slate-100 pt-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Collected</p>
                        <p class="text-base font-bold text-emerald-600">₹<?php echo format_inr($stats['fee_collected']); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Pending</p>
                        <p class="text-base font-bold text-orange-500">₹<?php echo format_inr($stats['fee_pending']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects Distribution Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-bordercolor overflow-hidden flex flex-col">
            <div class="px-3 py-1.5 border-b border-bordercolor bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider"><i class="fas fa-layer-group text-indigo-500 mr-2"></i> Academics Distribution</h3>
            </div>
            <div class="p-2 flex-1">
                <div class="relative h-24 flex justify-center">
                    <canvas id="subjectDistChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data from PHP
const attendanceData = <?php echo $attendanceTrend; ?>;
const subjectDist = <?php echo $subjectsByDept; ?>;
const feeCollected = <?php echo $stats['fee_collected']; ?>;
const feePending = <?php echo $stats['fee_pending']; ?>;

// Attendance Line Chart
if(attendanceData.length > 0) {
    new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: {
            labels: attendanceData.map(d => d.label),
            datasets: [{
                label: 'Attendance %',
                data: attendanceData.map(d => d.value),
                borderColor: '#2563eb', // primary blue
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, max: 100, grid: { borderDash: [2, 4], color: '#f1f5f9' }, ticks: { font: { size: 9 } } },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });
}

// Fee Distribution Doughnut Chart
if (feeCollected > 0 || feePending > 0) {
    new Chart(document.getElementById('feeDistChart'), {
        type: 'doughnut',
        data: {
            labels: ['Collected', 'Pending'],
            datasets: [{
                data: [feeCollected, feePending],
                backgroundColor: ['#10b981', '#f97316'], // emerald and orange
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.toLocaleString('en-IN');
                        }
                    }
                }
            }
        }
    });
}

// Subjects Distribution Pie Chart
if(subjectDist.length > 0) {
    new Chart(document.getElementById('subjectDistChart'), {
        type: 'pie',
        data: {
            labels: subjectDist.map(d => d.label),
            datasets: [{
                data: subjectDist.map(d => d.value),
                backgroundColor: ['#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f59e0b', '#10b981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, font: { size: 9 } } }
            }
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
