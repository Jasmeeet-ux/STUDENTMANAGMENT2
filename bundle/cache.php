<?php
// File-based caching system for database fallbacks

define('CACHE_DIR', __DIR__ . '/cache/');
define('CACHE_EXPIRY', 3600); // 1 hour cache expiry

// Create cache directory if it doesn't exist
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// Function to get cached data
function getCachedData($key) {
    $cacheFile = CACHE_DIR . md5($key) . '.json';

    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);

        // Check if cache is still valid
        if (time() - $cacheData['timestamp'] < CACHE_EXPIRY) {
            return $cacheData['data'];
        } else {
            // Cache expired, delete it
            unlink($cacheFile);
        }
    }

    return null;
}

// Function to set cached data
function setCachedData($key, $data) {
    $cacheFile = CACHE_DIR . md5($key) . '.json';

    $cacheData = [
        'timestamp' => time(),
        'data' => $data
    ];

    file_put_contents($cacheFile, json_encode($cacheData));
}

// Function to get default dashboard data
function getDefaultDashboardData() {
    return [
        'userName' => 'User',
        'userInitial' => 'U',
        'completedCourses' => 0,
        'totalEarnings' => '0.00',
        'currentCourses' => [],
        'learningStreak' => 0,
        'experiencePoints' => 0,
        'achievements' => 0,
        'earningsThisMonth' => '0.00',
        'referralCode' => ''
    ];
}

// Function to get default courses data
function getDefaultCoursesData() {
    return [
        [
            'id' => 1,
            'title' => 'Digital Marketing Fundamentals',
            'total_lessons' => 10,
            'completed_lessons' => 0
        ],
        [
            'id' => 2,
            'title' => 'Web Development Basics',
            'total_lessons' => 15,
            'completed_lessons' => 0
        ]
    ];
}

// Function to cache and retrieve user dashboard data
function getDashboardDataWithFallback($userId, $user) {
    $cacheKey = "dashboard_data_user_{$userId}";

    // Try to get from cache first
    $cachedData = getCachedData($cacheKey);
    if ($cachedData) {
        return $cachedData;
    }

    // If database is available, fetch fresh data and cache it
    if (isDatabaseAvailable()) {
        global $db;

        try {
            if (!$pdo) {
                throw new Exception("Database connection is null");
            }

            $dashboardData = [];

            // User's first name + initial
            $dashboardData['userName'] = $user && isset($user['name']) ? htmlspecialchars(explode(' ', $user['name'])[0]) : 'User';
            $dashboardData['userInitial'] = $user && isset($user['name']) ? strtoupper(substr($user['name'], 0, 1)) : 'U';

            // Completed Courses
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM (
                    SELECT l.course_id
                    FROM lessons l
                    INNER JOIN purchases p ON l.course_id = p.course_id
                    LEFT JOIN progress prog
                        ON l.id = prog.lesson_id
                       AND prog.user_id = ?
                       AND prog.completed = 1
                    WHERE p.user_id = ? AND p.status = 'completed'
                    GROUP BY l.course_id
                    HAVING COUNT(l.id) = COUNT(prog.id)
                ) as sub
            ");
            $stmt->execute([$userId, $userId]);
            $dashboardData['completedCourses'] = $stmt->fetchColumn() ?: 0;

            // Total Earnings
            $stmt = $pdo->prepare("SELECT total_earnings FROM affiliates WHERE user_id = ?");
            $stmt->execute([$userId]);
            $dashboardData['totalEarnings'] = $stmt->fetchColumn() ?: '0.00';

            // Current Courses
            $stmt = $pdo->prepare("
                SELECT
                    c.id,
                    c.title,
                    COALESCE((SELECT COUNT(*) FROM lessons WHERE course_id = c.id), 0) as total_lessons,
                    COALESCE((SELECT COUNT(*)
                              FROM progress p
                              WHERE p.user_id = ?
                                AND p.lesson_id IN (SELECT id FROM lessons WHERE course_id = c.id)
                                AND p.completed = 1), 0) as completed_lessons
                FROM courses c
                JOIN purchases pur ON c.id = pur.course_id
                WHERE pur.user_id = ? AND pur.status = 'completed'
                ORDER BY pur.purchased_at DESC
                LIMIT 3
            ");
            $stmt->execute([$userId, $userId]);
            $dashboardData['currentCourses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Mock data for gamification
            $dashboardData['learningStreak'] = 12;
            $dashboardData['experiencePoints'] = 2450;
            $dashboardData['achievements'] = 23;
            $dashboardData['earningsThisMonth'] = '8,250';
            $dashboardData['referralCode'] = $user['referral_code'] ?? '';

            // Cache the data
            setCachedData($cacheKey, $dashboardData);

            return $dashboardData;

        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            // Fall back to default data
            return getDefaultDashboardData();
        }
    } else {
        // Database not available, return default data
        return getDefaultDashboardData();
    }
}
?>