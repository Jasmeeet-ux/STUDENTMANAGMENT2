<?php
session_start();

// Include database connection (NEW PDO SYSTEM)
require 'db.php';

// Debug logs
error_log('Pricings.php - Request Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
error_log('Pricings.php - POST data: ' . var_export($_POST, true));


// =======================================================
// ERRORS FROM PURCHASE HANDLER
// =======================================================
$error = $_GET['error'] ?? '';
$purchaseError = $_SESSION['purchase_error'] ?? '';
unset($_SESSION['purchase_error']);


// =======================================================
// DEFAULT FALLBACK PRICES (WILL BE OVERRIDDEN BY DB)
// =======================================================
$proPrice = 29;
$premiumPrice = 59;


// =======================================================
// LOAD BUNDLE PRICES FROM DATABASE
// =======================================================
if ($pdo && isDatabaseAvailable()) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, price 
            FROM courses 
            WHERE is_bundle = 1
            ORDER BY id ASC
        ");
        $stmt->execute();
        $bundlesForPrice = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If bundles exist override prices dynamically
        if (!empty($bundlesForPrice)) {
            if (isset($bundlesForPrice[0]['price'])) {
                $proPrice = $bundlesForPrice[0]['price'];
            }
            if (isset($bundlesForPrice[1]['price'])) {
                $premiumPrice = $bundlesForPrice[1]['price'];
            }
        }

        error_log("Bundle prices loaded successfully.");
    } catch (Exception $e) {
        error_log("Bundle price fetch error: " . $e->getMessage());
    }
}


// =======================================================
// USER LOGIN + PURCHASED COURSES
// =======================================================
$loggedIn = isLoggedIn();
$purchasedCourses = [];  // always an array
$proPurchased = false;
$premiumPurchased = false;

