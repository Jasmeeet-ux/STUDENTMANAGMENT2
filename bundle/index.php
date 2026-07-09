<?php
session_start();
// Include database connection with graceful error handling
require 'db.php';

// Define base URL for consistent path resolution
$baseUrl = '/bundle/';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Culture of Internet - Transform Your Career</title>
    <link rel="stylesheet" href="Home_Page.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }

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
            padding: 16px 32px;
            font-size: 1.1rem;
        }

        .hero-btn-full {
            width: 100%;
            justify-content: center;
        }


        /* Hero Section */
        .hero-home {
            padding: 110px 0 20px;
            background: -moz-linear-gradient(left, #000000 0, #EBBE81 100%);
            background: -webkit-linear-gradient(left, #000000 0, #EBBE81 100%);
            background: linear-gradient(to right, #000000 0%, #EBBE81 100%);
            min-height: 90vh;
            display: flex;
            align-items: center;
        }

        .hero-container {
            max-width: 95%;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .hero-highlight {
            color: #be8652;
        }

        .hero-text p {
            font-size: 1.2rem;
            color: white;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .hero-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
            color: #ffffff;
        }

        .hero-youtube-icon {
            color: #ff0000;
            font-size: 2rem;
        }

        .hero-image img {
            width: 100%;
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            animation: step 6s ease-out infinite;
            transform: translateY();
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) scale(1);
            }

            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        /* Section Styles */
        section {
            padding: 60px 0;
        }

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
            max-width: 95%;
            margin: 0 auto;
        }

        /* About Section  */
        .about-container {
            max-width: 95%;
            margin: 0 auto;
            min-height: 100vh;
            padding: 4rem 1rem;
            overflow-x: hidden;
        }

        .about-content {
            animation: fadeInUp 0.8s ease-out;
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

        /* Header Section */
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
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

        .about-subtitle {
            color: #be8652;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .about-title {
            font-size: clamp(2rem, 5vw, 2.5rem);
            font-weight: 700;
            color: #2c2c2c;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            animation: slideDown 0.8s ease-out;
        }

        .about-description {
            color: #000;
            max-width: 42rem;
            margin: 0 auto;
            line-height: 1.7;
            font-size: 1rem;
            animation: fadeIn 1.2s ease-out;
        }

        /* Values Card */
        .about-values-card {
            background: linear-gradient(to right, #9e7249);
            border-radius: 1.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow:
                0 20px 40px rgba(190, 134, 82, 0.2),
                0 10px 20px rgba(190, 134, 82, 0.15);
            animation: scaleIn 0.8s ease-out 0.2s both;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .about-values-card:hover {
            transform: translateY(-5px);
            box-shadow:
                0 25px 50px rgba(190, 134, 82, 0.2),
                0 15px 25px rgba(190, 134, 82, 0.501);
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .about-values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 2rem;
        }

        .about-value-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            animation: fadeInScale 0.6s ease-out backwards;
        }

        .about-value-item:nth-child(1) {
            animation-delay: 0.3s;
        }

        .about-value-item:nth-child(2) {
            animation-delay: 0.4s;
        }

        .about-value-item:nth-child(3) {
            animation-delay: 0.5s;
        }

        .about-value-item:nth-child(4) {
            animation-delay: 0.6s;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .about-icon {
            width: 2.5rem;
            height: 2.5rem;
            margin-bottom: 0.75rem;
            transition: transform 0.3s ease;
        }

        .about-value-item:hover .about-icon {
            transform: scale(1.15) rotate(5deg);
        }

        .about-value-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Cards Grid */
        .about-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .about-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 2rem;
            border: 1px solid rgba(200, 200, 200, 0.3);
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.08),
                0 5px 15px rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.8s ease-out backwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .about-card:nth-child(1) {
            animation-delay: 0.4s;
        }

        .about-card:nth-child(2) {
            animation-delay: 0.5s;
        }

        .about-card:hover {
            transform: translateY(-8px);
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.12),
                0 8px 20px rgba(0, 0, 0, 0.08);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .about-card-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .about-card-icon {
            width: 1.25rem;
            height: 1.25rem;
            color: #be8652;
            transition: transform 0.3s ease;
        }

        .about-card:hover .about-card-icon {
            transform: scale(1.2);
        }

        .about-card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c2c2c;
        }

        .about-card-text {
            color: #000;
            line-height: 1.7;
            font-size: 0.95rem;
        }

        /* CTA Button */
        .about-cta-container {
            text-align: center;
            animation: fadeIn 1s ease-out 0.8s both;
        }

        .about-cta-button {
            background: #9e7249;
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            box-shadow:
                0 10px 25px rgba(190, 134, 82, 0.3),
                0 5px 10px rgba(190, 134, 82, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .about-cta-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .about-cta-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .about-cta-button:hover {
            background: #8b5a3c;
            transform: translateY(-3px);
            box-shadow:
                0 15px 35px rgba(190, 134, 82, 0.4),
                0 8px 15px rgba(190, 134, 82, 0.25);
        }

        .about-cta-button:active {
            transform: translateY(-1px);
            box-shadow:
                0 8px 20px rgba(190, 134, 82, 0.3),
                0 4px 8px rgba(190, 134, 82, 0.2);
        }

        .about-cta-button span {
            position: relative;
            z-index: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {

            .about-values-card {
                padding: 1.5rem 1.5rem;
            }

            .about-values-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.5rem;
            }

            .about-cards-grid {
                gap: 1.5rem;
            }

            .about-card {
                padding: 1.5rem;
            }
        }

        /* Why Choose section */
        :root {
            --color-text-primary: #1a1a1a;
            --color-text-secondary: #6b7280;
            --color-background: #ffffff;
            --color-card-bg: #f9fafb;
            --color-badge: #f3f4f6;
            --color-border: #e5e7eb;
            --spacing-unit: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .wcci-container {
            max-width: 95%;
            width: 100%;
            margin: 0 auto;
            padding: 0 20px;
            animation: fadeIn 0.8s ease-out;

        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wcci-hero-section {
            margin-bottom: calc(var(--spacing-unit) * 8);
            animation: slideInFromLeft 0.8s ease-out;
        }

        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .wcci-badge {
            display: inline-block;
            padding: calc(var(--spacing-unit) * 1.5) calc(var(--spacing-unit) * 3);
            background-color: var(--color-badge);
            color: var(--color-text-secondary);
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: calc(var(--spacing-unit) * 4);
            transition: var(--transition);
        }

        .wcci-badge:hover {
            transform: scale(1.05);
            background-color: var(--color-border);
        }

        .wcci-main-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: calc(var(--spacing-unit) * 3);
            color: var(--color-text-primary);
            letter-spacing: -0.02em;
        }


        .wcci-subtitle {
            font-size: clamp(1rem, 2vw, 1.125rem);
            color: var(--color-text-secondary);
            line-height: 1.8;
            /* max-width: 480px; */
            font-weight: 400;
        }

        .wcci-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: calc(var(--spacing-unit) * 3);
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

        .wcci-feature-card {
            /* background-color: var(--color-card-bg); */
            background: #be865241;
            /* background: #be865241; */
            border-radius: calc(var(--spacing-unit) * 2);
            padding: calc(var(--spacing-unit) * 3);
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid transparent;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .wcci-feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--color-border);
            /* background-color: var(--color-background); */
            /* background: #be865241; */
            background: #be8652;
        }

        .wcci-feature-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .wcci-feature-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .wcci-feature-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .wcci-feature-card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .wcci-icon-wrapper {
            width: 56px;
            height: 56px;
            background-color: var(--color-background);
            border-radius: calc(var(--spacing-unit) * 1.5);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: calc(var(--spacing-unit) * 3);
            transition: var(--transition);
            border: 1px solid var(--color-border);
        }

        .wcci-feature-card:hover .wcci-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
        }

        .wcci-feature-icon {
            width: 28px;
            height: 28px;
            color: var(--color-text-primary);
            transition: var(--transition);
        }

        .wcci-feature-card:hover .wcci-feature-icon {
            color: #be8652;
        }

        .wcci-feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: calc(var(--spacing-unit) * 2);
            color: var(--color-text-primary);
            letter-spacing: -0.01em;
        }

        /* .wcci-feature-title:hover {
  color: #be8652;
} */

        .wcci-feature-description {
            font-size: 0.9375rem;
            /* color: var(--color-text-secondary); */
            color: black;
            line-height: 1.7;
            font-weight: 400;
        }

        @media (max-width: 768px) {

            .wcci-hero-section {
                margin-bottom: calc(var(--spacing-unit) * 6);
            }

            .wcci-main-title {
                font-size: 2rem;
            }

            .wcci-subtitle {
                font-size: 1rem;
            }

            .wcci-features-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: calc(var(--spacing-unit) * 2);
            }

            .wcci-feature-card {
                padding: calc(var(--spacing-unit) * 1.5);
            }
        }

        @media (max-width: 480px) {

            .wcci-badge {
                font-size: 11px;
                padding: calc(var(--spacing-unit) * 1.25) calc(var(--spacing-unit) * 2.5);
            }

            .wcci-main-title {
                font-size: 1.75rem;
            }

            .wcci-features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .wcci-feature-card {
                padding: calc(var(--spacing-unit) * 1.5);
            }

            .wcci-icon-wrapper {
                width: 48px;
                height: 48px;
            }

            .wcci-feature-icon {
                width: 24px;
                height: 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Course Bundle  */
        .bundle-container{
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f7ff 50%, #fff5f5 100%);
            /* background-image:
                radial-gradient(circle at 25% 25%, rgba(0,255,136,0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255,102,0,0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(0,102,204,0.1) 0%, transparent 50%),
                linear-gradient(rgba(255,255,255,0.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.3) 1px, transparent 1px);
            background-size: 200px 200px, 200px 200px, 200px 200px, 60px 60px, 60px 60px; */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 95px 20px;
            position: relative;
        }

        .bundle-container {
            display: grid;
            grid-template-columns: repeat(3, 320px);
            gap: 40px;
            position: relative;
        }

        .bundle-card {
            background: linear-gradient(145deg, #ffffff 0%, #dfdede 100%);
            border-radius: 20px;
            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.08),
                0 4px 8px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            overflow: visible;
            position: relative;
            height: 450px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(0);
        }

        .bundle-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.15),
                0 10px 20px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.9),
                0 0 20px rgba(27, 27, 27, 0.4);
            animation: bundle-pulse 2s infinite;
        }

        @keyframes bundle-pulse {
            0% {
                box-shadow:
                    0 20px 40px rgba(0, 0, 0, 0.15),
                    0 10px 20px rgba(0, 0, 0, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.9),
                    0 0 20px rgba(27, 27, 27, 0.4);
            }

            50% {
                box-shadow:
                    0 20px 40px rgba(0, 0, 0, 0.15),
                    0 10px 20px rgba(0, 0, 0, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.9),
                    0 0 30px rgba(27, 27, 27, 0.6);
            }

            100% {
                box-shadow:
                    0 20px 40px rgba(0, 0, 0, 0.15),
                    0 10px 20px rgba(0, 0, 0, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.9),
                    0 0 20px rgba(27, 27, 27, 0.4);
            }
        }

        .bundle-card-header {
            color: white;
            margin-top: -15px;
            padding: 80px 20px 60px;
            text-align: center;
            position: absolute;
            top: -65px;
            left: 20px;
            right: 20px;
            border-radius: 15px 15px 0 0 ;
            /* border-radius: 15px; */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            z-index: -1;
        }

        .bundle-card-alpha .bundle-card-header {
            background-image: url('data:image/png;base64,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');
            background-size: cover;
            background-position: center;
        }

        .bundle-card-beta .bundle-card-header {
            background-image: url('data:image/png;base64,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');
            background-size: cover;
            background-position: center;
        }

        .bundle-card-gamma .bundle-card-header {
            background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ4AAACUCAMAAABV5TcGAAABelBMVEU2lpSyw+v///81k5EABUP76dwzmpU0kI9PbIVNc4c+W206d39XYYFOtOgAAEIqk45vqLYAAD7/mYWuwudlbpj/8+NmO3U3hYozi4lQnqKIl79NipdCRGBuW3mbudgAADvA1NRaVn5iVYNmeJ9sbZ1UhZqYvL5Xlpjs8/N1apcAeHX64tFWi7x5qqng6+sAcG6q0OxaOXAvCkNbZZaIweJooJ/R4OBiQ3c4EUKOfIcSPG0AADMmLVkKG0ety8uMsrIhVGpAmM+gstiehZs1J1iaj54HF0qNtM1IL1F8pau3ytIAYF1JiYcYQF4mYXEWLFGBUWCkZWpsRlp7WWrsj4K2c3VYPFdaYHTUhH9RKlGDlY+UY3Vlf4TomIfDl4v9tKCkiop/nLvUmYovmKvVzbyMi6TZ3NGksrianJbIvtorhb3Dvca0rL1lpdLSrbPp4eFARndcfLHS2On/zbt6UnoAL2kAF2AABFMgXYE5YpQAACgAABaAwtYqd5512wSvAAAPiklEQVR4nO2cjV/bxhnHXcn2QcCvwfMJUiwHkC4iJycLUlOSSJA1kBGbdG3Spd3SlmaFtgkjzZqNZu3/vue5O9nmzaYrL0by7/OJbWT5ovv6eb0TpFJDDTXUUEMNNdRQQw0Vb+lET6WIrovXOlHHQPq5XtY5STe4p+ucGwjF4x5BGLbHQ89OIg8SaCa1Nc2nQMHVmAMWwk0N5HNy3hd39qKWxMEMAi8RBwmZprlAJoE49AiHFjjURxzUY5ob2nbIneR5SweHSzlYBWvQAOzCodSBf4nj0cHBwgAfG00XDlBihJzLAJsktXFArHDxoQGvLYdQZANmQs/7As9WbRx/xlyyhtbBtIDq+3HoyTCTNo4pF6zCwtjha75NCcSOAF/Is6BWSwSQNo5m6Pq2hZklhCxDKBXPFHMtwPCCwEhCmdrB4ThOQ+BoQhHmWyE+CuPQCcVKxKIJsI82DvAPAu7BGoTaoigFGh5mWqABGThwgyTgSFFuhgDAJATQhGYAVSn4ien7ptWUrkKhdvepD44TXxwYGcW3TQjMmtg2zhVmjo2sTsFjbHjA93XimJrLKSSZvTiiAeIgHW1AWD/08oBGtfPqWdfhTfWSOKHGLCdk9l4c4pyYpBuYCwlMfoxoAAkXQ6pj+oR0nY0wQtOiJA48YDLQpx0nWQhX0cKGx8LuqcMAto/B1okBDkXDNTWj37cLLqVpJhiH2+0rigYLtBikGzR0hg29w0ynd7bQRe4NG5zt8QtwNbAZs/rIZX0GGHxB3HR8QcNjfu/ZYIiBkqNJfWEceuewBY4ChZvGLnz2xSJTYz51GqaGOHpMRxoHb4RauJcGrhDZToNDEUsu9pIZ+r3IFWAce2Z5+KmuZjabDBdAOjgodHkuh6Le1fqY1+ALjANaVsuhFKor2tPWhXFoQcNne1wFj7oWpQ50OPYFdxZdx8mY2LAy0+5dN0DkQDOwNK8Lm7SuwHaozaDFwxGkzuTyT1rS/j0wDsMWlWdPX4HezQw1PLvbOFxpXbaHbR+MQAq8cAwjgaL+hCZxYtJxmcv3HSQh59J1iV11hWhIcIWQ+Rp3unFAf4vrIDgAIarAh7Tt0cNNpHN0EG0IkoKLjTvahZxMxwnal6sTnePPsvIMH9kM69doADAO6HvlAPgJQl2NeppB5YFosGhUwg3RGsFwRugNVqQRFYMLxqE8nhBPXCxetm1Zhvq6wZ9CmJyoXXkDaGhmtGUrFj/MJu2MACOGjo3bNEYQEF1CMjw1LARsn0pqpsZYMFA8dMwnuIsi2nlxiaLM1oXVsIB6nAgf0aDTJ2J5sEGZ6be7G9HDQDFLVedLqG0xRqHrtRyIuxpFwIIjUcNiZ0SU49mWO0j2Ib9w1owmAxfLPPE9o8UHthm4Gqd45ZiCKWLxHN/0NNdTqQWSjYsHxcKQsCkscClUdNwRa6s0dE1M5UBMDAvlK6HC0nC/wjcH6cYAeXkmbePgUFx6GAUw+9rYvEJlRuEkQEZx0dRtBi4ECwyd6iMhw/VUGSSEeYFjOY/gGICBUhfCqolNgGXbFPzGgbjLMWzDIeYEjFN7YHayYDJNHy8vWvfBVs51ofzARY2gofZoKTeZhscghASB3wyBFKVeSJR7YZggagD4zgMehAA5gA+HWKgyCzd7mYuriY54xxElG4zFgLUZDkp6iZoNGQYJhwRjhz56ArYmXsMSfS71zIBhdvVgUoHvPdLQ8qkf6lGyaVIZcTl38NOODaMGMBCzH4F7wQHT8rj4lM08fHbEYjSDjOZYvjcwOKKaQUzG88FM0D5MiibtNjzXZ1rToUHQBLNwTAZuwTBpwAM1TZFIMOK6KjFRlzfFZOHjPm7tBo3QhzTeCHz7kSUitmkiMPREsBoPWmDuH2cJ7mwkells2wSOAKxC9B3caTLRmgTw3VLo7GyIjLixAiebzYYwnIDJOkvsMUgc8FHi4WKIA0idBkZlz63CQCHYhwNW6ECs8JA0rhEEmgkn2u4ArSeKSCpTCYIRW20Mv14X/T5gYNJug+JKCC7uOI1QdPFgDvDKkiaF6UN0MJijoLuHWOtAUsL+GNpkZjt4Z4QtQgWQAFYNjisjGJZC7BqDwaGREsnfFY0GBhEuNh7hkk2Gq2OMg1GYDRe9BiZVRR+AqNjAm18gWygcltygItj6hPj98xADDVVLII7lmgDIEbcCuIGoRTSrAWQAGQ18Olg4LLEdLWhg8kTPhtQZQhR1Q7EeZLJqyOARvlwIqngKRkFRd+ipCAdIpFUQRBis+ZGsRcVNMvgAw1uO6UOMZRitGnjAbppunxb6bCWKIfgKKUYAbM4puLjJxdoFh8P4HTIPvKghbJ0quRhLiUytGEpxAMw6goLjebKdo7T9AShSAScEDs7MalVkK7wbwIWYMkg3AuBWU8As25PpEG9agJiJNKK5hCYkC/gywSKinhXKD4srG8dFZwuqV89CRO3Zy96WRM/oSeA5vtd0TYqpF1cDqGUGttNzQeGshRG0aTKX4W60IyOA73NJgggoToOzqlgYjto8+bW3d+mQB2RNSUM0bKovFud22jo3aIKxcWFwdsR7oGjI2UBTgYtZqsWndpN2dafwflPzofxQwUIgJLSrcYczuC82949cCBMn2SK+QCXDsEoVIw8aDcXDVu4uv0e5CCTfFT/DbEV0jHqtPTMWZ1AxwNGrpHIcPA99kUf/1+Ct/qSiC5XWr6vVifa77YnsSYj6kQMc9b9IJ+rEFuVMpzOn3yN5od3LVvr+t2lPu+4eIFutZrOHn6R30A5ST39Afcx2H67DB1Duo1dLtdbSUbdaqmjUc6xBUG+z7b9PoN7Orqdr+Xy+lF4/ch/uQu85RDruBFr5fBpUyld//1gXX6RVK5VKyGMpITPuqerjj//y9BPgkX98sXetT0Zjnz4pl59+VoNwOsQBYeGvz8pPnj775JPP18/7UgZB2S/+Vi4/K5fLT/4+jB0g43lZ6GmPzJIgEYXj4yEOVPbLcvkrwPF1QiOpQapVw+j87Enz+PL8ruiUVThUqkszpjc2JjdWi52zv/jyG8DxjX1OV3vaMoqHyZ5SweE9qS770JvA47kHyOKYXFYvH6bpyRkRHQrTEkdkHjpQoF8DjrkXnPMUlR2sfmjHfxFlqPnu1+TEFM6xYMgfpwt4cqFgWP/45fpMufzt8vLm/Obm1gxqZGQqLjz64Eh99/33bW8p8M3MfGZh+YeNyR8QRyaTeTkxC6qPV2PCow+O7Nfl54JHEcwjyGTm5yvLuevu9A+5ZfgBeLyanZiYqNdHk4GjUFY4LhdSZgUJAI7cwnYuwlH5djZBOLLflf8pD6Q40EAEiCOHOJBGprID7lKvjyUDRyqVeSMPrJqCxry/nFu40cGB5pGc2AE4dtSRjDSOtev7cOwkCYduZt7IUmQH5r4zX5leXt6DA8zjFuA473mckPriCCuZnbWisbazk9EY0yobue0be3Gsva6PxKWj6+ssxmYms+mlcN74W9iV7dz2G8CxnUMcgsgm4DjnWZyY+uJIYQj1zEwkwMFu5JaXc/MZhaPyfn0kJqGjPw7dQgOIYMy7EsePP+bmQfLgq9dxKTuOYR0806V5czu35t4AX5HWIYEkF8eN5dwbF0OpwKHC6aux853Eyak/jtTmPhxuNw4ZTJOEw+rGsbC8zQ7gyMRmbaw/jkLkLQE+LOQWFA7FRzzywvnO4uTUXg0TFKIf7s+MtisrQ3oL5xLH9ps3uf04gtjgSBlSq0hjemZK/mRPddWZpjSAEKMEcFhYuLEGmpcSODbjg0OqUBQ4bk2piUWBA1fVeaay6RUKVgWe0S7WMvu1eW7XfRrKZrMKB0SMPXd+ecVi0fCsgBeL1XBzc3Nre/XV868qB3AYR459kZSVmgJt3EdNzMHL6HC2sNoVXO/PSpXL/6rs12bh8NvnLpb0kbrQB6CbQuLV1Xpb1zr6QOqnnz777P39unLlyujFb2pJ/Q//hx5+fhv1R6HbUp/fvnTxcegjV+vjB3TYMdSfQBO3bt2qv/4ZNP6h0Pv1hw8fXgKNxWFPbmQ0e0CXxg4eQ2GMwEXj67cwvSxkRNR4OTFeH89m4xA6wFtGDvaigOPQcz3IIH6uCweq8mq2Hp/VDv234ICKa+1QHLFp738LDqzRbxyKIy6bLL/NOg7DkUEcU6d+nWekY+OAUBlWsIE7DMfFT7FKx8Ihfy0Da3SzG4ds4ASOpDgLqY61Hqfz+fTbt4tv//2fO293seS4+vPP169vVNrWERcafXCQsVapVkvX7i0u1lClfOkO6PbNd+/e/fdTFUonoOw446s+NfXEQZZKafx1jbuLKzXxaxtS+Y9ultLpOx9KHC9jVHb0xEHWBYz8vcWVUjq9Bwc83nlQkdaRFBzVe3L6dxf30Iis44GKHTGqwnriGJMOsrKYTh+NY+fbGJUdPXGs19LSV2p9cMTlZoaeODCQHoWjls6/68SO2FRhPXG0JI6VNo57d6Ue3oR/HyUMh36ppnDISJq/Oz4+XseVofoH4/Xx+oRMtA9e18/+sk9LvXCsl7px5GvjI6j6xEz96szMzITCsTUSn9DRO5RGOKRxrIi74bJXNi5PXhMbdbKFM1OxSbP9Em1eJtoVUY2tjEkczdW5a/cnJzdWIxwxUu8yTGBILwpvyafXJY5qYfTqzOzsnC73m/hZX/JpqgeObPWxjKGLLRlTWwpHVuJI8RAUKxq9exZZeKRbS6oew5jZhSOG6tnRqlja2lU4MHgkF0d2XVnHroylSccxJmPp4927pSEOSC0tmVJarSGOVLtrKbV2JZalZOPIrotCrKSCR6mVSjaOdvAQFUj+MXiLwjGRQBzgLSrDqqWPpWySrSOVXZKLx2NyERn/3o/AMT43MxOjPrajPttOsm0prU8Jbym1iMJhXF492+s8I/XBkW3JGFoVz+lWVeEovpdMHOu435RPV6fQTLDy6FiH+DsOZ3u1p66+W9bKW16IJr+2bhjjU8bcePHydFHdrHyWV3vq6ocjK7pZCBq7aCa18enp+9PTk9emL09HWo0TkL7WQe6k8/lai75YKeFzVTpLdrWo/MSYLu7//AVWXxzZpXsPH360RFK79/CGSbIfR2F1NSFrpVJk7NmTZzb+lVJ4fqBwGBsb6i7S6sZGfPYk++MoFIytl1sGPBe3trY2RlICx9TshCzD9LnZ2Zkzu9pTV18cv6CKhnx+sTElrcOemFPWMRercr3vbbZzv/46eunuujE6uru0e28JnSU1N5Kai34zTJ+LEQ1xR2DPY7Qa/fV4dVw+d06Jx93XQw011FBDDTXUUG39D3GjofhCAaRtAAAAAElFTkSuQmCC');
            background-size: cover;
            background-position: center;
        }


        @keyframes bundle-countUp {
            0% {
                opacity: 0;
                transform: scale(0.5);
            }

            50% {
                opacity: 1;
                transform: scale(1.1);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .bundle-card:nth-child(1) .bundle-card-number {
            animation-delay: 0.5s;
        }

        .bundle-card:nth-child(2) .bundle-card-number {
            animation-delay: 1s;
        }

        .bundle-card:nth-child(3) .bundle-card-number {
            animation-delay: 1.5s;
        }


        .bundle-card-body-alpha {
            padding: 70px 25px 25px;
            text-align: center;
            background: #be865241;
            border-radius: 15px;
            margin: 10px;
            margin-top: 60px;
            height: calc(97% - 60px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .bundle-card-body-beta {
            padding: 70px 25px 25px;
            text-align: center;
            background: #be865241;
            border-radius: 15px;
            margin: 10px;
            margin-top: 60px;
            height: calc(97% - 60px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);

        }

        .bundle-card-body-gamma {
            padding: 70px 25px 25px;
            text-align: center;
            background: #be865241;
            border-radius: 15px;
            margin: 10px;
            margin-top: 60px;
            height: calc(97% - 60px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);


        }

        .bundle-card-content {
            flex: 1;
            margin-top: -55px;
        }



        .bundle-card-subtitle {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            /* text-transform: uppercase; */
            letter-spacing: 0.5px;
        }

        .bundle-card-alpha .bundle-card-subtitle {
            background-color: #000;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .bundle-card-beta .bundle-card-subtitle {
            background-color: #000;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .bundle-card-gamma .bundle-card-subtitle {
            background-color: #000;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }



        .bundle-card-text {
            font-size: 12px;
            color: #888;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .bundle-card-text ul {
            list-style: none;
            padding: 0;
            font-size: 16px;
            text-align: left;
        }

        .bundle-card-text li {
            margin-bottom: 12px;
            display: flex;
            color: #000;
            align-items: center;
            animation: bundle-staggerFadeIn 0.8s ease-out;
            animation-fill-mode: both;
        }

        .bundle-card-alpha .bundle-card-text li:nth-child(1) {
            animation-delay: 0.7s;
        }

        .bundle-card-alpha .bundle-card-text li:nth-child(2) {
            animation-delay: 0.8s;
        }

        .bundle-card-alpha .bundle-card-text li:nth-child(3) {
            animation-delay: 0.9s;
        }

        .bundle-card-alpha .bundle-card-text li:nth-child(4) {
            animation-delay: 1.0s;
        }

        .bundle-card-alpha .bundle-card-text li:nth-child(5) {
            animation-delay: 1.1s;
        }

        .bundle-card-alpha .bundle-card-text li:nth-child(6) {
            animation-delay: 1.2s;
        }

        .bundle-card-beta .bundle-card-text li:nth-child(1) {
            animation-delay: 1.2s;
        }

        .bundle-card-beta .bundle-card-text li:nth-child(2) {
            animation-delay: 1.3s;
        }

        .bundle-card-beta .bundle-card-text li:nth-child(3) {
            animation-delay: 1.4s;
        }

        .bundle-card-beta .bundle-card-text li:nth-child(4) {
            animation-delay: 1.5s;
        }

        .bundle-card-beta .bundle-card-text li:nth-child(5) {
            animation-delay: 1.6s;
        }

        .bundle-card-beta .bundle-card-text li:nth-child(6) {
            animation-delay: 1.7s;
        }

        .bundle-card-gamma .bundle-card-text li:nth-child(1) {
            animation-delay: 1.7s;
        }

        .bundle-card-gamma .bundle-card-text li:nth-child(2) {
            animation-delay: 1.8s;
        }

        .bundle-card-gamma .bundle-card-text li:nth-child(3) {
            animation-delay: 1.9s;
        }

        .bundle-card-gamma .bundle-card-text li:nth-child(4) {
            animation-delay: 2.0s;
        }

        .bundle-card-gamma .bundle-card-text li:nth-child(5) {
            animation-delay: 2.1s;
        }

        .bundle-card-gamma .bundle-card-text li:nth-child(6) {
            animation-delay: 2.2s;
        }

        @keyframes bundle-staggerFadeIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .bundle-card-text li i {
            margin-right: 8px;
            /* background: rgba(255, 255, 255, 0.2); */
            border-radius: 50%;
            padding: 6px;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bundle-card-alpha .bundle-card-text li i {
            color: #2c2d3a;
            ;
        }

        .bundle-card-beta .bundle-card-text li i {
            color: #ffab40;
        }

        .bundle-card-gamma .bundle-card-text li i {
            color: #00b0ff;
        }

        .bundle-card-icon {
            font-size: 32px;
            padding: 15px;
            transition: transform 0.3s ease;
        }

        .bundle-card:hover .bundle-card-icon {
            transform: rotate(360deg);
        }

        .bundle-card-alpha .bundle-card-icon {
            color: #5ec95e;
        }

        .bundle-card-beta .bundle-card-icon {
            color: #ffab40;
        }

        .bundle-card-gamma .bundle-card-icon {
            color: #00b0ff;
        }

        .bundle-button-group {
            display: flex;
            justify-content: space-around;
            gap: 10px;
            margin-top: -6px;
        }

        .bundle-read-more-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            width: 120px;
            border: none;
            padding: 12px 7px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .bundle-read-more-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .bundle-learn-more-btn {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            color: white;
            width: 120px;
            border: none;
            padding: 12px 7px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .bundle-learn-more-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }


        .bundle-badge {
            padding: 10px 18px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bundle-badge-yellow {
            background-color: #ffd740;
            color: #333;
            border-radius: 5px 0 0 5px;
        }

        .bundle-badge-dark {
            background-color: #2c2c2c;
            color: white;
            border-radius: 0 5px 5px 0;
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            .bundle-container {
                grid-template-columns: repeat(2, 280px);
                gap: 80px;
            }
        }

        @media (max-width: 768px) {
            .bundle-container {
                grid-template-columns: 1fr;
                gap: 100px;
                max-width: 100%;
            }

        }
        /* Impact Section */
        .impact {
            background: #f8f9fa;
        }

        .impact-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
        }

        .impact-stat {
            text-align: center;
            padding: 1.2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .impact-stat:hover {
            transform: translateY(-5px);
        }

        .impact-number {
            font-size: 3rem;
            font-weight: 700;
            color: #be8652;
            margin-bottom: 0.5rem;
        }

        .impact-label {
            color: #666;
            font-weight: 500;
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



            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2rem;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .course-cards {
                grid-template-columns: 1fr;
            }

            .course-card.popular {
                transform: none;
            }

            .steps-container {
                grid-template-columns: repeat(2, 1fr);
            }

            /* Smaller font and box sizes for steps-container on mobile */
            .steps-container .step {
                padding: 0.5rem;
            }

            .steps-container .step h3 {
                font-size: 1rem;
            }

            .steps-container .step p {
                font-size: 0.8rem;
            }

            .steps-container .step-icon {
                width: 50px;
                height: 50px;
                margin-bottom: 0.75rem;
            }

            .steps-container .step-icon i {
                font-size: 1.2rem;
            }

            .steps-container .step-number {
                width: 20px;
                height: 20px;
                font-size: 0.7rem;
                top: -8px;
                right: -8px;
            }

            .testimonial-author {
                flex-direction: column;
                text-align: center;
            }

            .author-info {
                text-align: center;
            }

            .author-info .rating {
                justify-content: center;
            }

            .slider-btn {
                display: none;
            }

            .ment-cards {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 1rem;
                padding: 0 1rem;
                scrollbar-width: none;
                /* Firefox */
                -ms-overflow-style: none;
                /* IE and Edge */
            }

            .ment-cards::-webkit-scrollbar {
                display: none;
                /* Chrome, Safari, and Opera */
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

            section {
                padding: 60px 0;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 0.9rem;
            }

            .hero-text h1 {
                font-size: 2rem;
            }

            .hero-text p {
                font-size: 1rem;
            }

            .testimonial-slide {
                padding: 2rem 1rem;
            }

            .testimonial-content p {
                font-size: 1rem;
            }

            .hero-btn-large {
                padding: 14px 24px;
                font-size: 1rem;
            }
        }

        /* FAQ Section  */

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
        
        .FaqH1 {
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
            margin-bottom: -10px;
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

        /* How It Works  */
        :root {
            --primary-teal: #be8652;
            --primary-teal-light: #785125;
            --primary-teal-dark: #615228;
            --text-dark: #1a1a1a;
            --text-gray: #666;
            --text-light: #888;
            --bg-light: #f8f8f8;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 30px 80px rgba(0, 0, 0, 0.2);
        }

        .hiw-how-it-works {
            position: relative;
            height: auto;
            width: 100%;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            /* background-image: linear-gradient(90deg, #EBBE81, #000000); */
            background: #be86522c;
        }

        .hiw-container {
            width: 100%;
            max-width: 95%;
            position: relative;
            z-index: 10;
        }

        .hiw-background-elements {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .hiw-decorative-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(2px);
        }

        .hiw-circle-1 {
            width: 200px;
            height: 200px;
            top: -100px;
            left: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .hiw-circle-1::before,
        .hiw-circle-1::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
        }

        .hiw-circle-1::before {
            width: 25px;
            height: 25px;
            top: 35%;
            left: 28%;
            animation: pulse 3s ease-in-out infinite;
        }

        .hiw-circle-1::after {
            width: 18px;
            height: 18px;
            top: 55%;
            left: 48%;
            animation: pulse 3s ease-in-out infinite 1s;
        }

        .hiw-circle-2 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            right: -50px;
            animation: float 10s ease-in-out infinite 1s;
        }

        .hiw-circle-3 {
            width: 100px;
            height: 100px;
            top: 40%;
            right: 5%;
            animation: float 12s ease-in-out infinite 2s;
        }

        .hiw-gradient-blob {
            position: absolute;
            border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: blobMove 20s ease-in-out infinite;
        }

        .hiw-blob-1 {
            width: 300px;
            height: 300px;
            /* background: linear-gradient(135deg, #0ea5e9, #14b8a6); */
            top: 10%;
            left: 10%;
        }

        .hiw-blob-2 {
            width: 250px;
            height: 250px;
            /* background: linear-gradient(135deg, #06b6d4, #10b981); */
            bottom: 15%;
            right: 15%;
            animation-delay: -10s;
        }

        .hiw-section-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .hiw-main-title {
            font-size: 3rem;
            font-weight: 800;
            /* color: var(--white); */
            color: #000;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            letter-spacing: -1px;
            animation: fadeIn 1s ease-out 0.4s backwards;
            margin-top: -20px;
        }

        .hiw-section-description {
            font-size: 1rem;
            /* color: rgba(255, 255, 255, 0.9); */
            color: #000;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
            animation: fadeIn 1s ease-out 0.6s backwards;

        }

        .hiw-card {
            background: var(--white);
            border-radius: 40px;
            /* padding: 70px 80px 60px; */
            padding-left: 40px;
            padding-right: 40px;
            padding-top: 40px;
            padding-bottom: 1px;
            /* box-shadow: var(--shadow-xl); */
            position: relative;
            animation: cardEntry 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.3s backwards;
        }

        .hiw-card::before {
            content: '';
            position: absolute;
            inset: -2px;
            /* background: linear-gradient(135deg,#be8652,#be8652); */
            border-radius: 40px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .hiw-card:hover::before {
            opacity: 1;
        }

        .hiw-steps-container {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .hiw-step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .hiw-step[data-step="1"] {
            animation: stepEntry 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.8s forwards;
        }

        .hiw-step[data-step="2"] {
            animation: stepEntry 0.8s cubic-bezier(0.16, 1, 0.3, 1) 1.1s forwards;
        }

        .hiw-step[data-step="3"] {
            animation: stepEntry 0.8s cubic-bezier(0.16, 1, 0.3, 1) 1.4s forwards;
        }

        .hiw-step:hover {
            transform: translateY(-8px);
        }

        .hiw-step-badge {
            margin-bottom: 20px;
            position: relative;
        }

        .hiw-step-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-teal-light) 0%, var(--primary-teal) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 30px #be8652;
            cursor: pointer;
        }

        .hiw-step-circle::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal-light), var(--primary-teal));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .hiw-step:hover .hiw-step-circle {
            transform: scale(1.12) rotate(-5deg);
            box-shadow: 0 15px 50px #be8652;
        }

        .hiw-step:hover .hiw-step-circle::before {
            opacity: 0.5;
        }

        .hiw-step-number {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--white);
            position: relative;
            z-index: 2;
        }

        .hiw-circle-glow {
            position: absolute;
            inset: -15px;
            border-radius: 50%;
            background: radial-gradient(circle, #be8652 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        .hiw-step:hover .hiw-circle-glow {
            opacity: 1;
            animation: glowPulse 2s ease-in-out infinite;
        }

        .hiw-step-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ffffff 0%, #be5c0038 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: var(--shadow-sm);
        }

        .hiw-step-icon svg {
            width: 20px;
            height: 20px;
            stroke: #be8652;
            transition: all 0.4s ease;
        }

        .hiw-step:hover .hiw-step-icon {
            transform: translateY(-5px) rotate(5deg);
            box-shadow: var(--shadow-md);
        }

        .hiw-step:hover .hiw-step-icon svg {
            stroke: var(--primary-teal-dark);
            transform: scale(1.1);
        }

        .hiw-step-content h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 15px;
            transition: color 0.3s ease;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .hiw-step:hover .hiw-step-content h3 {
            color: var(--primary-teal);
        }

        .hiw-step-content p {
            font-size: 0.85rem;
            /* color: var(--text-gray); */
            color: #000000;
            line-height: 1.7;
            max-width: 280px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            transition: all 0.3s ease;
        }

        .hiw-step:hover .hiw-step-content p {
            color: #000000;
            transform: translateY(-2px);
        }

        .hiw-connector {
            width: 140px;
            height: 80px;
            flex-shrink: 0;
            margin-top: 45px;
            position: relative;
            opacity: 0;
        }

        .hiw-steps-container .hiw-connector:nth-of-type(2) {
            animation: connectorEntry 1s ease-out 1s forwards;
        }

        .hiw-steps-container .hiw-connector:nth-of-type(4) {
            animation: connectorEntry 1s ease-out 1.3s forwards;
        }

        .hiw-connector svg {
            width: 100%;
            height: 100%;
        }

        .hiw-connector-path {
            stroke-dasharray: 400;
            stroke-dashoffset: 400;
        }

        .hiw-steps-container .hiw-connector:nth-of-type(2) .hiw-connector-path {
            animation: drawPath 1.5s ease-out 1.2s forwards;
        }

        .hiw-steps-container .hiw-connector:nth-of-type(4) .hiw-connector-path {
            animation: drawPath 1.5s ease-out 1.5s forwards;
        }

        .hiw-connector-dot {
            position: absolute;
            width: 8px;
            height: 8px;
            background: var(--primary-teal);
            border-radius: 50%;
            opacity: 0;
            box-shadow: 0 0 15px #be8652;
        }

        .hiw-dot-1 {
            top: 20%;
            left: 30%;
            animation: dotAppear 0.6s ease-out 1.8s forwards, dotFloat 3s ease-in-out 2.4s infinite;
        }

        .hiw-dot-2 {
            top: 60%;
            left: 70%;
            animation: dotAppear 0.6s ease-out 2s forwards, dotFloat 3s ease-in-out 2.6s infinite;
        }

        .cta-section {
            text-align: center;
            padding-top: 20px;
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 1.7s backwards;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: linear-gradient(135deg, var(--primary-teal-light) 0%, var(--primary-teal) 100%);
            color: var(--white);
            border: none;
            border-radius: 50px;
            padding: 16px 40px;
            font-size: 1.05rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 25px rgba(15, 118, 110, 0.3);
        }

        .button-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-teal) 0%, var(--primary-teal-dark) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 0;
        }

        .cta-button:hover .button-bg {
            opacity: 1;
        }

        .cta-button:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 15px 40px rgba(15, 118, 110, 0.4);
        }

        .cta-button:active {
            transform: translateY(-2px) scale(0.98);
        }

        .phone-icon,
        .button-text {
            position: relative;
            z-index: 1;
        }

        .phone-icon {
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .cta-button:hover .phone-icon {
            transform: rotate(-15deg) scale(1.15);
        }

        .cta-subtext {
            margin-top: 16px;
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 500;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
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

        @keyframes cardEntry {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(30px);
            }

            60% {
                transform: scale(1.01) translateY(-5px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes stepEntry {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }

            60% {
                transform: translateY(-8px) scale(1.02);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes connectorEntry {
            to {
                opacity: 1;
            }
        }

        @keyframes drawPath {
            to {
                stroke-dashoffset: 0;
            }
        }

        @keyframes dotAppear {
            to {
                opacity: 1;
            }
        }

        @keyframes dotFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }

            33% {
                transform: translate(20px, -15px) rotate(6deg);
            }

            66% {
                transform: translate(-15px, 20px) rotate(-6deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.3;
            }

            50% {
                transform: scale(1.4);
                opacity: 0.8;
            }
        }

        @keyframes blobMove {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
                border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
            }

            33% {
                transform: translate(30px, -20px) scale(1.1);
                border-radius: 60% 40% 30% 70% / 50% 60% 40% 50%;
            }

            66% {
                transform: translate(-20px, 30px) scale(0.9);
                border-radius: 30% 60% 70% 40% / 50% 40% 60% 50%;
            }
        }

        @keyframes glowPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        @media (max-width: 1024px) {
            .hiw-card {
                padding: 50px 40px 40px;
            }

            .hiw-connector {
                width: 110px;
            }

            .hiw-step-content p {
                max-width: 240px;
            }
        }

        @media (max-width: 768px) {
            .hiw-how-it-works {
                padding: 50px 20px;
            }

            .hiw-section-header {
                margin-bottom: 30px;
            }

            .hiw-main-title {
                font-size: 2rem;
                margin-bottom: 15px;
            }

            .hiw-section-description {
                font-size: 0.9rem;
                padding: 0 20px;
            }

            .hiw-card {
                padding: 40px 25px 30px;
                border-radius: 30px;
            }

            .hiw-steps-container {
                display: grid;
                grid-template-columns: 1fr;
                gap: 40px;
                margin-bottom: 40px;
            }

            .hiw-step {
                max-width: 100%;
            }

            .hiw-step-content {
                padding: 0 20px;
            }

            .hiw-step-content p {
                max-width: 100%;
                font-size: 0.85rem;
            }

            .hiw-connector {
                display: none;
            }

            .hiw-circle-1 {
                width: 150px;
                height: 150px;
                top: -70px;
                left: -70px;
            }

            .hiw-circle-2,
            .hiw-circle-3 {
                display: none;
            }

            .hiw-blob-1,
            .hiw-blob-2 {
                width: 250px;
                height: 250px;
            }
        }

        @media (max-width: 480px) {
            .hiw-how-it-works {
                padding: 30px 15px;
            }

            .hiw-main-title {
                font-size: 1.5rem;
                padding: 0 10px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            .hiw-section-description {
                font-size: 0.8rem;
                line-height: 1.6;
                padding: 0 15px;
            }

            .hiw-card {
                padding: 30px 15px 25px;
                border-radius: 25px;
            }

            .hiw-steps-container {
                gap: 35px;
                margin-bottom: 35px;
            }

            .hiw-step-circle {
                width: 60px;
                height: 60px;
            }

            .hiw-step-number {
                font-size: 1rem;
            }

            .hiw-step-icon {
                display: none;
                width: 45px;
                height: 45px;
                margin-bottom: 20px;
                border-radius: 18px;
            }

            .hiw-step-icon svg {
                width: 22px;
                height: 22px;
            }

            .hiw-step-content {
                padding: 0 15px;
            }

            .hiw-step-content h3 {
                font-size: 1rem;
                margin-bottom: 12px;
                word-wrap: break-word;
                overflow-wrap: break-word;
                padding: 0 5px;
            }

            .hiw-step-content p {
                font-size: 0.8rem;
                line-height: 1.65;
                padding: 0 5px;
            }

            .cta-button {
                font-size: 0.9rem;
                padding: 12px 28px;
                width: 100%;
                max-width: 300px;
            }

            .cta-subtext {
                font-size: 0.8rem;
                padding: 0 10px;
            }

            .hiw-circle-1 {
                width: 120px;
                height: 120px;
                top: -50px;
                left: -50px;
            }

            .hiw-blob-1,
            .hiw-blob-2 {
                width: 200px;
                height: 200px;
            }
        }

        @media (max-width: 360px) {
            .hiw-main-title {
                font-size: 1.25rem;
            }

            .hiw-section-description {
                font-size: 0.75rem;
            }

            .hiw-card {
                padding: 25px 12px 20px;
            }

            .hiw-step-circle {
                width: 55px;
                height: 55px;
            }

            .hiw-step-number {
                font-size: 0.9rem;
            }

            .hiw-step-icon {
                width: 40px;
                height: 40px;
            }

            .hiw-step-content h3 {
                font-size: 0.95rem;
            }

            .hiw-step-content p {
                font-size: 0.75rem;
            }

            .cta-button {
                font-size: 0.85rem;
                padding: 10px 24px;
            }
        }
    </style>
</head>

<body>

<!-- header  -->
    <?php 
    $CURRENT_PAGE = 'index';
    include ('includes/header.php');
    ?>
    <!-- Hero Section -->
    <section class="hero-home">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Culture of Internet<br><span class="hero-highlight">Explore Courses & Learn</span></h1>
                    <p>Join India's leading Masterclasses with internship opportunities, expert mentors, and
                        industry-recognized certifications. Transform your career with practical skills.</p>
                    <div class="hero-buttons">
                        <a href="pricings.php" class="hero-btn hero-btn-primary hero-btn-large">
                            Explore Courses
                            <i class="fas fa-arrow-right"></i>
    </a>
                        
                    </div>
                    <div class="hero-stats">
                        <i class="fab fa-youtube hero-youtube-icon"></i>
                        <span>1M+ Subscribers on YouTube</span>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=600"
                        alt="Learning Platform">
                </div>
            </div>
        </div>
    </section>
    <!-- About Section  -->
    <div class="about-container">
        <div class="about-content">
            <!-- Header Section -->
            <div class="about-header">
                <p class="about-subtitle">ABOUT US</p>
                <h1 class="about-title">
                    Unveiling Our Identity,<br />Vision and Values
                </h1>
                <p class="about-description">
                    We're passionate about chemical innovation. With years of experience in the industry,
                    we've established ourselves as leaders in providing high-quality chemical solutions.
                </p>
            </div>

            <!-- Values Card -->
            <div class="about-values-card">
                <div class="about-values-grid">
                    <div class="about-value-item">
                        <svg class="about-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <p class="about-value-label">Safety</p>
                    </div>
                    <div class="about-value-item">
                        <svg class="about-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="3" />
                            <path
                                d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24" />
                        </svg>
                        <p class="about-value-label">Efficient</p>
                    </div>
                    <div class="about-value-item">
                        <svg class="about-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M22 12h-4m-6 0H2m10-6v4m0 6v4" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>
                        <p class="about-value-label">Precision</p>
                    </div>
                    <div class="about-value-item">
                        <svg class="about-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path d="M12 4v1m6 7h1m-9 8v1M6 12H5m4.4-5.6.7.7m7.8 0 .7-.7M5 19l14-14" />
                        </svg>
                        <p class="about-value-label">Innovation</p>
                    </div>
                </div>
            </div>

            <!-- Vision & Mission Cards -->
            <div class="about-cards-grid">
                <div class="about-card">
                    <div class="about-card-header">
                        <svg class="about-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <h2 class="about-card-title">Vision</h2>
                    </div>
                    <p class="about-card-text">
                        To lead the way in chemical manufacturing by delivering innovative, sustainable,
                        and cost-effective solutions
                    </p>
                </div>

                <div class="about-card">
                    <div class="about-card-header">
                        <svg class="about-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M22 12h-6m-4 0H2m10-6v4m0 6v4" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>
                        <h2 class="about-card-title">Mission</h2>
                    </div>
                    <p class="about-card-text">
                        To leverage our expertise, resources, and technology to manufacture chemical
                        products that exceed industry standards
                    </p>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="about-cta-container">
                <button class="about-cta-button">Know More About Us</button>
            </div>
        </div>
    </div>

    <!-- Course Bundle Section  -->
    <div class="bundle-container">
        <div class="bundle-card bundle-card-alpha">
            <div class="bundle-card-header">
            </div>
            <div class="bundle-card-body-alpha">
                <div class="bundle-card-content">
                    <div class="bundle-card-subtitle">Alpha</div>
                    <div class="bundle-card-text">
                        <ul>
                            <li><i class="fas fa-bullseye"></i> Targeted marketing strategies</li>
                            <li><i class="fas fa-user-friends"></i> Personalized outreach</li>
                            <li><i class="fas fa-chart-pie"></i> Data-driven insights</li>
                            <li><i class="fas fa-users"></i> Customer segmentation</li>
                            <li><i class="fas fa-calculator"></i> ROI tracking</li>
                            <li><i class="fas fa-envelope"></i> Email campaigns</li>
                        </ul>
                    </div>
                </div>
                <div class="bundle-button-group">
                    <button class="bundle-read-more-btn">Learn More</button>
                    <button class="bundle-learn-more-btn">Buy Now</button>
                </div>
            </div>
        </div>

        <div class="bundle-card bundle-card-beta">
            <div class="bundle-card-header">
            </div>
            <div class="bundle-card-body-beta">
                <div class="bundle-card-content">
                    <div class="bundle-card-subtitle">Beta</div>
                    <div class="bundle-card-text">
                        <ul>
                            <li><i class="fas fa-tasks"></i> Streamline processes</li>
                            <li><i class="fas fa-chart-line"></i> Optimize workflows</li>
                            <li><i class="fas fa-robot"></i> Automation tools</li>
                            <li><i class="fas fa-tachometer-alt"></i> Performance metrics</li>
                            <li><i class="fas fa-award"></i> Quality assurance</li>
                            <li><i class="fas fa-sync-alt"></i> Continuous improvement</li>
                        </ul>
                    </div>
                </div>
                <div class="bundle-button-group">
                    <button class="bundle-read-more-btn">Learn More</button>
                    <button class="bundle-learn-more-btn">Buy Now</button>
                </div>
            </div>
        </div>

        <div class="bundle-card bundle-card-gamma">
            <div class="bundle-card-header">
            </div>
            <div class="bundle-card-body-gamma">
                <div class="bundle-card-content">
                    <div class="bundle-card-subtitle">Gamma</div>
                    <div class="bundle-card-text">
                        <ul>
                            <li><i class="fas fa-chart-bar"></i> Implement proven sales</li>
                            <li><i class="fas fa-dollar-sign"></i> Leverage data analytics</li>
                            <li><i class="fas fa-magnet"></i> Lead generation</li>
                            <li><i class="fas fa-funnel-dollar"></i> Conversion optimization</li>
                            <li><i class="fas fa-search"></i> Market research</li>
                            <li><i class="fas fa-search"></i> Market research</li>
                        </ul>
                    </div>
                </div>
                <div class="bundle-button-group">
                    <button class="bundle-read-more-btn">Learn More</button>
                    <button class="bundle-learn-more-btn">Buy Now</button>
                </div>
            </div>
        </div>


    </div>

    <!-- How It Works  -->
    <section class="hiw-how-it-works">
        <div class="hiw-background-elements">
            <div class="hiw-decorative-circle hiw-circle-1"></div>
            <div class="hiw-decorative-circle hiw-circle-2"></div>
            <div class="hiw-decorative-circle hiw-circle-3"></div>
            <div class="hiw-gradient-blob hiw-blob-1"></div>
            <div class="hiw-gradient-blob hiw-blob-2"></div>
        </div>

        <div class="hiw-container">
            <div class="hiw-section-header">
                <h1 class="hiw-main-title">How it works?</h1>
                <p class="hiw-section-description">Experience seamless service with our simple three-step process designed for
                    your convenience</p>
            </div>

            <div class="hiw-card">
                <div class="hiw-steps-container">
                    <div class="hiw-step" data-step="1">
                        <div class="hiw-step-badge">
                            <div class="hiw-step-circle">
                                <span class="hiw-step-number">01</span>
                                <div class="hiw-circle-glow"></div>
                            </div>
                        </div>
                        <div class="hiw-step-content">
                            <div class="hiw-step-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </div>
                            <h3>Select Product</h3>
                            <p>Browse our premium collection and choose the perfect product that fits your needs and lifestyle</p>
                        </div>
                    </div>

                    <div class="hiw-connector">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="lineGradient1" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#be8652;stop-opacity:0.3" />
                                    <stop offset="50%" style="stop-color:#be8652;stop-opacity:0.6" />
                                    <stop offset="100%" style="stop-color:#be8652;stop-opacity:0.3" />
                                </linearGradient>
                            </defs>
                            <path class="hiw-connector-path" d="M 0 50 Q 100 0, 200 50" fill="none" stroke="url(#lineGradient1)"
                                stroke-width="3" stroke-dasharray="8,8" />
                        </svg>
                        <div class="hiw-connector-dot hiw-dot-1"></div>
                        <div class="hiw-connector-dot hiw-dot-2"></div>
                    </div>

                    <div class="hiw-step" data-step="2">
                        <div class="hiw-step-badge">
                            <div class="hiw-step-circle">
                                <span class="hiw-step-number">02</span>
                                <div class="hiw-circle-glow"></div>
                            </div>
                        </div>
                        <div class="hiw-step-content">
                            <div class="hiw-step-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                            </div>
                            <h3>Make Payment</h3>
                            <p>Complete your secure checkout with our trusted payment system and multiple payment options</p>
                        </div>
                    </div>

                    <div class="hiw-connector">
                        <svg viewBox="0 0 200 100" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="lineGradient2" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#be8652;stop-opacity:0.3" />
                                    <stop offset="50%" style="stop-color:#be8652;stop-opacity:0.6" />
                                    <stop offset="100%" style="stop-color:#be8652;stop-opacity:0.3" />
                                </linearGradient>
                            </defs>
                            <path class="hiw-connector-path" d="M 0 50 Q 100 100, 200 50" fill="none" stroke="url(#lineGradient2)"
                                stroke-width="3" stroke-dasharray="8,8" />
                        </svg>
                        <div class="hiw-connector-dot hiw-dot-1"></div>
                        <div class="hiw-connector-dot hiw-dot-2"></div>
                    </div>

                    <div class="hiw-step" data-step="3">
                        <div class="hiw-step-badge">
                            <div class="hiw-step-circle">
                                <span class="hiw-step-number">03</span>
                                <div class="hiw-circle-glow"></div>
                            </div>
                        </div>
                        <div class="hiw-step-content">
                            <div class="hiw-step-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                    <polyline points="23 11 20 14 18 12"></polyline>
                                </svg>
                            </div>
                            <h3>Receive Delivery</h3>
                            <p>Sit back and relax while we deliver your order right to your doorstep with care and speed</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Mentors Section -->
    <section id="mentors" class="mentors">
        <div class="container">
            <div class="section-header">
                <h2>Meet Your Expert Mentors</h2>
                <p>Learn from industry veterans who have worked at top companies and have years of practical experience
                    to share with you.</p>
            </div>

            <div class="ment-slider">
                <div class="ment-cards">
                    <div class="ment-card-1">
                        <div class="ment-image-1">
                            <img
                                src="https://images.pexels.com/photos/1043471/pexels-photo-1043471.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop"
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
                            <img
                                src="https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop"
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
                            <img
                                src="https://images.pexels.com/photos/1239291/pexels-photo-1239291.jpeg?auto=compress&cs=tinysrgb&w=300&h=300&fit=crop"
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
    <!-- Why Choose Section  -->
    <div class="wcci-container">
        <div class="wcci-hero-section">
            <!-- <div class="wcci-badge">TRAVEL TO DELIVER</div> -->
            <h1 class="wcci-main-title" style="text-align: center;">Why Choose <span style="color: #be8652;">Culture of
                    Internet</span></h1>
            <p class="wcci-subtitle" style="text-align: center; color: #000;">Revolutionizing package delivery— connect, save,
                earn, and trust in a global network.</p>
        </div>

        <div class="wcci-features-grid">
            <div class="wcci-feature-card">
                <div class="wcci-icon-wrapper">
                    <svg class="wcci-feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path
                            d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                    </svg>
                </div>
                <h3 class="wcci-feature-title">Worldwide</h3>
                <p class="wcci-feature-description">Send packages globally from any city with matching travelers.</p>
            </div>

            <div class="wcci-feature-card">
                <div class="wcci-icon-wrapper">
                    <svg class="wcci-feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <h3 class="wcci-feature-title">Profitable</h3>
                <p class="wcci-feature-description">Earn money on travel and fund future trips at reduced costs.</p>
            </div>

            <div class="wcci-feature-card">
                <div class="wcci-icon-wrapper">
                    <svg class="wcci-feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <polyline points="7.5 4.21 12 6.81 16.5 4.21" />
                        <polyline points="7.5 19.79 7.5 14.6 3 12" />
                        <polyline points="21 12 16.5 14.6 16.5 19.79" />
                        <polyline points="3 12 12 17 21 12" />
                    </svg>
                </div>
                <h3 class="wcci-feature-title">Economical</h3>
                <p class="wcci-feature-description">Reduce shipping costs by matching travelers to carry packages.</p>
            </div>

            <div class="wcci-feature-card">
                <div class="wcci-icon-wrapper">
                    <svg class="wcci-feature-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <path d="M9 12l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="wcci-feature-title">Secure Payment</h3>
                <p class="wcci-feature-description">Payments are safely held until delivery or refunded if the trip is canceled.
                </p>
            </div>
        </div>
    </div>


    <!-- Testimonial Section  -->
    <section class="Testi-testimonial-section">
        <h1 style="text-align: center; justify-content: center; color:#000;">What Our Students Say</h1>
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
                <h1 class="FaqH1">Frequently asked <span class="faq-highlight">questions</span></h1>
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
  <?php include ('includes\footer.php') ?>
    <script>
        // About Section //
        document.addEventListener('DOMContentLoaded', () => {
            const valueItems = document.querySelectorAll('.about-value-item');
            const cards = document.querySelectorAll('.about-card');
            const valuesCard = document.querySelector('.about-values-card');
            const ctaButton = document.querySelector('.about-cta-button');

            valueItems.forEach((item, index) => {
                item.addEventListener('mouseenter', () => {
                    item.style.transform = 'scale(1.1)';
                });

                item.addEventListener('mouseleave', () => {
                    item.style.transform = 'scale(1)';
                });
            });

            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                });
            });

            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -100px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.about-card, .about-values-card').forEach(el => {
                observer.observe(el);
            });

            ctaButton.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s ease-out';
                ripple.style.pointerEvents = 'none';

                this.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });

            const style = document.createElement('style');
            style.textContent = `
    @keyframes ripple {
      to {
        transform: scale(2);
        opacity: 0;
      }
    }
  `;
            document.head.appendChild(style);

        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const targetPosition = target.offsetTop - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Scroll reveal animation
        const revealElements = document.querySelectorAll('.impact-stat, .ment-card-1, .ment-card-2, .ment-card-3');

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(element => {
            revealObserver.observe(element);
        });

        // Add loading animation to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                // Add ripple effect
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');

                this.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple effect CSS
        const style = document.createElement('style');
        style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
        document.head.appendChild(style);

        // Form validation (if forms are added later)
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Lazy loading for images
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });

        // Performance optimization: Debounce scroll events
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Optimized scroll handler
        const optimizedScrollHandler = debounce(() => {
            const scrolled = window.scrollY > 100;
            header.classList.toggle('scrolled', scrolled);
        }, 10);

        window.addEventListener('scroll', optimizedScrollHandler);

        // Add keyboard navigation for testimonial slider
        document.addEventListener('keydown', (e) => {
            const slider = document.querySelector('.testimonial-slider');
            if (slider && slider.matches(':hover')) {
                if (e.key === 'ArrowLeft') {
                    document.getElementById('prev-btn').click();
                } else if (e.key === 'ArrowRight') {
                    document.getElementById('next-btn').click();
                }
            }
        });

        // Mentor Slider
        class MentorSlider {
            constructor() {
                this.currentSlide = 0;
                this.slides = document.querySelectorAll('.ment-card-1, .ment-card-2, .ment-card-3');
                this.dots = document.querySelectorAll('.ment-dot-1, .ment-dot-2, .ment-dot-3');
                this.prevBtn = document.getElementById('mentor-prev-btn');
                this.nextBtn = document.getElementById('mentor-next-btn');
                this.sliderContainer = document.querySelector('.ment-cards');
                this.autoSlideInterval = null;

                this.init();
            }

            init() {
                // Check if we're on mobile
                if (window.innerWidth <= 768) {
                    this.showMobileControls();
                    this.addEventListeners();
                    this.startAutoSlide();
                    this.addTouchSupport();
                }

                // Re-check on window resize
                window.addEventListener('resize', () => {
                    if (window.innerWidth <= 768) {
                        this.showMobileControls();
                        if (!this.autoSlideInterval) {
                            this.startAutoSlide();
                        }
                    } else {
                        this.hideMobileControls();
                        this.stopAutoSlide();
                    }
                });
            }

            showMobileControls() {
                this.prevBtn.style.display = 'block';
                this.nextBtn.style.display = 'block';
                document.getElementById('ment-slider-dots').style.display = 'flex';
            }

            hideMobileControls() {
                this.prevBtn.style.display = 'none';
                this.nextBtn.style.display = 'none';
                document.getElementById('ment-slider-dots').style.display = 'none';
            }

            addEventListeners() {
                this.prevBtn.addEventListener('click', () => this.prevSlide());
                this.nextBtn.addEventListener('click', () => this.nextSlide());

                this.dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => this.goToSlide(index));
                });

                // Pause auto-slide on hover
                const slider = document.querySelector('.ment-slider');
                slider.addEventListener('mouseenter', () => this.stopAutoSlide());
                slider.addEventListener('mouseleave', () => this.startAutoSlide());
            }

            addTouchSupport() {
                let startX = 0;
                let endX = 0;

                this.sliderContainer.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                });

                this.sliderContainer.addEventListener('touchend', (e) => {
                    endX = e.changedTouches[0].clientX;
                    this.handleSwipe(startX, endX);
                });
            }

            handleSwipe(startX, endX) {
                const diff = startX - endX;
                const threshold = 50;

                if (Math.abs(diff) > threshold) {
                    if (diff > 0) {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                }
            }

            showSlide(index) {
                const cardWidth = 280 + 16; // card width + gap
                const scrollPosition = index * cardWidth;

                this.sliderContainer.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });

                // Update dots
                this.dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === index);
                });

                this.currentSlide = index;
            }

            nextSlide() {
                const nextIndex = (this.currentSlide + 1) % this.slides.length;
                this.showSlide(nextIndex);
            }

            prevSlide() {
                const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
                this.showSlide(prevIndex);
            }

            goToSlide(index) {
                this.showSlide(index);
            }

            startAutoSlide() {
                if (window.innerWidth <= 768) {
                    this.autoSlideInterval = setInterval(() => {
                        this.nextSlide();
                    }, 4000); // Change slide every 4 seconds
                }
            }

            stopAutoSlide() {
                if (this.autoSlideInterval) {
                    clearInterval(this.autoSlideInterval);
                    this.autoSlideInterval = null;
                }
            }
        }



        // Initialize all animations and interactions when page loads
        window.addEventListener('load', () => {
            // Add loaded class to body for CSS animations
            document.body.classList.add('loaded');

            // Trigger any initial animations
            const heroElements = document.querySelectorAll('.hero-text > *');
            heroElements.forEach((element, index) => {
                setTimeout(() => {
                    element.classList.add('fade-in-up');
                }, index * 200);
            });
        });

        // Initialize mentor slider when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new MentorSlider();
        });

        //FAQ Section//
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

        //Testimonial Section//
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
                const testimonialIndex = parseInt(avatar.dataset.testimonialIndex);
                updateTestimonial(testimonialIndex);
            });
        });

        nextButton.addEventListener('click', nextTestimonial);
        prevButton.addEventListener('click', prevTestimonial);

        updateTestimonial(currentIndex);

        autoPlayInterval = setInterval(nextTestimonial, 5000);


        // How it works //
        document.addEventListener('DOMContentLoaded', () => {
            const steps = document.querySelectorAll('.hiw-step');
            const ctaButton = document.querySelector('.cta-button');
            const decorativeCircles = document.querySelectorAll('.hiw-decorative-circle');
            const card = document.querySelector('.hiw-card');

            steps.forEach((step, index) => {
                const stepCircle = step.querySelector('.hiw-step-circle');
                const stepIcon = step.querySelector('.hiw-step-icon');

                stepCircle.addEventListener('click', () => {
                    stepCircle.style.animation = 'none';
                    setTimeout(() => {
                        stepCircle.style.animation = 'stepPulse 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    }, 10);
                });
            });

            ctaButton.addEventListener('click', (e) => {
                const ripple = document.createElement('span');
                const rect = ctaButton.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
      position: absolute;
      width: ${size}px;
      height: ${size}px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      left: ${x}px;
      top: ${y}px;
      transform: scale(0);
      animation: ripple 0.6s ease-out;
      pointer-events: none;
      z-index: 0;
    `;

                ctaButton.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);

                console.log('Contact button clicked!');
            });

            window.addEventListener('mousemove', (e) => {
                const moveX = (e.clientX / window.innerWidth - 0.5) * 30;
                const moveY = (e.clientY / window.innerHeight - 0.5) * 30;

                decorativeCircles.forEach((circle, index) => {
                    const speed = (index + 1) * 0.5;
                    circle.style.transform = `translate(${moveX * speed}px, ${moveY * speed}px)`;
                });
            });

            const observerOptions = {
                threshold: 0.2,
                rootMargin: '0px 0px -100px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, observerOptions);

            steps.forEach(step => observer.observe(step));

            const style = document.createElement('style');
            style.textContent = `
    @keyframes ripple {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }

    @keyframes stepPulse {
      0% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.2) rotate(10deg);
      }
      100% {
        transform: scale(1) rotate(0deg);
      }
    }
  `;
            document.head.appendChild(style);

            let tiltTimeout;

            card.addEventListener('mousemove', (e) => {
                if (window.innerWidth > 768) {
                    clearTimeout(tiltTimeout);

                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const rotateX = ((y - centerY) / centerY) * -5;
                    const rotateY = ((x - centerX) / centerX) * 5;

                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.01)`;
                }
            });

            card.addEventListener('mouseleave', () => {
                if (window.innerWidth > 768) {
                    tiltTimeout = setTimeout(() => {
                        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
                    }, 100);
                }
            });

            card.style.transition = 'transform 0.3s ease-out';

            document.documentElement.style.scrollBehavior = 'smooth';
        });


        //Course Bundle Section//

        // const mobileGrid = document.getElementById('mobile-grid');
        // let isAppending = false;
        // const originalCards = Array.from(mobileGrid.children); // Gamma, Alpha, Beta
        // let appendIndex = 1; // Next to append: 1=Alpha, 2=Beta, 0=Gamma, then 1 again

        // mobileGrid.addEventListener('scroll', () => {
        //     if (isAppending) return;

        //     const threshold = 200; // pixels from edge

        //     // When near the end, append 3 cards and remove 3 from start to keep infinite scroll
        //     if (mobileGrid.scrollLeft + mobileGrid.clientWidth >= mobileGrid.scrollWidth - threshold) {
        //         isAppending = true;
        //         for (let i = 0; i < 3; i++) {
        //             const cardToAppend = originalCards[appendIndex].cloneNode(true);
        //             mobileGrid.appendChild(cardToAppend);
        //             appendIndex = (appendIndex + 1) % 3; // Cycle: 1->2->0->1
        //         }
        //         // Remove 3 from start to prevent accumulation
        //         for (let i = 0; i < 3; i++) {
        //             if (mobileGrid.children.length > 3) {
        //                 mobileGrid.removeChild(mobileGrid.children[0]);
        //             }
        //         }
        //         isAppending = false;
        //     }
        // });
    </script>

</body>

</html>