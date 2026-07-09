<?php
$current_page = basename($_SERVER['PHP_SELF']);
function active($page, $current) {
    return $page === $current 
        ? "text-white bg-primary" 
        : "text-gray-700 hover:bg-blue-50";
}
?>

<div class="w-64 bg-white shadow-lg">
    <div class="p-6 border-b">
        <h1 class="text-xl font-bold text-gray-800">Learning Admin</h1>
    </div>

    <nav class="mt-6 space-y-1">

        <!-- Dashboard -->
        <a href="dashboard.php" class="flex items-center px-6 py-3 <?= active('dashboard.php', $current_page) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2"/>
            </svg>
            <span class="ml-3">Dashboard</span>
        </a>

        
        <!-- modules -->
        <a href="modules.php" class="flex items-center px-6 py-3 <?= active('modules.php', $current_page) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor">
                <path d="M4 19h16M4 5h16M4 12h16" stroke-width="2"/>
            </svg>
            <span class="ml-3">Modules</span>
        </a>

        <!-- Courses -->
        <a href="courses.php" class="flex items-center px-6 py-3 <?= active('courses.php', $current_page) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor">
                <path d="M4 19h16M4 5h16M4 12h16" stroke-width="2"/>
            </svg>
            <span class="ml-3">Courses</span>
        </a>

        <!-- Lessons -->
        <a href="lessons.php" class="flex items-center px-6 py-3 <?= active('lessons.php', $current_page) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor">
                <path d="M4 6h16v12H4z" stroke-width="2"/>
                <path d="M14 10l4-2v4l-4-2z" stroke-width="2"/>
            </svg>
            <span class="ml-3">Lessons</span>
        </a>

        <!-- Users -->
        <a href="users.php" class="flex items-center px-6 py-3 <?= active('users.php', $current_page) ?>">
            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                <circle cx="12" cy="7" r="4" stroke-width="2"/>
                <path d="M6 21c0-4 3-7 6-7s6 3 6 7" stroke-width="2"/>
            </svg>
            <span class="ml-3">Users</span>
        </a>

        <!-- Affiliates -->
        <a href="affiliates.php" class="flex items-center px-6 py-3 <?= active('affiliates.php', $current_page) ?>">
            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                <path d="M7 8l5-5 5 5M7 16l5 5 5-5" stroke-width="2"/>
            </svg>
            <span class="ml-3">Affiliates</span>
        </a>

        <!-- Payments -->
        <a href="payments.php" class="flex items-center px-6 py-3 <?= active('payments.php', $current_page) ?>">
            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                <rect x="3" y="7" width="18" height="10" rx="2" stroke-width="2"/>
            </svg>
            <span class="ml-3">Payments</span>
        </a>

        <!-- Moderation -->
        <a href="moderation.php" class="flex items-center px-6 py-3 <?= active('moderation.php', $current_page) ?>">
            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="7" stroke-width="2"/>
                <path d="M20 20l-4.3-4.3" stroke-width="2"/>
            </svg>
            <span class="ml-3">Moderation</span>
        </a>

        <!-- Settings -->
        <!-- <a href="settings.php" class="flex items-center px-6 py-3 <?= active('settings.php', $current_page) ?>">
            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                <path stroke-width="2" d="M10.3 4.3l1.7-1 1.7 1 2 .3 1.4 1.4.3 2 1 1.7-1 1.7-.3 2-1.4 1.4-2 .3-1.7 1-1.7-1-2-.3-1.4-1.4-.3-2-1-1.7 1-1.7.3-2L8.3 4.6l2-.3z"/>
                <circle cx="12" cy="12" r="3" stroke-width="2"/>
            </svg>
            <span class="ml-3">Settings</span>
        </a> -->

        <!-- Logout -->
        <a href="logout.php" class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50">
            <svg class="h-5 w-5" fill="none" stroke="currentColor">
                <path stroke-width="2" d="M15 12H3m12 0l-4-4m4 4l-4 4M21 4v16" />
            </svg>
            <span class="ml-3">Logout</span>
        </a>

    </nav>
</div>