if ($loggedIn && $pdo && isDatabaseAvailable()) {
    try {
        $userId = getCurrentUserId();

        $stmt = $pdo->prepare("
            SELECT course_id 
            FROM purchases 
            WHERE user_id = ? AND status = 'completed'
        ");
        $stmt->execute([$userId]);
        $purchasedCourses = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Auto detect purchased bundles (IDs not hardcoded in UI)
        $proPurchased = in_array($bundlesForPrice[0]['id'] ?? -1, $purchasedCourses);
        $premiumPurchased = in_array($bundlesForPrice[1]['id'] ?? -1, $purchasedCourses);

        error_log("Purchased courses: " . implode(',', $purchasedCourses));
    } catch (Exception $e) {
        error_log("Purchase check error: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Catalog - Culture of Internet</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon_coi.png">
    <link rel="shortcut icon" href="images/favicon_coi.png">

    <!-- Preload critical resources -->
    <link rel="preload" href="style.css" as="style">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        as="style">

    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        /* CSS Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            /* line-height: 1.6; */
            color: var(--text-color);
            background-color: var(--bg-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* CSS Custom Properties for Theme */
        :root {
            --primary-color: #be8652;
            --primary-dark: #a56f45;
            --secondary-color: #64748b;
            --accent-color: #be8652;
            --success-color: #10b981;
            --bg-color: #ffffff;
            --surface-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            --transition-smooth: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Advanced Animation Variables */
        :root {
            --animation-duration: 0.6s;
            --animation-delay: 0.1s;
            --animation-easing: cubic-bezier(0.4, 0, 0.2, 1);
            --stagger-delay: 0.1s;
        }

        /* Dark Mode Variables */
        [data-theme="dark"] {
            --bg-color: #0f172a;
            --surface-color: #1e293b;
            --card-bg: #1e293b;
            --text-color: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(235, 190, 129, 0.5)), url('https://images.unsplash.com/photo-1743167150074-4fe3fd1cd2b6?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fGJhY2tncm91bmQlMjBob3Jpem9udGFsfGVufDB8fDB8fHww&auto=format&fit=crop&q=60&w=600') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 5rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }


        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="500" cy="500" r="400" fill="url(%23a)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            padding-top: 30px;
            position: relative;
            z-index: 2;

        }


        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #ffffff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .hero-description {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
        }



        /* CTA Button */
        .cta-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            box-shadow: var(--shadow-lg);
        }

        .cta-button:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Main Content */
        main {
            min-height: calc(100vh - 200px);
        }

        /* Section Styles */
        /* section {
    padding: 3rem 0;
} */

        .section-title {
            padding-top: 20px;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Bundle Controls */
        .bundle-controls {
            margin-bottom: 3rem;
        }



        /* Bundles Grid */
        .bundles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* Bundle Slider */
        .bundle-slider {
            position: relative;
            margin-bottom: 3rem;
        }

        .bundle-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        /* Bundle slider controls removed - only touch and auto-slide functionality remains */

        /* Bundle Cards */
        .bundle-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border-color);
            position: relative;
            transform-style: preserve-3d;
        }

        .bundle-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.15),
                0 0 30px rgba(190, 134, 82, 0.2),
                0 0 60px rgba(190, 134, 82, 0.1);
            border-color: var(--primary-color);
        }

        .bundle-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(190, 134, 82, 0.05) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            z-index: 1;
        }

        .bundle-card:hover::before {
            opacity: 1;
        }

        .bundle-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .bundle-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .bundle-card:hover .bundle-image img {
            transform: scale(1.05);
        }

        .bundle-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bundle-badge.premium {
            background: #8b5cf6;
        }

        .bundle-badge.advanced {
            background: #ef4444;
        }

        .bundle-content {
            padding: 2rem;
        }

        .bundle-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .bundle-description {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* Bundle Includes */
        .bundle-includes {
            margin-bottom: 1.5rem;
        }

        .include-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .include-icon {
            font-size: 1.1rem;
        }

        /* Bundle Rating */
        .bundle-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            display: flex;
            gap: 0.1rem;
        }

        .star {
            color: #fbbf24;
            font-size: 1rem;
        }

        .rating-text {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Bundle Pricing */
        .bundle-pricing {
            margin-bottom: 1.5rem;
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
        }

        .price-note {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }

        /* Bundle Actions */
        .bundle-actions {
            display: flex;
            gap: 1rem;
        }

        .bundle-cta {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            text-align: center;
        }

        .bundle-cta.primary {
            background: var(--primary-color);
            color: white;
        }

        .bundle-cta.primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .bundle-cta.secondary {
            background: var(--surface-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .bundle-cta.secondary:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Comparison Section */
        /* .comparison {
    background: var(--surface-color);
    padding: 5rem 0;
} */

        /* Comparison Cards */
        .comparison-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .comparison-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 2px solid var(--border-color);
            position: relative;
            text-align: center;
            transform-style: preserve-3d;
        }

        .comparison-card:hover {
            transform: translateY(-8px) rotateX(2deg);
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 40px rgba(190, 134, 82, 0.15);
            border-color: var(--primary-color);
        }

        .comparison-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: var(--border-radius);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        .comparison-card:hover::before {
            opacity: 0.3;
        }

        .comparison-card.featured {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, var(--card-bg) 0%, rgba(37, 99, 235, 0.05) 100%);
        }

        .popular-ribbon {
            position: absolute;
            top: -10px;
            right: 20px;
            background: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow);
        }

        .card-header {
            margin-bottom: 2rem;
        }

        .bundle-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .comparison-card .bundle-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .comparison-card .bundle-level {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: block;
        }

        .comparison-card .bundle-price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .card-features {
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .feature-icon {
            font-size: 1rem;
        }

        .select-bundle-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            width: 100%;
        }

        .select-bundle-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .select-bundle-btn.featured {
            background: var(--accent-color);
        }

        .select-bundle-btn.featured:hover {
            background: #d97706;
        }

        /* Selected Card State */
        .comparison-card.selected {
            border-color: var(--success-color);
            background: linear-gradient(135deg, var(--card-bg) 0%, rgba(16, 185, 129, 0.05) 100%);
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.1), 0 10px 10px -5px rgba(16, 185, 129, 0.04);
        }

        .comparison-card.selected .select-bundle-btn {
            background: var(--success-color);
        }

        .comparison-card.selected .select-bundle-btn:hover {
            background: #059669;
        }

        /* Comparison Table Container */
        .comparison-table-container {
            margin-bottom: 4rem;
        }

        .table-title {
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        .comparison-table {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin: 0 auto;
            max-width: 1200px;
        }

        .table-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .table-header>div {
            padding: 1.5rem;
            text-align: center;
        }

        .feature-column {
            text-align: left !important;
            font-weight: 700;
        }

        .popular {
            background: rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .table-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            border-bottom: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .table-row:hover {
            background: var(--surface-color);
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-row>div {
            padding: 1rem 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature {
            text-align: left !important;
            font-weight: 600;
            color: var(--text-color);
        }

        .value {
            font-weight: 500;
            color: var(--text-secondary);
        }

        .value.check {
            font-size: 1.2rem;
        }

        .value.price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .price-row {
            background: var(--surface-color);
            font-weight: 700;
        }

        /* Comparison CTA Section */
        .comparison-cta {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            /* box-shadow: var(--shadow); */
            margin-top: 2rem;
        }

        .comparison-cta h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .comparison-cta p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .comparison-cta .cta-button {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .comparison-cta .cta-button.secondary {
            background: var(--surface-color);
            color: var(--text-color);
            border: 2px solid var(--primary-color);
        }

        .comparison-cta .cta-button.secondary:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Testimonial section  */
        .Testi-testimonial-section {
            max-width: auto;
            width: 100%;
            text-align: center;
            padding: 60px 0px;
        }

        .Testi-quote-icon {
            color: #be8652;
            width: 70px;
            height: 70px;
            margin: 0 auto 40px;
        }

        .quote-icon svg {
            width: 100%;
            height: 100%;
        }

        .Testi-testimonial-text {
            font-size: 18px;
            line-height: 1.8;
            color: #000000;
            margin-bottom: 40px;
            text-align: center;
            max-width: 70%;
            margin-left: auto;
            margin-right: auto;
            transition: opacity 0.5s ease;
        }

        .Testi-testimonial-text.fade-in {
            opacity: 1;
        }

        .Testi-avatars-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            position: relative;
            width: 100%;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .Testi-avatars-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            flex: 1;
            overflow: hidden;
            padding: 20px 0;
        }

        .Testi-nav-button {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            transition: all 0.3s ease;
            padding: 0;
            flex-shrink: 0;
        }

        .Testi-nav-button:hover {
            background-color: #f5f5f5;
            color: #be8652;
        }

        .Testi-nav-button svg {
            width: 24px;
            height: 24px;
        }

        .Testi-avatar {
            width: 70px;
            height: 70px;
            min-width: 70px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            opacity: 0.5;
            flex-shrink: 0;
        }

        .Testi-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .Testi-avatar-active {
            width: 120px;
            height: 120px;
            min-width: 120px;
            border: 4px solid #be8652;
            opacity: 1;
            box-shadow: 0 0 0 4px rgba(190, 134, 82, 0.1);
        }

        .Testi-avatar:not(.Testi-avatar-active):hover {
            opacity: 0.7;
            transform: scale(1.05);
        }

        .Testi-person-info {
            text-align: center;
        }

        .Testi-person-name {
            padding-bottom: 10px;
            font-size: 20px;
            font-weight: 500;
            color: #000;
            margin-bottom: -10px;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .Testi-person-name.fade-in {
            opacity: 1;
        }

        .Testi-person-title {
            font-size: 12px;
            font-weight: 400;
            color: #252525;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: opacity 0.5s ease;
        }

        .Testi-person-title.fade-in {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .Testi-testimonial-section {
                padding: 30px 15px;
            }

            .Testi-quote-icon {
                width: 45px;
                height: 45px;
                margin-bottom: 30px;
            }

            .Testi-testimonial-text {
                font-size: 13px;
                margin-bottom: 40px;
            }

            .Testi-avatars-wrapper {
                gap: 15px;
            }

            .Testi-avatars-container {
                gap: 8px;
                padding: 15px 0;
            }

            .Testi-nav-button {
                width: 35px;
                height: 35px;
                min-width: 35px;
            }

            .Testi-nav-button svg {
                width: 20px;
                height: 20px;
            }

            .Testi-avatar {
                width: 50px;
                height: 50px;
                min-width: 50px;
                border: 2px solid transparent;
            }

            .Testi-avatar-active {
                width: 90px;
                height: 90px;
                min-width: 90px;
                border: 3px solid #be8652;
            }

            .Testi-person-name {
                font-size: 18px;
            }

            .Testi-person-title {
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .Testi-testimonial-section {
                padding: 20px 10px;
            }

            .Testi-quote-icon {
                width: 40px;
                height: 40px;
                margin-bottom: 25px;
            }

            .Testi-testimonial-text {
                font-size: 14px;
                margin-bottom: 30px;
            }

            .Testi-avatars-wrapper {
                gap: 10px;
            }

            .Testi-avatars-container {
                gap: 6px;
                padding: 10px 0;
            }

            .Testi-nav-button {
                width: 30px;
                height: 30px;
                min-width: 30px;
            }

            .Testi-nav-button svg {
                width: 18px;
                height: 18px;
            }

            .Testi-avatar {
                width: 40px;
                height: 40px;
                min-width: 40px;
            }

            .Testi-avatar-active {
                width: 70px;
                height: 70px;
                min-width: 70px;
                border: 3px solid #be8652;
            }

            .Testi-person-name {
                font-size: 16px;
            }

            .Testi-person-title {
                font-size: 10px;
            }
        }

        /* FAQ Section */
        .faq-container {
            width: 100%;
            max-width: auto;
            margin: 0 auto;
        }

        .faq-wrapper {
            background: white;
            padding: 80px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.04);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            border: 1px solid rgba(190, 134, 82, 0.08);
        }

        .faq-header {
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: sticky;
            top: 40px;
            align-self: start;
        }

        .faq-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #f0e6dd 0%, #f0e6dd 100%);
            color: #be8652;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            width: fit-content;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            border: 1px solid rgba(190, 134, 82, 0.15);
            animation: pulse 2s ease-in-out infinite;
        }

        .faq-icon {
            font-size: 16px;
        }

        .faq-wrapper h1 {
            font-size: 52px;
            font-weight: 700;
            color: #0f0f0f;
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .faq-highlight {
            color: #be8652;
            display: inline-block;
            position: relative;
        }

        .faq-highlight::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 12px;
            background: linear-gradient(90deg, rgba(190, 134, 82, 0.2), rgba(190, 134, 82, 0.2));
            z-index: -1;
            border-radius: 4px;
        }

        .faq-subtitle {
            color: #252525;
            font-size: 17px;
            line-height: 1.7;
            max-width: 420px;
            font-weight: 400;
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .faq-item {
            background: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .faq-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(190, 134, 82, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .faq-item:hover::before {
            left: 100%;
        }

        .faq-item:hover {
            border-color: #be8652;
            box-shadow: 0 12px 32px rgba(190, 134, 82, 0.15);
            background: white;
            transform: translateY(-3px) scale(1.01);
        }

        .faq-question {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px;
            cursor: pointer;
            user-select: none;
            transition: padding 0.25s ease;
        }

        .faq-question span {
            font-size: 17px;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -0.01em;
        }

        .faq-toggle-btn {
            background: linear-gradient(135deg, #f0e6dd 0%, #f0e6dd 100%);
            border: 1px solid rgba(190, 134, 82, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            color: #be8652;
        }

        .faq-toggle-btn:hover {
            background: linear-gradient(135deg, #be8652 0%, #be8652 100%);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(190, 134, 82, 0.3);
        }

        .faq-item.active {
            border-color: #be8652;
            background: white;
            box-shadow: 0 12px 32px rgba(190, 134, 82, 0.18);
        }

        .faq-item.active .faq-toggle-btn {
            background: linear-gradient(135deg, #be8652 0%, #be8652 100%);
            color: white;
            transform: rotate(180deg);
            box-shadow: 0 4px 12px rgba(190, 134, 82, 0.3);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), padding 0.5s ease, opacity 0.4s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 300px;
            padding: 0 28px 28px 28px;
            opacity: 1;
        }

        .faq-answer p {
            color: #252525;
            font-size: 15px;
            line-height: 1.75;
            font-weight: 400;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @media (max-width: 968px) {
            .faq-wrapper {
                grid-template-columns: 1fr;
                gap: 48px;
                padding: 48px;
            }

            .faq-header {
                position: static;
            }

            .faq-wrapper h1 {
                font-size: 40px;
            }

            .faq-subtitle {
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .faq-wrapper {
                padding: 32px 24px;
            }

            .faq-wrapper h1 {
                font-size: 32px;
            }

            .faq-question {
                padding: 20px;
            }

            .faq-question span {
                font-size: 15px;
            }

            .faq-toggle-btn {
                width: 36px;
                height: 36px;
            }

            .faq-item.active .faq-answer {
                padding: 0 20px 20px 20px;
            }
        }

        /* Trust Section */
        .trust {
            background: var(--surface-color);
        }

        .trust-content {
            padding: 3rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .payment-partners h3,
        .trust-stats h3 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .payment-logos {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .payment-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--card-bg);
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .payment-logo:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }



        .trust-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            align-items: center
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
        }



        /* Shopping Cart */

        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: var(--card-bg);
            box-shadow: var(--shadow-lg);
            transition: right 0.3s ease;
            z-index: 1001;
            overflow-y: auto;
        }

        .cart-sidebar.open {
            right: 0;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--primary-color);
            color: white;
        }

        .cart-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        #close-cart {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
        }

        #close-cart:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .cart-items {
            padding: 1rem;
            min-height: 300px;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            margin-bottom: 1rem;
            background: var(--bg-color);
        }

        .cart-item-image {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius-sm);
            overflow: hidden;
            flex-shrink: 0;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.25rem;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .cart-item-remove {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .cart-item-remove:hover {
            background: #d97706;
        }

        .cart-total {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-color);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-color);
        }

        .checkout-btn {
            width: 100%;
            background: var(--success-color);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
        }

        .checkout-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .hero-title {
                padding-top: 30px;
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .bundles-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            /* Bundle Slider Tablet and smaller screens (below 1200px) */
            @media (max-width: 1199px) {
                .bundle-cards {
                    display: flex;
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    gap: 1rem;
                    padding: 0 1rem;
                    scrollbar-width: none;
                    -ms-overflow-style: none;
                }

                .bundle-cards::-webkit-scrollbar {
                    display: none;
                }

                .bundle-card {
                    flex-shrink: 0;
                    width: 320px;
                    scroll-snap-align: start;
                }

                /* Bundle slider controls removed - only touch/auto-slide functionality remains */
            }

            /* Bundle Slider Mobile (below 770px) */
            @media (max-width: 769px) {
                .bundle-cards {
                    display: flex;
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    gap: 1rem;
                    padding: 0 1rem;
                    scrollbar-width: none;
                    -ms-overflow-style: none;
                }

                .bundle-cards::-webkit-scrollbar {
                    display: none;
                }

                .bundle-card {
                    flex-shrink: 0;
                    width: 280px;
                    scroll-snap-align: start;
                }

                .bundle-slider-controls {
                    display: flex !important;
                    justify-content: center;
                    gap: 1rem;
                    margin-top: 2rem;
                }

                .bundle-prev-btn,
                .bundle-next-btn {
                    width: 35px;
                    height: 35px;
                    font-size: 1rem;
                }

                .bundle-slider-dots {
                    display: flex !important;
                    justify-content: center;
                    gap: 0.5rem;
                    margin-top: 1rem;
                }
            }

            .bundle-actions {
                flex-direction: column;
            }

            .table-header,
            .table-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .table-header>div,
            .table-row>div {
                padding: 0.75rem;
                border-bottom: 1px solid var(--border-color);
            }

            .feature-column {
                background: var(--primary-color);
                color: white;
                font-weight: 700;
            }

            .trust-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .trust-stats {
                flex-direction: row;
                gap: 1rem;
                justify-content: space-around;
                flex-wrap: wrap;
            }

            .footer-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .footer-links {
                justify-content: center;
            }

            .cart-sidebar {
                width: 100%;
                right: -100%;
            }







            /* Comparison Section Mobile - Slider for Small Screens */
            .comparison-cards {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 1rem;
                padding: 0 1rem;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .comparison-cards::-webkit-scrollbar {
                display: none;
            }

            .comparison-card {
                flex-shrink: 0;
                width: 280px;
                scroll-snap-align: start;
            }

            .comparison-table-container {
                display: none !important;
            }

            /* Show mobile accordion on small screens */
            .mobile-bundle-features {
                display: block !important;
                margin-top: 2rem !important;
            }

            .mobile-bundle-accordion {
                background: var(--card-bg) !important;
                border-radius: var(--border-radius) !important;
                margin-bottom: 1rem !important;
                overflow: hidden !important;
                box-shadow: var(--shadow) !important;
                border: 1px solid var(--border-color) !important;
            }

            .mobile-bundle-header {
                padding: 1.5rem !important;
                background: var(--primary-color) !important;
                color: white !important;
                cursor: pointer !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                transition: var(--transition) !important;
                user-select: none !important;
            }

            .mobile-bundle-header:hover {
                background: var(--primary-dark) !important;
            }

            .mobile-bundle-header:active {
                background: var(--primary-dark) !important;
                transform: scale(0.98) !important;
            }

            .mobile-bundle-title {
                font-size: 1.2rem !important;
                font-weight: 700 !important;
                flex: 1 !important;
            }

            .mobile-bundle-toggle {
                font-size: 1.5rem !important;
                transition: transform 0.3s ease !important;
                margin-left: 1rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 24px !important;
                height: 24px !important;
            }

            .mobile-bundle-content {
                max-height: 0 !important;
                overflow: hidden !important;
                transition: max-height 0.4s ease !important;
                background: var(--surface-color) !important;
            }

            .mobile-bundle-content.open {
                max-height: 1000px !important;
            }

            .mobile-feature-list {
                padding: 1.5rem !important;
                margin: 0 !important;
            }

            .mobile-feature-item {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding: 0.75rem 0 !important;
                border-bottom: 1px solid var(--border-color) !important;
                margin: 0 !important;
            }

            .mobile-feature-item:last-child {
                border-bottom: none !important;
            }

            .mobile-feature-name {
                font-weight: 600 !important;
                color: var(--text-color) !important;
                font-size: 0.95rem !important;
                flex: 1 !important;
            }

            .mobile-feature-value {
                color: var(--text-secondary) !important;
                font-weight: 500 !important;
                font-size: 0.9rem !important;
                text-align: right !important;
                flex-shrink: 0 !important;
            }

            .mobile-feature-check {
                color: var(--success-color) !important;
                font-size: 1.2rem !important;
                flex-shrink: 0 !important;
            }

            .mobile-feature-x {
                color: #ef4444 !important;
                font-size: 1.2rem !important;
                flex-shrink: 0 !important;
            }

            .comparison-cta {
                padding: 2rem 1rem;
            }

            /* Desktop Styles - Show original design on screens 786px and above */
            @media (min-width: 786px) {

                /* Hide mobile accordion on desktop */
                .mobile-bundle-features {
                    display: none !important;
                }

                /* Show desktop comparison cards and table on desktop */
                .comparison-cards {
                    display: flex !important;
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    gap: 1rem;
                    padding: 0 1rem;
                    scrollbar-width: none;
                    -ms-overflow-style: none;
                }

                .comparison-cards::-webkit-scrollbar {
                    display: none;
                }

                .comparison-card {
                    flex-shrink: 0;
                    width: 280px;
                    scroll-snap-align: start;
                }

                .comparison-table-container {
                    display: block !important;
                }
            }

            .comparison-cta h3 {
                font-size: 1.5rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .hero {
                padding: 3rem 0;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .testimonial-content {
                padding: 2rem 1rem;
            }

            .bundle-content {
                padding: 1.5rem;
            }

            .cart-header {
                padding: 1rem;
            }

            .cart-total {
                padding: 1rem;
            }

            /* Keep comparison cards as slider on 480px screens */
            .comparison-cards {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 1rem;
                padding: 0 1rem;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .comparison-cards::-webkit-scrollbar {
                display: none;
            }

            .comparison-card {
                flex-shrink: 0;
                width: 260px;
                scroll-snap-align: start;
            }

            /* Adjust table padding for smaller screens */
            .table-header>div,
            .table-row>div {
                padding: 0.5rem;
            }

            .feature {
                font-size: 0.9rem;
            }

            .value {
                font-size: 0.9rem;
            }
        }

        /* Focus Styles for Accessibility */
        button:focus,
        input:focus,
        select:focus,
        a:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            :root {
                --border-color: #000000;
                --text-secondary: #000000;
            }

            [data-theme="dark"] {
                --border-color: #ffffff;
                --text-secondary: #ffffff;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            html {
                scroll-behavior: auto;
            }
        }

        /* Advanced Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(100px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(37, 99, 235, 0.3);
            }

            50% {
                box-shadow: 0 0 30px rgba(37, 99, 235, 0.6);
            }
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* Animation Classes */
        .fade-in-up {
            animation: fadeInUp 0.8s var(--animation-easing) forwards;
        }

        .fade-in-down {
            animation: fadeInDown 0.8s var(--animation-easing) forwards;
        }

        .fade-in-left {
            animation: fadeInLeft 0.8s var(--animation-easing) forwards;
        }

        .fade-in-right {
            animation: fadeInRight 0.8s var(--animation-easing) forwards;
        }

        .slide-in-up {
            animation: slideInUp 0.8s var(--animation-easing) forwards;
        }

        .slide-in-left {
            animation: slideInLeft 0.8s var(--animation-easing) forwards;
        }

        .slide-in-right {
            animation: slideInRight 0.8s var(--animation-easing) forwards;
        }

        .scale-in {
            animation: scaleIn 0.6s var(--animation-easing) forwards;
        }

        .bounce-in {
            animation: bounceIn 0.8s var(--animation-easing) forwards;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        .glow {
            animation: glow 2s ease-in-out infinite alternate;
        }

        /* Stagger Animation Delays */
        .bundle-card:nth-child(1) {
            animation-delay: 0s;
        }

        .bundle-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .bundle-card:nth-child(3) {
            animation-delay: 0.2s;
        }

        .comparison-card:nth-child(1) {
            animation-delay: 0s;
        }

        .comparison-card:nth-child(2) {
            animation-delay: 0.1s;
        }

        .comparison-card:nth-child(3) {
            animation-delay: 0.2s;
        }

        .stat:nth-child(1) {
            animation-delay: 0s;
        }

        .stat:nth-child(2) {
            animation-delay: 0.1s;
        }

        .stat:nth-child(3) {
            animation-delay: 0.2s;
        }

        /* Enhanced Loading Spinner */
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-right: 4px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite, pulse 2s ease-in-out infinite;
            margin: 0 auto 20px;
            position: relative;
        }

        .loading-spinner::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border: 2px solid transparent;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 2s linear infinite reverse;
        }

        /* Loading animations for testimonials */
        .testimonial-loading {
            opacity: 0;
            animation: fadeInUp 0.8s ease forwards;
        }

        .testimonial-loading.fade-in {
            opacity: 1;
        }

        /* Enhanced Button Animations */
        .cta-button {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--accent-color) 0%, #f97316 100%);
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        /* Card Hover Effects */
        .bundle-card {
            position: relative;
            overflow: hidden;
        }

        .bundle-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.6s;
            z-index: 1;
        }

        .bundle-card:hover::before {
            left: 100%;
        }

        .bundle-content {
            position: relative;
            z-index: 2;
        }

        /* Enhanced Bundle Actions */
        .bundle-cta {
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }

        .bundle-cta::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
            z-index: 0;
        }

        .bundle-cta:hover::before {
            width: 300px;
            height: 300px;
        }

        .bundle-cta span,
        .bundle-cta i {
            position: relative;
            z-index: 1;
        }

        /* Search Input Enhancement */
        .nav-search input {
            transition: var(--transition-bounce);
        }

        .nav-search input:focus {
            transform: scale(1.02);
        }



        /* Testimonial Cards */
        .testimonial-content {
            position: relative;
            overflow: hidden;
        }

        .testimonial-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--primary-color));
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        /* Stats Animation */
        .stat-number {
            font-variant-numeric: tabular-nums;
            transition: var(--transition-smooth);
        }

        .stat-number.animated {
            animation: bounceIn 0.8s var(--animation-easing) forwards;
        }

        /* Navigation Enhancement */
        .nav-menu a {
            position: relative;
            overflow: hidden;
        }

        .nav-menu a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-menu a:hover::before {
            width: 100%;
        }

        /* Dark Mode Toggle Enhancement */
        .dark-mode-toggle {
            position: relative;
            overflow: hidden;
        }

        .dark-mode-toggle::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: var(--primary-color);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
            z-index: -1;
        }

        .dark-mode-toggle:hover::before {
            width: 100%;
            height: 100%;
        }

        /* Scroll Progress Indicator */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            transform-origin: left;
            z-index: 1000;
            animation: slideInLeft 0.3s ease;
        }

        /* Intersection Observer Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s var(--animation-easing), transform 0.8s var(--animation-easing);
        }

        .animate-on-scroll.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        /* Scroll-triggered animations for sections */
        .section-animate {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .section-animate.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        /* Hero animations */
        .hero-animate {
            opacity: 0;
            transform: translateY(-30px);
            transition: all 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hero-animate.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        /* Staggered children animations */
        .stagger-children>* {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stagger-children.animate-in>*:nth-child(1) {
            transition-delay: 0.1s;
        }

        .stagger-children.animate-in>*:nth-child(2) {
            transition-delay: 0.2s;
        }

        .stagger-children.animate-in>*:nth-child(3) {
            transition-delay: 0.3s;
        }

        .stagger-children.animate-in>*:nth-child(4) {
            transition-delay: 0.4s;
        }

        .stagger-children.animate-in>*:nth-child(5) {
            transition-delay: 0.5s;
        }

        .stagger-children.animate-in>* {
            opacity: 1;
            transform: translateY(0);
        }

        /* Enhanced Focus States */
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        a:focus-visible {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
            border-radius: var(--border-radius-sm);
        }

        /* Loading States */
        .loading-shimmer {
            background: linear-gradient(90deg, var(--surface-color) 25%, var(--border-color) 50%, var(--surface-color) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        /* Success/Error States */
        .success-message {
            background: var(--success-color);
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            animation: slideInRight 0.3s ease;
        }

        .error-message {
            background: #ef4444;
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            animation: slideInLeft 0.3s ease;
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .loading-spinner {
                width: 40px;
                height: 40px;
            }

            .bundle-card {
                animation-duration: 0.4s;
            }

            .cta-button {
                animation-duration: 2s;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .parallax-element {
                transform: none !important;
            }
        }

        /* High Contrast Mode */
        @media (prefers-contrast: high) {

            .bundle-card::before,
            .cta-button::before,
            .bundle-cta::before {
                display: none;
            }

            .testimonial-content::before {
                display: none;
            }
        }

        /* Print Styles */
        @media print {
            .cart-sidebar,
            .loading-screen,

            .bundle-actions {
                display: none !important;
            }

            .hero {
                background: none !important;
                color: black !important;
            }

            .bundle-card,
            .testimonial-content {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }
        }

        /* Mentor Section  */
        .container {
            max-width: 95%;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 3rem;
        }

        h2 {
            font-size: 2.5rem;
        }

        h3 {
            font-size: 1.8rem;
        }

        h4 {
            font-size: 1.4rem;
        }

        p {
            margin-bottom: 1rem;
            line-height: 1.8;
        }

        /* Section Styles */
        /* section {
    padding: 60px 0;
} */

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            margin-bottom: 1rem;
            color: #333;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Mentors Section */
        .mentors {
            background: white;
            padding-top: 50px;
        }

        .ment-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 4rem;
        }

        .ment-card-1,
        .ment-card-2,
        .ment-card-3 {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .ment-card-1:hover,
        .ment-card-2:hover,
        .ment-card-3:hover {
            transform: translateY(-10px);
            border-color: #be8652;
            background: white;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .ment-image-1,
        .ment-image-2,
        .ment-image-3 {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .ment-image-1 img,
        .ment-image-2 img,
        .ment-image-3 img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .ment-card-1:hover .ment-image-1 img,
        .ment-card-2:hover .ment-image-2 img,
        .ment-card-3:hover .ment-image-3 img {
            transform: scale(1.05);
        }

        .ment-badge-1,
        .ment-badge-2,
        .ment-badge-3 {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 30px;
            height: 30px;
            background: #be8652;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .ment-info-1 h3,
        .ment-info-2 h3,
        .ment-info-3 h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .ment-role-1,
        .ment-role-2,
        .ment-role-3 {
            color: #be8652;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .ment-experience-1,
        .ment-experience-2,
        .ment-experience-3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .ment-company-1,
        .ment-company-2,
        .ment-company-3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .ment-specialties-1,
        .ment-specialties-2,
        .ment-specialties-3 {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .ment-specialties-1 span,
        .ment-specialties-2 span,
        .ment-specialties-3 span {
            background: #f0e6dd;
            color: #be8652;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .mentor-cta {
            background: #f8f9fa;
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
        }

        .mentor-cta h3 {
            color: #333;
            margin-bottom: 1rem;
        }

        .mentor-cta p {
            color: #666;
            margin-bottom: 2rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
                max-width: 100%;
            }

            .ment-cards {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 1rem;
                padding: 0 1rem;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .ment-cards::-webkit-scrollbar {
                display: none;
            }

            .ment-card-1,
            .ment-card-2,
            .ment-card-3 {
                flex-shrink: 0;
                width: 280px;
                scroll-snap-align: start;
            }

            .mentor-slider-btn {
                display: block !important;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: white;
                border: none;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                z-index: 10;
            }

            .mentor-slider-btn:hover {
                background: #be8652;
                color: white;
                transform: translateY(-50%) scale(1.1);
            }

            .mentor-prev-btn {
                left: -20px;
            }

            .mentor-next-btn {
                right: -20px;
            }

            .ment-slider-dots {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                margin-top: 2rem;
            }

            .ment-dot-1,
            .ment-dot-2,
            .ment-dot-3 {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #ccc;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .ment-dot-1.active,
            .ment-dot-2.active,
            .ment-dot-3.active {
                background: #be8652;
                transform: scale(1.2);
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 2rem;
            }

            h3 {
                font-size: 1.5rem;
            }

        }

        /* Buttons */
        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .hero-btn-primary {
            background-image: linear-gradient(90deg, #EBBE81, #000000);
            background-size: 200% 200%;
            box-shadow: 0 5px 8px 0 black;
            color: white;
            animation: gradientShift 3s ease infinite;
        }

        .hero-btn-primary:hover {
            background-image: linear-gradient(120deg, #EBBE81, #000000);
            background-size: 200% 200%;
            transform: translateY(-2px);
            box-shadow: 0 5px 8px 0 black;
        }

        .hero-btn-outline {
            background: transparent;
            color: #be8652;
            border: 2px solid #be8652;
        }

        .hero-btn-outline:hover {
            background: #b37841;
            color: white;
        }

        .hero-btn-large {
            padding: 9px 17px;
            font-size: 1rem;
        }

        .hero-btn-full {
            width: 100%;
            justify-content: center;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }



        @media (max-width: 768px) {
            .button-group {
                flex-direction: column;
                align-items: center;
            }

            .hero-btn {
                width: 50%;
                justify-content: center;
            }
        }

        .plan-comparison-wrapper {
            margin: 5rem 0;
        }

        .plan-comparison-wrapper h2 {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
        }

        .plan-comparison-table-wrap {
            overflow-x: auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            scrollbar-width: none;
        }

        .plan-comparison-table-wrap::-webkit-scrollbar {
            display: none;
        }

        .plan-comparison-table {
            width: 100%;
            border-collapse: collapse;
        }

        .plan-comparison-table th,
        .plan-comparison-table td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #E5E7EB;
        }

        .plan-comparison-table th {
            background: #F9FAFB;
            font-weight: 700;
            color: #1F2937;
        }

        .plan-comparison-table td:first-child {
            text-align: left;
            font-weight: 600;
        }

        .plan-comparison-table tr:hover {
            background: #F9FAFB;
        }
    </style>

</head>

<body>

    <!-- header  -->
    <?php include('includes/header.php') ?>

    <!-- Hero Section -->
    <section class="hero" aria-labelledby="hero-title">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Choose Your Learning Path</h1>
                <h2 class="hero-subtitle" style="color: #fff;">3 Powerful Bundles</h2>
                <p class="hero-description">Expert-designed bundles to help you master skills faster and transform your
                    career with comprehensive, hands-on learning experiences.</p>

                
            </div>
    </section>

    <!-- Main Content -->
    <main id="main-content">
        <?php if ($error || $purchaseError): ?>
        <div class="alert alert-danger" role="alert" style="margin: 20px;">
            <?php if ($error): ?>
                <p><strong>Error:</strong> <?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($purchaseError): ?>
                <p><strong>Technical details:</strong> <?= htmlspecialchars($purchaseError) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Bundle Showcase -->
<section id="bundles" class="bundles" aria-labelledby="bundles-title">
    <div class="container">
        <h2 id="bundles-title" class="section-title">Our Premium Learning Bundles</h2>
        <p class="section-subtitle">Carefully curated courses designed by industry experts</p>

        <div class="bundle-slider">
            <div class="bundle-cards" role="region" aria-label="Course bundles">

                <?php
                // Fetch bundles from database using NEW PDO VAR: $pdo
                $bundles = [];

                if ($pdo && isDatabaseAvailable()) {
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM courses WHERE is_bundle = 1 ORDER BY created_at DESC");
                        $stmt->execute();
                        $bundles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        error_log('Bundle fetch success: count=' . count($bundles));
                    } catch (Exception $e) {
                        error_log('Bundle fetch error: ' . $e->getMessage());
                    }
                } else {
                    error_log("Bundle fetch failed: DB unavailable or $pdo empty");
                }

                if (empty($bundles)) : ?>

                    <div class="alert alert-info" role="alert" style="padding:1rem; margin:1rem">
                        <p>No bundles are available right now. Please check back later.</p>
                    </div>

                <?php else: ?>

                    <?php foreach ($bundles as $bundle): 
                        $bid     = htmlspecialchars($bundle['id']);
                        $title   = htmlspecialchars($bundle['title'] ?? "Untitled Bundle");
                        $desc    = htmlspecialchars($bundle['description'] ?? "");
                        $price   = number_format((float)($bundle['price'] ?? 0), 2);
                        $image   = htmlspecialchars($bundle['image_url'] ?: "https://images.pexels.com/photos/3184639/pexels-photo-3184639.jpeg?auto=compress&cs=tinysrgb&w=600");
                        $badge   = htmlspecialchars($bundle['badge'] ?? "");

                        // Features
                        $features = [];
                        if (!empty($bundle['features'])) {
                            $features = array_filter(preg_split("/\r\n|\r|\n/", $bundle['features']), "trim");
                        }
                    ?>

                    <div class="bundle-card" data-bundle="bundle-<?= $bid ?>" role="article" aria-labelledby="bundle-<?= $bid ?>-title">

                        <div class="bundle-image">
                            <img src="<?= $image ?>" alt="<?= $title ?>" loading="lazy">

                            <?php if ($badge): ?>
                                <div class="bundle-badge <?= strtolower(str_replace(' ', '-', $badge)) ?>">
                                    <?= $badge ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="bundle-content">

                            <h3 id="bundle-<?= $bid ?>-title" class="bundle-title"><?= $title ?></h3>
                            <p class="bundle-description"><?= $desc ?></p>

                            <?php if (!empty($features)): ?>
                                <div class="bundle-includes">
                                    <?php foreach ($features as $f): ?>
                                        <div class="include-item">
                                            <span class="include-icon"><i class="fas fa-check"></i></span>
                                            <span><?= htmlspecialchars($f) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="bundle-rating">
                                <div class="stars">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>

                            <div class="bundle-pricing">
                                <span class="price">₹<?= $price ?></span>
                                <span class="price-note">One-time payment</span>
                            </div>

                            <div class="bundle-actions">

                                <!-- FIXED: Correct detail link -->
                                <a class="hero-btn hero-btn-primary hero-btn-large"
                                   href="course-detail.php?id=<?= $bid ?>">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>

                                <?php if (in_array($bid, $purchasedCourses)) : ?>

                                    <a href="user-dashboard/my-courses.php"
                                       class="hero-btn hero-btn-outline hero-btn-large">
                                       View in My Courses
                                    </a>

                                <?php elseif ($loggedIn): ?>

                                    <form method="post" action="purchase-handler.php" style="display:inline-block;">
                                        <input type="hidden" name="course_id" value="<?= $bid ?>">
                                        <input type="hidden" name="price" value="<?= $price ?>">

                                        <button type="submit" class="hero-btn hero-btn-outline hero-btn-large">
                                            Buy Now
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <a href="login-Sign-Up1.php?course_id=<?= $bid ?>&price=<?= $price ?>&redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                                       class="hero-btn hero-btn-outline hero-btn-large">
                                       Buy Now
                                    </a>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

        <!-- Comparison Section -->
        <section id="comparison" class="comparison" aria-labelledby="comparison-title">
            <div class="container">
                
                <!-- Interactive Comparison Cards -->


                <div class="plan-comparison-wrapper">
                    <h2>Feature Comparison</h2>
                    <div class="plan-comparison-table-wrap">
                        <table class="plan-comparison-table">
                            <thead>
                                <tr>
                                    <th>Features</th>
                                    <th>Free</th>
                                    <th>Pro</th>
                                    <th>Premium</th>
                                    <th>Enterprise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Free Courses</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td>Premium Courses</td>
                                    <td>✗</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td>Certificates</td>
                                    <td>Basic</td>
                                    <td>Enhanced</td>
                                    <td>Professional</td>
                                    <td>Custom</td>
                                </tr>
                                <tr>
                                    <td>Community Access</td>
                                    <td>Basic</td>
                                    <td>Full</td>
                                    <td>VIP</td>
                                    <td>Private</td>
                                </tr>
                                <tr>
                                    <td>Mentorship</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>1hr/month</td>
                                    <td>Unlimited</td>
                                </tr>
                                <tr>
                                    <td>Priority Support</td>
                                    <td>✗</td>
                                    <td>✗</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td>Affiliate Program</td>
                                    <td>✗</td>
                                    <td>Basic</td>
                                    <td>Advanced</td>
                                    <td>White-label</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
            <!-- Mentors Section -->
            <section id="mentors" class="mentors">
                <div class="container">
                    <div class="section-header">
                        <h2>Meet Your Expert Mentors</h2>
                        <p>Learn from industry veterans who have worked at top companies and have years of practical
                            experience to share with you.</p>
                    </div>

                    <div class="ment-slider">
                        <div class="ment-cards">
                            <div class="ment-card-1">
                                <div class="ment-image-1">
                                    <img src="https://images.pexels.com/photos/1043471/pexels-photo-1043471.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop"
                                        alt="Suraj Singh">
                                    <div class="ment-badge-1">
                                        <i class="fas fa-award"></i>
                                    </div>
                                </div>
                                <div class="ment-info-1">
                                    <h3>Suraj Singh</h3>
                                    <p class="ment-role-1">Digital Marketing Expert</p>
                                    <p class="ment-experience-1">8+ Years Experience</p>
                                    <p class="ment-company-1">Ex-Google, Meta</p>
                                    <div class="ment-specialties-1">
                                        <span>SEO</span>
                                        <span>SEM</span>
                                        <span>Social Media Marketing</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ment-card-2">
                                <div class="ment-image-2">
                                    <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop"
                                        alt="Jane Smith">
                                    <div class="ment-badge-2">
                                        <i class="fas fa-award"></i>
                                    </div>
                                </div>
                                <div class="ment-info-2">
                                    <h3>Jane Smith</h3>
                                    <p class="ment-role-2">Full Stack Developer</p>
                                    <p class="ment-experience-2">10+ Years Experience</p>
                                    <p class="ment-company-2">Ex-Amazon, Microsoft</p>
                                    <div class="ment-specialties-2">
                                        <span>React</span>
                                        <span>Node.js</span>
                                        <span>Cloud Architecture</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ment-card-3">
                                <div class="ment-image-3">
                                    <img src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop"
                                        alt="Geeta Verma">
                                    <div class="ment-badge-3">
                                        <i class="fas fa-award"></i>
                                    </div>
                                </div>
                                <div class="ment-info-3">
                                    <h3>Geeta Verma</h3>
                                    <p class="ment-role-3">Data Science Mentor</p>
                                    <p class="ment-experience-3">12+ Years Experience</p>
                                    <p class="ment-company-3">Ex-IBM, Oracle</p>
                                    <div class="ment-specialties-3">
                                        <span>Machine Learning</span>
                                        <span>AI</span>
                                        <span>Analytics</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ment-slider-dots" id="mentor-slider-dots" style="display: none;">
                            <span class="ment-dot-1 active" data-slide="0"></span>
                            <span class="ment-dot-2" data-slide="1"></span>
                            <span class="ment-dot-3" data-slide="2"></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Selection CTA -->
           
            </div>
        </section>



        <!-- Trust Section -->
        <section id="guarantee" class="trust">
            <div class="container">
                <div class="trust-content">
                    <div class="payment-partners">
                        <h3>Secure Payment Options</h3>
                        <div class="payment-logos" role="img"
                            aria-label="Accepted payment methods: Visa, Mastercard, PayPal, Stripe">
                            <div class="payment-logo">
                                <span aria-hidden="true"><i class="fas fa-credit-card"></i></span>
                                <span>Visa</span>
                            </div>
                            <div class="payment-logo">
                                <span aria-hidden="true"><i class="fas fa-credit-card"></i></span>
                                <span>Mastercard</span>
                            </div>
                            <div class="payment-logo">
                                <span aria-hidden="true"><i class="fab fa-paypal"></i></span>
                                <span>PayPal</span>
                            </div>
                            <div class="payment-logo">
                                <span aria-hidden="true"><i class="fas fa-lock"></i></span>
                                <span>Stripe</span>
                            </div>
                        </div>
                    </div>



                    <div class="trust-stats" role="region" aria-label="Company statistics">
                        <div class="stat">
                            <div class="stat-number" aria-label="Over 500">500+</div>
                            <div class="stat-label">Happy Students</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number" aria-label="4.9 stars">4.9★</div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number" aria-label="92 percent">92%</div>
                            <div class="stat-label">Career Success</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

  <!-- Testimonials  -->
  <section class="Testi-testimonial-section">
    <h1 style="text-align: center; justify-content: center;">What Our Students Say</h1>
    <div class="Testi-quote-icon">

      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z" />
      </svg>
    </div>

    <p class="Testi-testimonial-text">
      Maecenas consectetur dolor eu finibus molestie. Ut ultricies magna a nunc scelerisque, quis aliquet lacus
      molestie. Nullam nec pharetra quam. Sed vulputate turpis ac purus ultricies fermentum. Nulla vel metus in
      enim aliquam egestas.
    </p>

    <div class="Testi-avatars-wrapper">
      <button class="Testi-nav-button Testi-prev-button" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>

      <div class="Testi-avatars-container">
        <div class="Testi-avatar" data-position="left-2">
          <img src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=150"
            alt="Sarah Johnson">
        </div>
        <div class="Testi-avatar" data-position="left-1">
          <img src="https://images.pexels.com/photos/1222271/pexels-photo-1222271.jpeg?auto=compress&cs=tinysrgb&w=150"
            alt="David Chen">
        </div>
        <div class="Testi-avatar Testi-avatar-active" data-position="center">
          <img src="https://images.pexels.com/photos/733872/pexels-photo-733872.jpeg?auto=compress&cs=tinysrgb&w=150"
            alt="Michelle Andersson">
        </div>
        <div class="Testi-avatar" data-position="right-1">
          <img src="https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=150"
            alt="James Wilson">
        </div>
        <div class="Testi-avatar" data-position="right-2">
          <img src="https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=150"
            alt="Emma Rodriguez">
        </div>
      </div>

      <button class="Testi-nav-button Testi-next-button" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18l6-6-6-6" />
        </svg>
      </button>
    </div>

    <div class="Testi-person-info">
      <h3 class="Testi-person-name">Michelle Andersson</h3>
      <p class="Testi-person-title">TITEL, FÖRETAG</p>
    </div>
  </section>

  <!-- FAQ Section -->
  <div class="faq-container">
    <div class="faq-wrapper">
      <div class="faq-header">
        <h1>Frequently asked <span class="faq-highlight">questions</span></h1>
        <p class="faq-subtitle">Choose a plan that fits your business needs and budget. No hidden fees, no surprises -
          just straightforward pricing for powerful financial management.</p>
      </div>

      <div class="faq-list">
        <div class="faq-item">
          <div class="faq-question">
            <span>What is Nicepay?</span>
            <button class="faq-toggle-btn" aria-label="Toggle answer">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="faq-answer">
            <p>Nicepay is an all-in-one financial management platform designed to simplify payments, automate invoicing,
              track expenses in real-time, and deliver powerful insights to businesses of all sizes.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>How does Nicepay work?</span>
            <button class="faq-toggle-btn" aria-label="Toggle answer">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="faq-answer">
            <p>Nicepay integrates seamlessly with your existing systems to provide comprehensive financial management
              tools, automated workflows, and real-time analytics to help you manage your business finances efficiently.
            </p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>Is Nicepay secure?</span>
            <button class="faq-toggle-btn" aria-label="Toggle answer">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="faq-answer">
            <p>Yes, Nicepay employs bank-level encryption and security protocols to ensure your financial data is
              protected at all times. We comply with industry standards and regulations to keep your information safe.
            </p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-question">
            <span>Can Nicepay integrate with other accounting software?</span>
            <button class="faq-toggle-btn" aria-label="Toggle answer">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="faq-answer">
            <p>Absolutely! Nicepay offers integrations with popular accounting software and business tools, allowing you
              to sync data seamlessly and maintain a unified workflow across all your platforms.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

    <!-- Footer Section -->
  <?php include('includes\footer.php') ?>



<script>
document.addEventListener("DOMContentLoaded", () => {

    /* ========= Scroll Animations ========= */
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("animate-in");
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll("section").forEach(sec => observer.observe(sec));


    /* ========= Slider Base Helper ========= */
    class BaseSlider {
        constructor(containerSelector, cardSelector, slideWidth, autoTime, responsiveWidth) {
            this.currentSlide = 0;
            this.slides = document.querySelectorAll(cardSelector);
            this.sliderContainer = document.querySelector(containerSelector);
            this.autoSlideInterval = null;
            this.slideWidth = slideWidth;
            this.autoTime = autoTime;
            this.responsiveWidth = responsiveWidth;
        }

        init() {
            if (!this.sliderContainer || this.slides.length === 0) return;

            if (window.innerWidth <= this.responsiveWidth) {
                this.startAutoSlide();
                this.addTouchSupport();
            }

            window.addEventListener("resize", () => {
                if (window.innerWidth <= this.responsiveWidth && !this.autoSlideInterval)
                    this.startAutoSlide();
                else
                    this.stopAutoSlide();
            });
        }

        showSlide(index) {
            if (!this.sliderContainer) return;

            index = (index + this.slides.length) % this.slides.length;
            this.sliderContainer.scrollTo({
                left: index * this.slideWidth,
                behavior: "smooth"
            });
            this.currentSlide = index;
        }

        nextSlide() {
            this.showSlide(this.currentSlide + 1);
        }

        startAutoSlide() {
            this.autoSlideInterval = setInterval(() => this.nextSlide(), this.autoTime);
        }

        stopAutoSlide() {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }

        addTouchSupport() {
            let startX = 0;
            this.sliderContainer.addEventListener("touchstart", e => startX = e.touches[0].clientX);
            this.sliderContainer.addEventListener("touchend", e => {
                const diff = startX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) diff > 0 ? this.nextSlide() : this.showSlide(this.currentSlide - 1);
            });
        }
    }


    /* ========= Mentor Slider ========= */
    const mentorSlider = new BaseSlider(".ment-cards", ".ment-card-1, .ment-card-2, .ment-card-3", 300, 4000, 768);
    mentorSlider.init();


    /* ========= Bundle Slider ========= */
    const bundleSlider = new BaseSlider(".bundle-cards", ".bundle-card", 340, 5000, 1200);
    bundleSlider.init();


    /* ========= Comparison Slider ========= */
    const comparisonSlider = new BaseSlider(".comparison-cards", ".comparison-card", 300, 6000, 1210);
    comparisonSlider.init();


    /* ========= Testimonials ========= */
    const testimonials = [
        {
            name: "Sarah Johnson",
            title: "CEO, TECH SOLUTIONS",
            text: "Working with this team has been an absolute game-changer...",
            image: "https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?w=150"
        },
        {
            name: "David Chen",
            title: "DESIGNER, CREATIVE STUDIO",
            text: "The level of innovation and creative thinking brought to our projects...",
            image: "https://images.pexels.com/photos/1222271/pexels-photo-1222271.jpeg?w=150"
        },
        {
            name: "Michelle Andersson",
            title: "TITEL, FÖRETAG",
            text: "Maecenas consectetur dolor eu finibus molestie...",
            image: "https://images.pexels.com/photos/733872/pexels-photo-733872.jpeg?w=150"
        },
        {
            name: "James Wilson",
            title: "DIRECTOR, MARKETING AGENCY",
            text: "Outstanding results delivered consistently on time...",
            image: "https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?w=150"
        },
        {
            name: "Emma Rodriguez",
            title: "FOUNDER, DIGITAL VENTURES",
            text: "An incredible partner who truly understands the digital landscape...",
            image: "https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?w=150"
        }
    ];

    let currentIndex = 2;
    let autoPlayInterval;

    const avatarElements = document.querySelectorAll(".Testi-avatar");
    const textElem = document.querySelector(".Testi-testimonial-text");
    const nameElem = document.querySelector(".Testi-person-name");
    const titleElem = document.querySelector(".Testi-person-title");
    const prevButton = document.querySelector(".Testi-prev-button");
    const nextButton = document.querySelector(".Testi-next-button");

    function getVisibleIndices(center) {
        const t = testimonials.length;
        return [
            (center - 2 + t) % t,
            (center - 1 + t) % t,
            center,
            (center + 1) % t,
            (center + 2) % t
        ];
    }

    function updateTestimonial(index) {
        if (!textElem || !avatarElements.length) return;

        currentIndex = (index + testimonials.length) % testimonials.length;
        const data = testimonials[currentIndex];

        textElem.textContent = data.text;
        nameElem.textContent = data.name;
        titleElem.textContent = data.title;

        const visible = getVisibleIndices(currentIndex);

        avatarElements.forEach((el, pos) => {
            const idx = visible[pos];
            const img = el.querySelector("img");

            img.src = testimonials[idx].image;
            img.alt = testimonials[idx].name;

            el.classList.toggle("avatar-active", pos === 2);
            el.dataset.testimonialIndex = idx;
        });

        resetAutoPlay();
    }

    function nextTestimonial() {
        updateTestimonial(currentIndex + 1);
    }

    function prevTestimonial() {
        updateTestimonial(currentIndex - 1);
    }

    function resetAutoPlay() {
        clearInterval(autoPlayInterval);
        autoPlayInterval = setInterval(nextTestimonial, 5000);
    }

    avatarElements.forEach(avatar => {
        avatar.addEventListener("click", () => {
            const index = parseInt(avatar.dataset.testimonialIndex);
            updateTestimonial(index);
        });
    });

    nextButton?.addEventListener("click", nextTestimonial);
    prevButton?.addEventListener("click", prevTestimonial);

    updateTestimonial(currentIndex);


    /* ========= FAQ ========= */
    const faqItems = document.querySelectorAll(".faq-item");
    const header = document.querySelector(".faq-header");

    if (header) {
        header.style.opacity = "0";
        header.style.transform = "translateY(-30px)";
        setTimeout(() => {
            header.style.opacity = "1";
            header.style.transform = "translateY(0)";
        }, 100);
    }

    faqItems.forEach((item, idx) => {
        const q = item.querySelector(".faq-question");
        const a = item.querySelector(".faq-answer");

        if (!q || !a) return;

        q.addEventListener("click", () => {
            const active = item.classList.contains("active");
            faqItems.forEach(f => f.classList.remove("active"));
            if (!active) item.classList.add("active");
        });

        item.style.opacity = "0";
        item.style.transform = "translateX(30px)";
        item.style.transition = `opacity .6s ${idx * 0.1}s, transform .6s ${idx * 0.1}s`;
    });

    setTimeout(() => {
        faqItems.forEach(item => {
            item.style.opacity = "1";
            item.style.transform = "translateX(0)";
        });
    }, 300);


    /* ========= Hero Button Ripple ========= */
    document.querySelectorAll(".hero-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const ripple = document.createElement("span");
            const rect = btn.getBoundingClientRect();
            ripple.style.cssText = `
                position:absolute; left:${e.clientX - rect.left}px; top:${e.clientY - rect.top}px;
                width:0;height:0;background:rgba(255,255,255,0.5);border-radius:50%;
                transform:scale(0);animation:ripple .6s linear;
            `;
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });

    const rippleStyle = document.createElement("style");
    rippleStyle.textContent = `@keyframes ripple { to { transform: scale(4); opacity: 0; } }`;
    document.head.appendChild(rippleStyle);
});
</script>

</body>
</html>