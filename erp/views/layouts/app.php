<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Education ERP</title>
    <link rel="icon" type="image/png" href="/STUDENTLOGIN/erp/favicon.png">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in-up': 'fadeInUp 0.4s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1d4ed8', // Blue-700
                        secondary: '#ffffff',
                        accent: '#eab308', // Yellow-500
                        background: '#f8fafc', // Very Light Gray
                        card: '#ffffff',
                        hover: '#eff6ff', // Soft Blue
                        bordercolor: '#e2e8f0', // Light Gray
                        success: '#22c55e',
                        danger: '#ef4444',
                        warning: '#eab308'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; }
        .sidebar-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-link:hover, .sidebar-link.active {
            background-color: #eff6ff;
            color: #1d4ed8;
            border-right: 3px solid #1d4ed8;
            transform: translateX(4px);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-slate-800 font-sans">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-bordercolor flex flex-col h-full flex-shrink-0 transition-all duration-300 z-20">
        <div class="h-16 flex items-center px-6 border-b border-bordercolor">
            <i class="fas fa-graduation-cap text-primary text-2xl mr-3"></i>
            <span class="font-bold text-lg tracking-tight">Edu<span class="text-primary">ERP</span></span>
        </div>
        <div class="flex-1 overflow-y-auto py-2">
            <nav class="space-y-1">
                <a href="?module=dashboard" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (!isset($_GET['module']) || $_GET['module'] == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-th-large w-6 text-center mr-2"></i> Dashboard
                </a>
                <a href="?module=students" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'students') ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate w-6 text-center mr-2"></i> Student Management
                </a>
                <a href="?module=teachers" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'teachers') ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard-teacher w-6 text-center mr-2"></i> Teacher Management
                </a>
                <a href="?module=departments" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'departments') ? 'active' : ''; ?>">
                    <i class="fas fa-building w-6 text-center mr-2"></i> Department Management
                </a>
                <a href="?module=courses" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'courses') ? 'active' : ''; ?>">
                    <i class="fas fa-book w-6 text-center mr-2"></i> Course Management
                </a>
                <a href="?module=subjects" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'subjects') ? 'active' : ''; ?>">
                    <i class="fas fa-book-open w-6 text-center mr-2"></i> Subject Management
                </a>
                <a href="?module=classes" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'classes') ? 'active' : ''; ?>">
                    <i class="fas fa-chalkboard w-6 text-center mr-2"></i> Class Management
                </a>
                <a href="?module=sections" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'sections') ? 'active' : ''; ?>">
                    <i class="fas fa-layer-group w-6 text-center mr-2"></i> Section Management
                </a>
                <a href="?module=attendance" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'attendance') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check w-6 text-center mr-2"></i> Attendance
                </a>
                <a href="?module=examinations" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'examinations') ? 'active' : ''; ?>">
                    <i class="fas fa-file-signature w-6 text-center mr-2"></i> Examinations
                </a>
                <a href="?module=assignments" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'assignments') ? 'active' : ''; ?>">
                    <i class="fas fa-tasks w-6 text-center mr-2"></i> Assignments
                </a>
                <a href="?module=fees" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'fees') ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar w-6 text-center mr-2"></i> Fees
                </a>
                <a href="?module=leaves" class="sidebar-link flex items-center px-6 py-2.5 text-sm font-semibold text-slate-600 <?php echo (isset($_GET['module']) && $_GET['module'] == 'leaves') ? 'active' : ''; ?>">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-2"></i> Leave Management
                </a>

            </nav>
        </div>
        <div class="border-t border-bordercolor p-4">
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Header removed -->

        <!-- Main Workspace -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 animate-fade-in-up">
            
            <!-- Toast Notifications -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 flex items-center p-4 rounded-lg bg-green-50 border border-green-200">
                    <i class="fas fa-check-circle text-success mr-3 text-lg"></i>
                    <p class="text-sm text-green-800 font-medium"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 flex items-center p-4 rounded-lg bg-red-50 border border-red-200">
                    <i class="fas fa-exclamation-circle text-danger mr-3 text-lg"></i>
                    <p class="text-sm text-red-800 font-medium"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?php echo $content; ?>
            
        </main>
    </div>
</body>
</html>
