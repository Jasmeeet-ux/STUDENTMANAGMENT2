<?php
require "db.php";

// Accept both ?id= AND ?course_id=
$course_id = $_GET['id'] ?? $_GET['course_id'] ?? null;

if (!$course_id) {
    die("Course ID not provided.");
}

// ======================== FETCH COURSE ========================
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found.");
}

// ======================== FETCH MODULES ========================
$module_stmt = $pdo->prepare("
    SELECT id, title, module_number, duration 
    FROM modules 
    WHERE course_id = ? 
    ORDER BY module_number ASC
");
$module_stmt->execute([$course_id]);
$modules = $module_stmt->fetchAll();

// ======================== LESSON FETCHER ======================
$lesson_stmt = $pdo->prepare("
    SELECT * FROM lessons 
    WHERE course_id = ? AND module_id = ? 
    ORDER BY 
        CASE WHEN order_no IS NULL OR order_no = 0 THEN 99999 ELSE order_no END ASC,
        lesson_number ASC,
        id ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($course['title']) ?> - Course Details</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .Detail-hero-section {
            background: linear-gradient(90deg, #000000, #EBBE81);
            color: white;
            padding: 110px 0;
            position: relative;
            overflow: hidden;
            animation: gradientShift 10s ease infinite;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .Detail-hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.5;
            }

            50% {
                opacity: 1;
            }
        }

        .Detail-hero-section .container {
            position: relative;
            z-index: 1;
        }

        .breadcrumb-nav {
            animation: slideInLeft 0.6s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .breadcrumb-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-link:hover {
            color: white;
        }

        .breadcrumb-separator {
            margin: 0 10px;
            color: rgba(255, 255, 255, 0.5);
        }

        .breadcrumb-current {
            color: white;
        }

        .Detail-hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.2;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

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

        .Detail-hero-subtitle {
            font-size: 1rem;
            margin-bottom: 15px;
            opacity: 0.9;
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .Detail-rating-section {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .rating-section i {
            color: #ffd700;
            font-size: 1.2rem;
            margin-right: 3px;
            animation: starTwinkle 2s ease-in-out infinite;
            animation-delay: calc(var(--i) * 0.2s);
        }

        .rating-section i:nth-child(1) {
            --i: 0;
        }

        .rating-section i:nth-child(2) {
            --i: 1;
        }

        .rating-section i:nth-child(3) {
            --i: 2;
        }

        .rating-section i:nth-child(4) {
            --i: 3;
        }

        .rating-section i:nth-child(5) {
            --i: 4;
        }

        @keyframes starTwinkle {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .Detail-review-count {
            margin-left: 10px;
            opacity: 0.8;
        }

        .Detail-hero-description {
            margin-bottom: 20px;
            line-height: 1.8;
            animation: fadeInUp 0.8s ease-out 0.5s both;
        }

        .Detail-read-more {
            color: #be8652;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .read-more:hover {
            color: #d4a574;
            text-decoration: underline;
        }

        .Detail-skill-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .Detail-skill-tag {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .skill-tag:nth-child(1) {
            --i: 0;
        }

        .skill-tag:nth-child(2) {
            --i: 1;
        }

        .skill-tag:nth-child(3) {
            --i: 2;
        }

        .skill-tag:nth-child(4) {
            --i: 3;
        }

        .skill-tag:nth-child(5) {
            --i: 4;
        }

        .skill-tag:nth-child(6) {
            --i: 5;
        }

        .skill-tag:nth-child(7) {
            --i: 6;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .skill-tag:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .Detail-skill-tag.Detail-more-skills {
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }

        .Detail-cta-buttons {
            display: flex;
            gap: 15px;
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }

        .Detail-btn-subscribe {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .Detail-btn-subscribe:hover {
            background-color: white;
            color: #1a0b2e;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .Detail-btn-cart {
            background-color: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .Detail-btn-cart:hover {
            background-color: white;
            color: #1a0b2e;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }



        .Detail-whats-included-section {
            padding: 80px 0;
            background-color: white;
        }

        .Detail-section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 40px;
            color: #1a0b2e;
            animation: fadeInUp 0.8s ease-out;
            text-align: center;
        }

        .Detail-feature-card {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease-out both;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .Detail-feature-card:nth-child(1) {
            --i: 0;
        }

        .Detail-feature-card:nth-child(2) {
            --i: 1;
        }

        .Detail-feature-card:nth-child(3) {
            --i: 2;
        }

        .Detail-feature-card:nth-child(4) {
            --i: 3;
        }

        .Detail-feature-card:nth-child(5) {
            --i: 4;
        }

        .Detail-feature-card:nth-child(6) {
            --i: 5;
        }

        .Detail-feature-card:nth-child(7) {
            --i: 6;
        }

        .Detail-feature-card:nth-child(8) {
            --i: 7;
        }

        /* .Detail-feature-card:hover {
  /* transform: translateY(-10px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); }*/


        .Detail-feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .Detail-feature-card:hover .Detail-feature-icon {
            background: linear-gradient(135deg, #be8652 0%, #8b4513 100%);
            color: white;
            transform: scale(1.1) rotate(5deg);
        }

        .Detail-feature-text {
            font-weight: 600;
            color: #374151;
            margin: 0;
        }

        .Detail-course-description-section {
            padding: 80px 0;
            background-color: #fff;
        }

        .Detail-description-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4b5563;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .Detail-course-image img {
            width: 80%;
            height: auto;
            max-width: 380px;
            border-radius: 10px;
            /* box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); */
            transition: transform 0.3s ease;
            margin: auto;
            display: block;
        }

        .Detail-course-image img:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .Detail-courses-section {
            padding: 30px 0;
            background-color: white;
        }

        .Detail-progress {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .Detail-progress-bar {
            background: linear-gradient(90deg, #be8652 0%, #8b4513 100%);
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .Detail-course-info {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .Detail-expand-link {
            color: #be8652;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .Detail-expand-link:hover {
            color: #8b4513;
            gap: 10px;
        }

        .Detail-chapter-accordion {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out both;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .Detail-chapter-accordion:nth-child(1) {
            --i: 0;
        }

        .Detail-chapter-accordion:nth-child(2) {
            --i: 1;
        }

        .Detail-chapter-accordion:nth-child(3) {
            --i: 2;
        }

        .Detail-chapter-accordion:nth-child(4) {
            --i: 3;
        }

        .Detail-chapter-accordion:nth-child(5) {
            --i: 4;
        }

        .Detail-chapter-accordion:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-color: #d1d5db;
        }

        .Detail-chapter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            cursor: pointer;
            background-color: white;
            transition: all 0.3s ease;
        }

        .Detail-chapter-header:hover {
            background-color: #f9fafb;
        }

        .Detail-chapter-header.collapsed i {
            transform: rotate(180deg);
        }

        .Detail-chapter-header i {
            font-size: 1.5rem;
            color: #6b7280;
            transition: transform 0.3s ease;
        }

        .Detail-chapter-meta {
            color: #be8652;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .Detail-chapter-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a0b2e;
            margin: 0;
        }

        .Detail-chapter-content {
            padding: 30px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .Detail-lesson-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            gap: 15px;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            height: 100%;
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .Detail-lesson-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #be8652;
        }

        .Detail-lesson-icon {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #d97706;
            transition: all 0.3s ease;
        }

        .Detail-lesson-card:hover .Detail-lesson-icon {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            transform: scale(1.1) rotate(10deg);
        }

        .Detail-lesson-content {
            flex: 1;
        }

        .Detail-lesson-title {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .Detail-lesson-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1a0b2e;
            margin-bottom: 10px;
        }

        .Detail-lesson-description {
            font-size: 0.9rem;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
        }

        .Detail-lesson-card-small {
            background: white;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            gap: 10px;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            height: auto;
            animation: fadeInUp 0.5s ease-out both;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .Detail-lesson-card-small:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #be8652;
        }

        .Detail-lesson-card-small .Detail-lesson-icon {
            flex-shrink: 0;
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #d97706;
            transition: all 0.3s ease;
        }

        .Detail-lesson-card-small:hover .Detail-lesson-icon {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            transform: scale(1.1) rotate(10deg);
        }

        .Detail-lesson-card-small .Detail-lesson-content {
            flex: 1;
        }

        .Detail-lesson-card-small .Detail-lesson-title {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .Detail-lesson-card-small .Detail-lesson-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1a0b2e;
            margin-bottom: 5px;
        }

        .Detail-lesson-card-small .Detail-lesson-description {
            font-size: 0.8rem;
            color: #6b7280;
            line-height: 1.5;
            margin: 0;
        }

        @media (max-width: 991px) {
            .Detail-hero-title {
                font-size: 2rem;
            }



            .cta-buttons {
                flex-direction: column;
            }

            .cta-buttons .btn {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .Detail-hero-section {
                padding: 110px 0;
            }

            .Detail-hero-title {
                font-size: 1.75rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .Detail-courses-section {
                padding: 20px 0;
            }

            .Detail-chapter-accordion {
                margin-bottom: 10px;
            }

            .Detail-chapter-header {
                padding: 15px 20px;
            }

            .Detail-chapter-title {
                font-size: 1.1rem;
            }

            .Detail-chapter-content {
                padding: 20px;
            }

            .Detail-lesson-card-small {
                padding: 10px;
            }

            .Detail-lesson-card-small .Detail-lesson-title {
                font-size: 0.7rem;
            }

            .Detail-lesson-card-small .Detail-lesson-name {
                font-size: 0.85rem;
            }

            .Detail-lesson-card-small .Detail-lesson-description {
                font-size: 0.75rem;
            }

            .Detail-courses-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

        }

        .Detail-hero-image {
            text-align: center;
            margin-top: 20px;
        }

        .Detail-hero-image img {
            width: 90%;
            height: auto;
            border-radius: 10px;
            /* box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); */
            transition: transform 0.3s ease;
            animation: slideInRight 0.8s ease-out 0.8s both;
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

        .hero-image img:hover {
            transform: scale(1.05);
        }

        /* Testimonial section  */
        .Testi-testimonial-section {
            width: 100%;
            text-align: center;
            /* min-height: 100vh; */
            align-items: center;
            justify-content: center;
            padding: 40px;
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
            margin-bottom: 60px;
            text-align: center;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
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
            font-size: 20px;
            font-weight: 500;
            color: #000;
            margin-bottom: 8px;
        }

        .Testi-person-title {
            font-size: 12px;
            font-weight: 400;
            color: #252525;
            letter-spacing: 1px;
            text-transform: uppercase;
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
                font-size: 12px;
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

        /* FAQ section  */
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

        h1 {
            font-size: 52px;
            font-weight: 700;
            color: #fff;
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
            opacity: 0;
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

        @media (max-width: 968px) {
            .faq-wrapper {
                grid-template-columns: 1fr;
                gap: 48px;
                padding: 48px;
            }

            .faq-header {
                position: static;
            }

            h1 {
                font-size: 40px;
            }

            .subtitle {
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .faq-wrapper {
                padding: 32px 24px;
            }

            h1 {
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
    </style>
</head>

<body>

    <?php include('includes/header.php'); ?>

    <!-- ================= HERO SECTION ================= -->
    <section class="Detail-hero-section">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-lg-6">
                    <h1 class="Detail-hero-title"><?= htmlspecialchars($course['title']) ?></h1>

                    <?php if (!empty($course['subtitle'])): ?>
                        <p class="Detail-hero-subtitle"><?= htmlspecialchars($course['subtitle']) ?></p>
                    <?php endif; ?>

                    <!-- RATING -->
                    <div class="Detail-rating-section mb-3">
                        <?php
                        $fullStars = floor($course['rating']);
                        for ($i = 0; $i < $fullStars; $i++) echo "<i class='fas fa-star'></i>";

                        if ($course['rating'] - $fullStars >= 0.5)
                            echo "<i class='fas fa-star-half-alt'></i>";
                        ?>

                        <span class="Detail-review-count">
                            (<?= (int)$course['review_count'] ?> reviews)
                        </span>
                    </div>

                    <!-- SHORT DESCRIPTION -->
                    <p class="Detail-hero-description">
                        <?= nl2br(htmlspecialchars($course['short_description'])) ?>
                    </p>

                    <!-- SKILLS -->
                    <div class="Detail-skill-tags mb-4">
                        <?php if (!empty($course['skills'])):
                            $skills = array_map('trim', explode(',', $course['skills']));
                            $limit = 6;

                            for ($i = 0; $i < min(count($skills), $limit); $i++):
                                echo "<span class='Detail-skill-tag'>" . htmlspecialchars($skills[$i]) . "</span>";
                            endfor;

                            if (count($skills) > $limit):
                                echo "<span class='Detail-skill-tag Detail-more-skills'>+" . (count($skills) - $limit) . " more</span>";
                            endif;
                        endif; ?>
                    </div>

                    <!-- CTA -->
                    <div class="Detail-cta-buttons">
                        <a href="payment.php?course_id=<?= $course_id ?>&price=<?= $course['price'] ?>" class="btn Detail-btn-subscribe">
                            Subscribe to learn
                        </a>

                        <button class="btn Detail-btn-cart">
                            Add to cart ₹<?= number_format($course['price']) ?>
                        </button>
                    </div>
                </div>

                <div class="col-lg-6 d-none d-lg-block">
                    <div class="Detail-hero-image">
                        <img src="<?= htmlspecialchars($course['hero_thumbnail']) ?>" class="img-fluid" alt="">
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- What's Included Section -->
    <section class="Detail-whats-included-section">
        <div class="container">
            <h2 class="Detail-section-title">What's included</h2>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <p class="Detail-feature-text">One project file</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <p class="Detail-feature-text">3 chapter quizzes</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-tablet-alt"></i>
                        </div>
                        <p class="Detail-feature-text">Access on tablet and phone</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <p class="Detail-feature-text">Certificate of completion</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-infinity"></i>
                        </div>
                        <p class="Detail-feature-text">Lifetime access</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <p class="Detail-feature-text">Downloadable resources</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="Detail-feature-text">Community access</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-6">
                    <div class="Detail-feature-card">
                        <div class="Detail-feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <p class="Detail-feature-text">Design templates</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =============== COURSE DESCRIPTION =============== -->
    <section class="Detail-course-description-section">
        <div class="container">
            <h2 class="Detail-section-title">Course description</h2>

            <div class="row">
                <div class="col-lg-6">
                    <p class="Detail-description-text">
                        <?= nl2br(htmlspecialchars($course['full_description'])) ?>
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="Detail-course-image"> <img src="<?= htmlspecialchars($course['description_image']) ?>" alt="Course Details" class="img-fluid"> </div>
                </div>
            </div>
        </div>
    </section>


    <!-- =============== MODULES + LESSONS =============== -->
    <section class="Detail-courses-section">
        <div class="container">

            <div class="Detail-courses-header mb-4">
                <h2 class="Detail-section-title">Courses in this program</h2>
                <p class="Detail-course-info"><?= count($modules) ?> Chapters</p>
            </div>

            <?php foreach ($modules as $module): ?>
                <div class="Detail-chapter-accordion">

                    <div class="Detail-chapter-header collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#module<?= $module['id'] ?>">

                        <div>
                            <p class="Detail-chapter-meta">
                                <?= htmlspecialchars($module['duration'] ?? "Duration not set") ?>
                            </p>
                            <h3 class="Detail-chapter-title">
                                Chapter <?= $module['module_number'] ?>:
                                <?= htmlspecialchars($module['title']) ?>
                            </h3>
                        </div>

                        <i class="fas fa-chevron-down"></i>
                    </div>

                    <div class="collapse" id="module<?= $module['id'] ?>">
                        <div class="Detail-chapter-content">
                            <div class="row g-2">

                                <?php
                                $lesson_stmt->execute([$course_id, $module['id']]);
                                $lessons = $lesson_stmt->fetchAll();

                                if (!$lessons): ?>

                                    <div class="col-12 text-muted p-3">
                                        No lessons added yet.
                                    </div>

                                <?php endif;

                                foreach ($lessons as $lesson): ?>
                                    <div class="col-md-4">
                                        <div class="Detail-lesson-card-small">
                                            <div class="Detail-lesson-icon">
                                                <?php if ($lesson['lesson_type'] === 'free' && !empty($lesson['video_url'])): ?>
                                                    <a href="<?= htmlspecialchars($lesson['video_url']) ?>" target="_blank" title="Watch Preview">
                                                        <i class="fas fa-play-circle"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <i class="fas fa-lock" title="Buy to unlock"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="Detail-lesson-content">
                                                <h4 class="Detail-lesson-title">
                                                    Lesson <?= $lesson['lesson_number'] ?>
                                                </h4>
                                                <h5 class="Detail-lesson-name">
                                                    <?= htmlspecialchars($lesson['title']) ?>
                                                </h5>
                                                <p class="Detail-lesson-description">
                                                    <?= htmlspecialchars($lesson['description']) ?>
                                                </p>
                                                <?php if ($lesson['lesson_type'] !== 'free'): ?>
                                                    <div class="text-danger small mt-2">
                                                        Locked. <a href="payment.php?course_id=<?= $course_id ?>&price=<?= $course['price'] ?>">Buy to unlock</a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <!-- Testimonial Section  -->
    <section class="Testi-testimonial-section">
        <h1 style="text-align: center; justify-content: center; color: black;">What Our Students Say</h1>
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

    <!-- FAQ Section  -->
    <div class="faq-container">
        <div class="faq-wrapper">
            <div class="faq-header">
                <h1 style="color: black;">Frequently asked <span class="faq-highlight">questions</span></h1>
                <p class="faq-subtitle">Choose a plan that fits your business needs and budget. No hidden fees, no surprises - just straightforward pricing for powerful financial management.</p>
            </div>

            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What is Nicepay?</span>
                        <button class="faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Nicepay is an all-in-one financial management platform designed to simplify payments, automate invoicing, track expenses in real-time, and deliver powerful insights to businesses of all sizes.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>How does Nicepay work?</span>
                        <button class="faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Nicepay integrates seamlessly with your existing systems to provide comprehensive financial management tools, automated workflows, and real-time analytics to help you manage your business finances efficiently.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Is Nicepay secure?</span>
                        <button class="faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, Nicepay employs bank-level encryption and security protocols to ensure your financial data is protected at all times. We comply with industry standards and regulations to keep your information safe.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can Nicepay integrate with other accounting software?</span>
                        <button class="faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely! Nicepay offers integrations with popular accounting software and business tools, allowing you to sync data seamlessly and maintain a unified workflow across all your platforms.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include('includes\footer.php') ?>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initAccordionAnimations();
            initScrollAnimations();
            initInteractiveElements();
            initExpandAll();
            initLessonIcons();
        });

        function initAccordionAnimations() {
            const accordionHeaders = document.querySelectorAll('.Detail-chapter-header');

            accordionHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const chevron = this.querySelector('i');

                    if (this.classList.contains('collapsed')) {
                        chevron.classList.remove('fas fa-chevron-down');
                        chevron.classList.add('fas fa-chevron-up');
                    } else {
                        chevron.classList.remove('fas fa-chevron-up');
                        chevron.classList.add('fas fa-chevron-down');
                    }
                });
            });
        }

        function initExpandAll() {
            const expandLink = document.querySelector('.Detail-expand-link');
            const collapses = document.querySelectorAll('.collapse');
            const headers = document.querySelectorAll('.Detail-chapter-header');
            let isAllExpanded = false;

            if (expandLink) {
                expandLink.addEventListener('click', (e) => {
                    e.preventDefault();

                    if (!isAllExpanded) {
                        // Expand all
                        collapses.forEach(collapse => {
                            collapse.classList.add('show');
                        });
                        headers.forEach(header => {
                            header.classList.remove('collapsed');
                            const chevron = header.querySelector('i');
                            chevron.classList.remove('fas fa-chevron-down');
                            chevron.classList.add('fas fa-chevron-up');
                        });
                        expandLink.innerHTML = 'Collapse all chapters <i class="fas fa-arrow-left"></i>';
                        isAllExpanded = true;
                    } else {
                        // Collapse all
                        collapses.forEach(collapse => {
                            collapse.classList.remove('show');
                        });
                        headers.forEach(header => {
                            header.classList.add('collapsed');
                            const chevron = header.querySelector('i');
                            chevron.classList.remove('fas fa-chevron-up');
                            chevron.classList.add('fas fa-chevron-down');
                        });
                        expandLink.innerHTML = 'Expand all chapters <i class="fas fa-arrow-right"></i>';
                        isAllExpanded = false;
                    }
                });
            }
        }

        function initScrollAnimations() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                    }
                });
            }, observerOptions);

            const animatedElements = document.querySelectorAll(
                '.Detail-feature-card, .lesson-card, .chapter-accordion, .Detail-description-text, .Detail-section-title'
            );

            animatedElements.forEach(el => {
                observer.observe(el);
            });
        }

        function initInteractiveElements() {
            const buttons = document.querySelectorAll('.Detail-btn-subscribe, .Detail-btn-cart');
            buttons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    createRippleEffect(e, btn);
                });
            });

            const skillTags = document.querySelectorAll('.Detail-skill-tag');
            skillTags.forEach(tag => {
                tag.addEventListener('click', (e) => {
                    e.preventDefault();
                    tag.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        tag.style.transform = '';
                    }, 150);
                });
            });

            const lessonCards = document.querySelectorAll('.lesson-card');
            lessonCards.forEach((card, index) => {
                card.style.setProperty('--i', index % 3);
            });

            const featureCards = document.querySelectorAll('.Detail-feature-card');
            featureCards.forEach((card, index) => {
                card.style.setProperty('--i', index);
            });
        }

        function createRippleEffect(e, button) {
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    width: ${size}px;
    height: ${size}px;
    left: ${x}px;
    top: ${y}px;
    transform: scale(0);
    animation: ripple 0.6s ease-out;
    pointer-events: none;
  `;

            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        }

        const style = document.createElement('style');
        style.textContent = `
  @keyframes ripple {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  .animate-in {
    animation: fadeInUp 0.6s ease-out forwards;
  }
`;
        document.head.appendChild(style);

        // Testimonial Section //
        const testimonials = [{
                name: "Sarah Johnson",
                title: "CEO, TECH SOLUTIONS",
                text: "Working with this team has been an absolute game-changer for our business. Their attention to detail and commitment to excellence is unmatched. Every project milestone was met with professionalism and creativity that exceeded our expectations.",
                image: "https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=150"
            },
            {
                name: "David Chen",
                title: "DESIGNER, CREATIVE STUDIO",
                text: "The level of innovation and creative thinking brought to our projects is remarkable. They understand the perfect balance between aesthetics and functionality, delivering solutions that not only look great but work flawlessly.",
                image: "https://images.pexels.com/photos/1222271/pexels-photo-1222271.jpeg?auto=compress&cs=tinysrgb&w=150"
            },
            {
                name: "Michelle Andersson",
                title: "TITEL, FÖRETAG",
                text: "Maecenas consectetur dolor eu finibus molestie. Ut ultricies magna a nunc scelerisque, quis aliquet lacus molestie. Nullam nec pharetra quam. Sed vulputate turpis ac purus ultricies fermentum. Nulla vel metus in enim aliquam egestas. Morbi ",
                image: "https://images.pexels.com/photos/733872/pexels-photo-733872.jpeg?auto=compress&cs=tinysrgb&w=150"
            },
            {
                name: "James Wilson",
                title: "DIRECTOR, MARKETING AGENCY",
                text: "Outstanding results delivered consistently on time and within budget. Their strategic approach and deep understanding of market trends helped us achieve remarkable growth. The collaboration was seamless from start to finish.",
                image: "https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=150"
            },
            {
                name: "Emma Rodriguez",
                title: "FOUNDER, DIGITAL VENTURES",
                text: "An incredible partner who truly understands the digital landscape. Their innovative solutions and forward-thinking approach have transformed our online presence. The results speak for themselves with measurable improvements across all metrics.",
                image: "https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=150"
            }
        ];

        let currentIndex = 2;
        let autoPlayInterval;

        const avatarElements = document.querySelectorAll('.Testi-avatar');
        const testimonialText = document.querySelector('.Testi-testimonial-text');
        const personName = document.querySelector('.Testi-person-name');
        const personTitle = document.querySelector('.Testi-person-title');
        const prevButton = document.querySelector('.Testi-prev-button');
        const nextButton = document.querySelector('.Testi-next-button');

        function getVisibleIndices(centerIndex) {
            const total = testimonials.length;
            return [
                (centerIndex - 2 + total) % total, // left-2
                (centerIndex - 1 + total) % total, // left-1
                centerIndex, // center
                (centerIndex + 1) % total, // right-1
                (centerIndex + 2) % total // right-2
            ];
        }

        function updateTestimonial(index) {
            currentIndex = index;
            const testimonial = testimonials[index];

            testimonialText.textContent = testimonial.text;
            personName.textContent = testimonial.name;
            personTitle.textContent = testimonial.title;

            const visibleIndices = getVisibleIndices(currentIndex);

            avatarElements.forEach((avatar, position) => {
                const testimonialIndex = visibleIndices[position];
                const testimonialData = testimonials[testimonialIndex];

                const img = avatar.querySelector('img');
                img.src = testimonialData.image;
                img.alt = testimonialData.name;

                if (position === 2) {
                    avatar.classList.add('avatar-active');
                } else {
                    avatar.classList.remove('avatar-active');
                }

                avatar.dataset.testimonialIndex = testimonialIndex;
            });

            resetAutoPlay();
        }

        function nextTestimonial() {
            const nextIndex = (currentIndex + 1) % testimonials.length;
            updateTestimonial(nextIndex);
        }

        function prevTestimonial() {
            const prevIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
            updateTestimonial(prevIndex);
        }

        function resetAutoPlay() {
            clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(nextTestimonial, 5000);
        }

        avatarElements.forEach((avatar) => {
            avatar.addEventListener('click', () => {
                avatar.style.transform = 'scale(0.9)';
                avatar.style.boxShadow = '0 0 20px rgba(190, 134, 82, 0.5)';
                setTimeout(() => {
                    avatar.style.transform = '';
                    avatar.style.boxShadow = '';
                    const testimonialIndex = parseInt(avatar.dataset.testimonialIndex);
                    updateTestimonial(testimonialIndex);
                }, 200);
            });
        });

        nextButton.addEventListener('click', nextTestimonial);
        prevButton.addEventListener('click', prevTestimonial);

        updateTestimonial(currentIndex);

        autoPlayInterval = setInterval(nextTestimonial, 5000);

        // FAQ Section //
        document.addEventListener('DOMContentLoaded', () => {
            const faqItems = document.querySelectorAll('.faq-item');
            const header = document.querySelector('.faq-header');

            if (header) {
                header.style.opacity = '0';
                header.style.transform = 'translateY(-30px)';
                header.style.transition = 'opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)';

                setTimeout(() => {
                    header.style.opacity = '1';
                    header.style.transform = 'translateY(0)';
                }, 100);
            }

            faqItems.forEach((item, index) => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');

                question.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');

                    faqItems.forEach(faqItem => {
                        faqItem.classList.remove('active');
                    });

                    if (!isActive) {
                        item.classList.add('active');

                        setTimeout(() => {
                            answer.style.animation = 'fadeInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards';
                        }, 50);
                    } else {
                        answer.style.animation = '';
                    }
                });

                item.style.opacity = '0';
                item.style.transform = 'translateX(30px)';
                item.style.transition = `opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s, transform 0.6s cubic-bezier(0.4, 0, 0.2, 1) ${index * 0.1}s`;
            });

            setTimeout(() => {
                faqItems.forEach(item => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                });
            }, 300);

            const observerOptions = {
                threshold: 0.15,
                rootMargin: '0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                        entry.target.classList.add('animated');
                    }
                });
            }, observerOptions);

            faqItems.forEach(item => {
                observer.observe(item);
            });
        });

        function initLessonIcons() {
            const lessonIcons = document.querySelectorAll('.Detail-lesson-icon');
            const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
            const lessonVideo = document.getElementById('lessonVideo');

            lessonIcons.forEach(icon => {
                icon.addEventListener('click', () => {
                    videoModal.show();
                    lessonVideo.play();
                });
            });

            document.getElementById('videoModal').addEventListener('hidden.bs.modal', () => {
                lessonVideo.pause();
                lessonVideo.currentTime = 0;
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="script.js"></script>
</body>

</html>