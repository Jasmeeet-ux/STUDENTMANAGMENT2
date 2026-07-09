 <?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ace the Interview - Master Your Next Job Interview</title>
    <link rel="stylesheet" href="Course.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #666666;
            background-color: #FFFFFF;
        }

        .Course-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Banner */
        .Course-hero-banner {
            background: #000000;
            background: -moz-linear-gradient(left, #000000 0, #EBBE81 100%);
            background: -webkit-linear-gradient(left, #000000 0, #EBBE81 100%);
            color: white;
            padding: 106px 0;
            position: relative;
            overflow: hidden;
        }

        .Course-hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)" /></svg>');
            opacity: 0.3;
        }

        .Course-hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .Course-course-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .Course-category,
        .Course-language {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .Course-course-title {
            font-size: 3.5rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .Course-course-subtitle {
            font-size: 1.25rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .Course-hero-stats {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
        }

        .Course-hero-stats .Course-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .Course-hero-stats .Course-stat i {
            color: #FFB400;
        }

        .Course-cta-button {
            background: #be8652;
            color: white;
            border: none;
            padding: 18px 36px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(190, 134, 82, 0.3);
        }

        .Course-cta-button:hover {
            background: #a06a3e;
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(190, 134, 82, 0.4);
        }

        .Course-cta-button.large {
            padding: 20px 40px;
            font-size: 20px;
        }

        .Course-course-preview {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: perspective(1000px) rotateY(-5deg) rotateX(5deg);
            transition: transform 0.3s ease;
        }

        .Course-course-preview:hover {
            transform: perspective(1000px) rotateY(-2deg) rotateX(2deg);
        }

        .Course-video {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            /* 16:9 aspect ratio */
            overflow: hidden;
            border-radius: 20px;
        }

        .Course-video iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 20px;
        }

        .Course-course-preview img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
        }

        .Course-play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .Course-play-button:hover {
            transform: translate(-50%, -50%) scale(1.1);
            background: white;
        }

        .Course-play-button i {
            color: #be8652;
            font-size: 28px;
            margin-left: 4px;
        }

        /* Certificate Section  */
        .Course-cert-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            min-height: 80vh;
            padding: 40px 20px;
        }

        .Course-cert-content {
            animation: slideInLeft 0.8s ease-out;
        }

        .Course-cert-main-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 50px;
            line-height: 1.2;
        }

        .Course-cert-benefits-list {
            display: flex;
            flex-direction: column;
            gap: 35px;
        }

        .Course-cert-benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .Course-cert-benefit-item:nth-child(1) {
            animation-delay: 0.2s;
        }

        .Course-cert-benefit-item:nth-child(2) {
            animation-delay: 0.4s;
        }

        .Course-cert-benefit-item:nth-child(3) {
            animation-delay: 0.6s;
        }

        .Course-cert-benefit-item:hover {
            transform: translateX(10px);
        }

        .Course-cert-icon-wrapper {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #be8652 0%, #a36f3f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(190, 134, 82, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .Course-cert-benefit-item:hover .Course-cert-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(190, 134, 82, 0.4);
        }

        .Course-cert-icon {
            width: 32px;
            height: 32px;
        }

        .Course-cert-benefit-content {
            flex: 1;
        }

        .Course-cert-benefit-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .Course-cert-benefit-description {
            font-size: 0.9rem;
            color: #000;
            line-height: 1.6;
        }

        .Course-cert-certificate-section {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: slideInRight 0.8s ease-out;
        }

        .Course-cert-certificate-frame {
            position: relative;
            background: linear-gradient(145deg, #6b4c2a 0%, #8c6a3a 100%);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 15px 40px rgba(139, 94, 44, 0.6),
                0 5px 15px rgba(139, 94, 44, 0.4);
            transform: perspective(1000px) rotateY(-5deg);
            transition: transform 0.5s ease, box-shadow 0.5s ease;
            animation: float 3s ease-in-out infinite;
        }

        .Course-cert-certificate-frame:hover {
            transform: perspective(1000px) rotateY(0deg) scale(1.02);
            box-shadow: 0 20px 50px rgba(139, 94, 44, 0.7),
                0 8px 20px rgba(139, 94, 44, 0.5);
        }

        .Course-cert-certificate {
            position: relative;
            background: white;
            padding: 40px 30px;
            border-radius: 4px;
            width: 380px;
            box-shadow: inset 0 0 0 1px #e0e0e0;
            overflow: hidden;
        }

        .Course-cert-certificate-ribbon {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 80px;
            background: linear-gradient(135deg, #be8652 0%, #a36f3f 100%);
            clip-path: polygon(0 0, 100% 0, 100% 80%, 50% 65%, 0 80%);
            box-shadow: 0 4px 10px rgba(190, 134, 82, 0.4);
            animation: ribbonSwing 2s ease-in-out infinite;
        }

        .Course-cert-certificate-badge {
            position: absolute;
            top: 20px;
            left: 30px;
            width: 50px;
            height: 50px;
            animation: badgePulse 2s ease-in-out infinite;
        }

        .Course-cert-certificate-header {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .Course-cert-logo {
            width: 80px;
            margin-bottom: 5px;
        }

        .Course-cert-subtitle {
            font-size: 0.75rem;
            color: #000;
            margin: 0;
        }

        .Course-cert-certificate-title {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 15px 0;
            letter-spacing: 2px;
        }

        .Course-cert-certificate-text {
            text-align: center;
            font-size: 0.85rem;
            color: #000;
            margin: 10px 0 5px 0;
        }

        .Course-cert-certificate-recipient {
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 10px 0;
        }

        .Course-cert-certificate-course {
            text-align: center;
            font-size: 0.85rem;
            color: #000;
            margin: 5px 0 30px 0;
        }

        .Course-cert-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 5rem;
            font-weight: 900;
            color: rgba(200, 200, 200, 0.15);
            letter-spacing: 10px;
            user-select: none;
            pointer-events: none;
        }

        .Course-cert-certificate-footer {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        .Course-cert-signature {
            text-align: center;
        }

        .Course-cert-signature-line {
            width: 150px;
            height: 1px;
            background: #333;
            margin: 0 auto 8px auto;
        }

        .Course-cert-signature-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .Course-cert-signature-title {
            font-size: 0.75rem;
            color: #000;
            margin: 3px 0 0 0;
        }

        .Course-cert-illustration {
            position: absolute;
            right: -120px;
            bottom: -50px;
            width: 200px;
            height: 300px;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards, wave 3s ease-in-out infinite;
            filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.15));
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

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: perspective(1000px) rotateY(-5deg) translateY(0px);
            }

            50% {
                transform: perspective(1000px) rotateY(-5deg) translateY(-10px);
            }
        }

        @keyframes ribbonSwing {

            0%,
            100% {
                transform: translateX(-50%) rotate(0deg);
            }

            50% {
                transform: translateX(-50%) rotate(2deg);
            }
        }

        @keyframes badgePulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes wave {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-15px) rotate(2deg);
            }
        }

        @media (max-width: 1024px) {
            .Course-cert-container {
                grid-template-columns: 1fr;
                gap: 50px;
                /* max-width: 700px; */
                margin-left: auto;
                margin-right: auto;
            }

            .Course-cert-main-title {
                font-size: 2.2rem;
                margin-bottom: 40px;
                text-align: center;
            }

            .Course-cert-certificate-section {
                justify-content: center;
                order: -1;
            }

            .Course-cert-content {
                order: 1;
            }

            .Course-cert-illustration {
                right: -60px;
                bottom: -30px;
                width: 140px;
                height: 210px;
            }

            .Course-cert-certificate-frame {
                transform: perspective(1000px) rotateY(0deg);
                animation: floatTablet 3s ease-in-out infinite;
            }

            @keyframes floatTablet {

                0%,
                100% {
                    transform: perspective(1000px) rotateY(0deg) translateY(0px);
                }

                50% {
                    transform: perspective(1000px) rotateY(0deg) translateY(-10px);
                }
            }

            .Course-cert-benefits-list {
                gap: 30px;
            }

            .Course-cert-icon-wrapper {
                width: 55px;
                height: 55px;
            }

            .Course-cert-icon {
                width: 28px;
                height: 28px;
            }
        }

        @media (max-width: 768px) {
            .Course-cert-container {
                gap: 40px;
                max-width: 100%;
            }

            .Course-cert-main-title {
                font-size: 1.75rem;
                margin-bottom: 30px;
            }

            .Course-cert-benefits-list {
                gap: 25px;
            }

            .Course-cert-benefit-item {
                gap: 15px;
            }

            .Course-cert-icon-wrapper {
                width: 50px;
                height: 50px;
            }

            .Course-cert-icon {
                width: 26px;
                height: 26px;
            }

            .Course-cert-benefit-title {
                font-size: 1.15rem;
            }

            .Course-cert-benefit-description {
                font-size: 0.95rem;
            }

            .Course-cert-certificate-frame {
                padding: 18px;
                max-width: 100%;
            }

            .Course-cert-certificate {
                width: 100%;
                max-width: 340px;
                padding: 30px 20px;
            }

            .Course-cert-certificate-ribbon {
                width: 50px;
                height: 70px;
            }

            .Course-cert-certificate-badge {
                width: 45px;
                height: 45px;
                top: 18px;
                left: 25px;
            }

            .Course-cert-certificate-title {
                font-size: 1.4rem;
                letter-spacing: 1.5px;
            }

            .Course-cert-certificate-recipient {
                font-size: 1.15rem;
            }

            .Course-cert-watermark {
                font-size: 4rem;
                letter-spacing: 8px;
            }

            .Course-cert-illustration {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .Course-cert-main-title {
                font-size: 1.5rem;
                margin-bottom: 25px;
            }

            .Course-cert-benefits-list {
                gap: 20px;
            }

            .Course-cert-benefit-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 12px;
            }

            .Course-cert-benefit-item:hover {
                transform: translateY(-5px);
            }

            .Course-cert-icon-wrapper {
                width: 55px;
                height: 55px;
            }

            .Course-cert-icon {
                width: 28px;
                height: 28px;
            }

            .Course-cert-benefit-title {
                font-size: 1.1rem;
            }

            .Course-cert-benefit-description {
                font-size: 0.9rem;
                line-height: 1.5;
            }

            .Course-cert-certificate-frame {
                padding: 15px;
            }

            .Course-cert-certificate {
                max-width: 300px;
                padding: 25px 15px;
            }

            .Course-cert-certificate-ribbon {
                width: 45px;
                height: 65px;
            }

            .Course-cert-certificate-badge {
                width: 40px;
                height: 40px;
                top: 15px;
                left: 20px;
            }

            .Course-cert-logo {
                width: 70px;
            }

            .Course-cert-certificate-title {
                font-size: 1.2rem;
                letter-spacing: 1px;
                margin: 12px 0;
            }

            .Course-cert-certificate-text {
                font-size: 0.8rem;
            }

            .Course-cert-certificate-recipient {
                font-size: 1.05rem;
                margin: 8px 0;
            }

            .Course-cert-certificate-course {
                font-size: 0.8rem;
                margin: 5px 0 25px 0;
            }

            .Course-cert-watermark {
                font-size: 3rem;
                letter-spacing: 5px;
            }

            .Course-cert-certificate-footer {
                margin-top: 30px;
            }

            .Course-cert-signature-line {
                width: 120px;
            }

            .Course-cert-signature-name {
                font-size: 0.85rem;
            }

            .Course-cert-signature-title {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 360px) {
            .Course-cert-main-title {
                font-size: 1.3rem;
            }

            .Course-cert-certificate {
                max-width: 280px;
                padding: 20px 12px;
            }

            .Course-cert-certificate-title {
                font-size: 1.1rem;
            }

            .Course-cert-benefit-title {
                font-size: 1rem;
            }

            .Course-cert-benefit-description {
                font-size: 0.85rem;
            }

            .Course-cert-icon-wrapper {
                width: 50px;
                height: 50px;
            }

            .Course-cert-icon {
                width: 26px;
                height: 26px;
            }
        }

        @media (min-width: 1130px) {
            .Course-cert-illustration {
                width: 250px;
                height: 375px;
                right: -150px;
                bottom: -60px;
            }
        }

        @media (min-width: 1650px) {
            .Course-cert-illustration {
                width: 300px;
                height: 450px;
                right: -200px;
                bottom: -70px;
            }
        }

        /* Course Info Box */
        .Course-info-box {
            background: white;
            padding: 40px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        .Course-info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .Course-info-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-radius: 15px;
            background: #F7F8FA;
            transition: all 0.3s ease, transform 0.3s ease;
            border: 2px solid transparent;
        }

        .Course-info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: #be8652;
            background: white;
        }

        .Course-info-card i {
            font-size: 24px;
            color: #be8652;
            width: 40px;
            text-align: center;
        }

        .Course-info-content {
            display: flex;
            flex-direction: column;
        }

        .Course-info-label {
            font-size: 14px;
            color: #666666;
            margin-bottom: 4px;
        }

        .Course-info-value {
            font-size: 18px;
            font-weight: 600;
            color: #333333;
        }

        .Course-content-wrapper {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 60px;
        }

        .Course-content-left {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        /* Tabs Navigation */
        .tabs-navigation {
            display: flex;
            gap: 8px;
            margin-bottom: 40px;
            background: #F7F8FA;
            padding: 8px;
            border-radius: 15px;
        }

        .Course-tab-button {
            flex: 1;
            background: transparent;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #666666;
        }

        .Course-tab-button.active,
        .Course-tab-button:hover {
            background: white;
            color: #be8652;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Tab Content */
        .Course-tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .Course-tab-content.active {
            display: block;
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

        .Course-tab-content h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333333;
        }

        .Course-tab-content h3 {
            font-size: 1.25rem;
            margin: 30px 0 15px 0;
            color: #be8652;
        }

        .Course-tab-content p {
            margin-bottom: 20px;
            line-height: 1.7;
            color: #666666;
        }

        .Course-tab-content ul {
            list-style: none;
            margin: 20px 0;
        }

        .Course-tab-content ul li {
            padding: 8px 0 8px 25px;
            position: relative;
            color: #666666;
        }

        .Course-tab-content ul li::before {
            content: '•';
            color: #be8652;
            position: absolute;
            left: 0;
            font-weight: bold;
            font-size: 20px;
        }

        /* Learning Outcomes */
        .Course-learning-outcomes {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            gap: 20px;
            margin: 20px auto 10px;
            max-width: 85%;
            justify-content: center;
        }

        .Course-outcome {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
            padding: 10px;
            background: #F0F9FF;
            border-radius: 10px;
            border-left: 4px solid #be8652;
            max-width: 250px;
        }

        .Course-outcome img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .Course-outcome span {
            text-align: center;
            font-size: 14px;
        }

        /* Requirements */
        .Course-requirements-list {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }

        .Course-requirement {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #FEF3F2;
            border-radius: 15px;
            border-left: 4px solid #E53E3E;
        }

        .Course-requirement i {
            color: #E53E3E;
            font-size: 20px;
            min-width: 20px;
        }

        .Course-no-prior-knowledge {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #F0FDF4;
            border-radius: 15px;
            border-left: 4px solid #EBBE81;
            margin-top: 30px;
        }

        .Course-no-prior-knowledge i {
            color: #EBBE81;
            font-size: 20px;
        }

        /* Curriculum */
        .Course-curriculum-modules {
            display: grid;
            gap: 20px;
        }

        .Course-module {
            border: 2px solid #E5E5E5;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .Course-module:hover {
            border-color: #be8652;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .Course-module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: #F7F8FA;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .Course-module-header:hover {
            background: #F7F8FA;
        }

        .Course-module-title {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            color: #333333;
        }

        .Course-module-title i {
            color: #be8652;
            font-size: 20px;
        }

        .Course-module-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #666666;
            font-size: 14px;
        }

        .Course-module-meta i {
            transition: transform 0.3s ease;
        }

        .Course-module.expanded .Course-module-meta i {
            transform: rotate(180deg);
        }

        .Course-module-content {
            display: none;
            padding: 0 25px 25px 25px;
            background: white;
        }

        .Course-module-content.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .Course-lesson {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #F7F8FA;
        }

        .Course-lesson:last-child {
            border-bottom: none;
        }

        .Course-lesson i {
            color: #666666;
            font-size: 16px;
            min-width: 16px;
        }

        .Course-lesson span:first-of-type {
            flex: 1;
            color: #333333;
        }

        .Course-lesson-time {
            color: #666666;
            font-size: 14px;
            min-width: 60px;
            text-align: right;
        }

        .Course-free-preview {
            background: #EBBE81;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Sidebar */
        .Course-sidebar {
            display: grid;
            gap: 30px;
            height: fit-content;
        }

        .Course-instructor-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .Course-instructor-card h3 {
            margin-bottom: 25px;
            color: #333333;
            font-size: 1.5rem;
        }

        .Course-instructor-profile {
            text-align: center;
        }

        .Course-instructor-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid #be8652;
        }

        .Course-instructor-info h4 {
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: #333333;
        }

        .Course-instructor-title {
            color: #be8652;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .Course-instructor-stats {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }

        .Course-instructor-stats .Course-stat {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            color: #666666;
        }

        .Course-instructor-stats .Course-stat i {
            color: #be8652;
        }

        .Course-instructor-bio {
            text-align: left;
            color: #666666;
            line-height: 1.6;
            margin: 0;
        }

        /* Related Courses */
        .Course-related-courses {
            background: white;
            padding: 40px 0;
        }

        .Course-related-courses h2 {
            text-align: center;
            margin-bottom: 50px;
            color: #333333;
            font-size: 2.5rem;
        }

        .Course-courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .Course-course-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .Course-course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            border-color: #be8652;
        }

        .Course-course-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .Course-course-info {
            padding: 25px;
        }

        .Course-course-info h3 {
            margin-bottom: 10px;
            color: #333333;
            font-size: 1.25rem;
        }

        .Course-course-instructor {
            color: #666666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .Course-course-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .Course-course-rating .Course-stars {
            color: #EBBE81;
        }

        .Course-course-rating span:last-child {
            color: #666666;
            font-size: 14px;
        }

        .Course-course-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #be8652;
        }



        /* Footer CTA */
        .Course-footer-cta {
            background: linear-gradient(135deg, #333333 0%, #be8652 100%);
            color: white;
            text-align: center;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .Course-footer-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)" /></svg>');
        }

        .Course-cta-content {
            position: relative;
            z-index: 1;
        }

        .Course-cta-content h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .Course-cta-content p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .Course-price-info {
            margin: 40px 0;
        }

        .Course-original-price {
            font-size: 1.5rem;
            text-decoration: line-through;
            color: #EBBE81;
            margin-right: 15px;
        }

        .Course-current-price {
            font-size: 3rem;
            font-weight: 700;
            color: #EBBE81;
            margin-right: 15px;
        }

        .Course-discount {
            background: #E53E3E;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .Course-guarantee {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            color: #EBBE81;
            font-size: 14px;
        }

        .Course-guarantee i {
            color: #EBBE81;
        }

        /* Responsive Design */
        @media (min-width: 1350px) {
            .Course-hero-left {
                margin-left: -55px;
            }

            .Course-hero-right {
                margin-right: -55px;
            }
        }

        @media (min-width: 768px) {
            /* Reviews list is now flex on all devices */
        }

        @media (max-width: 768px) {
            .Course-hero-content {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .Course-course-title {
                font-size: 2.5rem;
            }

            .Course-hero-stats {
                justify-content: center;
            }

            .Course-content-wrapper {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .Course-sidebar {
                order: -1;
            }

            .Course-info-cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .Course-info-card {
                padding: 10px;
            }

            .Course-info-card i {
                font-size: 20px;
                width: 30px;
            }

            .Course-info-label {
                font-size: 12px;
            }

            .Course-info-value {
                font-size: 16px;
            }

            .tabs-navigation {
                flex-wrap: wrap;
            }

            .Course-tab-button {
                flex: none;
                min-width: calc(50% - 4px);
                margin-bottom: 8px;
            }

            .Course-courses-grid {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 30px;
                -webkit-overflow-scrolling: touch;
            }

            .Course-courses-grid::-webkit-scrollbar {
                display: none;
            }

            .Course-course-card {
                flex: 0 0 280px;
                scroll-snap-align: start;
            }

            .Course-cta-content h2 {
                font-size: 2rem;
            }

            .Course-current-price {
                font-size: 2rem;
            }

            .slider-btn {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .Course-container {
                padding: 0 15px;
            }

            .Course-course-title {
                font-size: 2rem;
            }

            .Course-hero-stats {
                flex-direction: column;
                gap: 15px;
            }

            .Course-info-cards {
                gap: 8px;
            }

            .Course-info-card {
                padding: 15px;
            }

            .Course-info-card i {
                font-size: 18px;
                width: 28px;
            }

            .Course-info-label {
                font-size: 11px;
            }

            .Course-info-value {
                font-size: 14px;
            }

            .Course-tab-button {
                min-width: 100%;
                margin-bottom: 8px;
            }

        }

        /* Animations */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        @keyframes staggerIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes parallaxSlide {
            from {
                transform: translateY(0);
            }

            to {
                transform: translateY(-20px);
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

        .loading {
            animation: pulse 1.5s infinite;
        }

        /* Page load animation */
        body {
            animation: fadeInPage 0.8s ease-out;
        }

        @keyframes fadeInPage {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Hero parallax effect */
        .hero-banner::before {
            animation: parallaxSlide 10s ease-in-out infinite alternate;
        }

        /* Staggered animations for lists */
        .Course-learning-outcomes .Course-outcome,
        .Course-requirements-list .Course-requirement {
            animation: staggerIn 0.6s ease-out both;
        }

        .Course-learning-outcomes .Course-outcome:nth-child(1) {
            animation-delay: 0.1s;
        }

        .Course-learning-outcomes .Course-outcome:nth-child(2) {
            animation-delay: 0.2s;
        }

        .Course-learning-outcomes .Course-outcome:nth-child(3) {
            animation-delay: 0.3s;
        }

        .Course-learning-outcomes .Course-outcome:nth-child(4) {
            animation-delay: 0.4s;
        }

        .Course-learning-outcomes .Course-outcome:nth-child(5) {
            animation-delay: 0.5s;
        }

        .Course-learning-outcomes .Course-outcome:nth-child(6) {
            animation-delay: 0.6s;
        }

        .Course-requirements-list .Course-requirement:nth-child(1) {
            animation-delay: 0.1s;
        }

        .Course-requirements-list .Course-requirement:nth-child(2) {
            animation-delay: 0.2s;
        }

        .Course-requirements-list .Course-requirement:nth-child(3) {
            animation-delay: 0.3s;
        }

        .Course-requirements-list .Course-requirement:nth-child(4) {
            animation-delay: 0.4s;
        }



        /* Enhanced hover effects */
        .Course-info-card:hover i {
            animation: pulse 0.6s ease-in-out;
        }

        .Course-course-card:hover img {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        .Course-cta-button {
            animation: bounceIn 0.8s ease-out;
        }



        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {

            .Course-learning-outcomes .Course-outcome,
            .Course-requirements-list .Course-requirement,
            .Course-cta-button,
            body {
                animation: none;
            }

            .Course-hero-banner::before {
                animation: none;
            }

            .Course-info-card:hover i {
                animation: none;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus states for accessibility */
        .Course-cta-button:focus,
        .Course-tab-button:focus {
            outline: 3px solid #EBBE81;
            outline-offset: 2px;
        }

        /* Print styles */
        @media print {

            .Course-hero-banner,
            .Course-footer-cta,
            .Course-cta-button {
                display: none;
            }

            .Course-main-content {
                padding: 0;
            }

            .Course-content-wrapper {
                grid-template-columns: 1fr;
            }
        }

        /* Responsive padding for tab content
        @media only screen and (max-width:1500px) {
            .Course-content-left {
                padding: 20px;
            }
        } */

        /* FAQ Section  */
        .Course-faq-container {
            width: 100%;
            max-width: auto;
            margin: 0 auto;
        }

        .Course-faq-wrapper {
            background: white;
            padding: 80px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.04);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            border: 1px solid rgba(190, 134, 82, 0.08);
        }

        .Course-faq-header {
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: sticky;
            top: 40px;
            align-self: start;
        }

        .Course-faq-badge {
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

        .Course-faq-icon {
            font-size: 16px;
        }

        .Course-FaqH1 {
            font-size: 52px;
            font-weight: 700;
            color: #0f0f0f;
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .Course-faq-highlight {
            color: #be8652;
            display: inline-block;
            position: relative;
        }

        .Course-faq-highlight::after {
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

        .Course-faq-subtitle {
            color: #252525;
            font-size: 17px;
            line-height: 1.7;
            max-width: 420px;
            font-weight: 400;
        }

        .Course-faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .Course-faq-item {
            background: #fafafa;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .Course-faq-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(190, 134, 82, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .Course-faq-item:hover::before {
            left: 100%;
        }

        .Course-faq-item:hover {
            border-color: #be8652;
            box-shadow: 0 12px 32px rgba(190, 134, 82, 0.15);
            background: white;
            transform: translateY(-3px) scale(1.01);
        }

        .Course-faq-question {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px;
            cursor: pointer;
            user-select: none;
            transition: padding 0.25s ease;
        }

        .Course-faq-question span {
            font-size: 17px;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -0.01em;
        }

        .Course-faq-toggle-btn {
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

        .Course-faq-toggle-btn:hover {
            background: linear-gradient(135deg, #be8652 0%, #be8652 100%);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(190, 134, 82, 0.3);
        }

        .Course-faq-item.active {
            border-color: #be8652;
            background: white;
            box-shadow: 0 12px 32px rgba(190, 134, 82, 0.18);
        }

        .Course-faq-item.active .Course-faq-toggle-btn {
            background: linear-gradient(135deg, #be8652 0%, #be8652 100%);
            color: white;
            transform: rotate(180deg);
            box-shadow: 0 4px 12px rgba(190, 134, 82, 0.3);
        }

        .Course-faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), padding 0.5s ease, opacity 0.4s ease;
            opacity: 0;
        }

        .Course-faq-item.active .Course-faq-answer {
            max-height: 300px;
            padding: 0 28px 28px 28px;
            opacity: 1;
        }

        .Course-faq-answer p {
            color: #252525;
            font-size: 15px;
            line-height: 1.75;
            font-weight: 400;
        }

        @media (max-width: 968px) {
            .Course-faq-wrapper {
                grid-template-columns: 1fr;
                gap: 48px;
                padding: 48px;
            }

            .Course-faq-header {
                position: static;
            }

            .Course-FaqH1 {
                font-size: 40px;
            }

            .Course-faq-subtitle {
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .Course-faq-wrapper {
                padding: 32px 24px;
            }

            .Course-FaqH1 {
                font-size: 32px;
            }

            .Course-faq-question {
                padding: 20px;
            }

            .Course-faq-question span {
                font-size: 15px;
            }

            .Course-faq-toggle-btn {
                width: 36px;
                height: 36px;
            }

            .Course-faq-item.active .Course-faq-answer {
                padding: 0 20px 20px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- header  -->
    <?php include ('includes/header.php') ?>
    <!-- Course Banner -->
    <section class="Course-hero-banner">
        <div class="Course-container">
            <div class="Course-hero-content">
                <div class="Course-hero-left">
                    <div class="Course-course-meta">
                        <span class="Course-category">Career</span>
                        <span class="Course-language">English</span>
                    </div>
                    <h1 class="Course-course-title">Culture of Internet</h1>
                    <p class="Course-course-subtitle">Master the art of interviewing and land your dream job with confidence
                    </p>
                    <div class="Course-hero-stats">
                        <div class="Course-stat">
                            <i class="fas fa-star"></i>
                            <span>4.8 (2,847 reviews)</span>
                        </div>
                        <div class="Course-stat">
                            <i class="fas fa-users"></i>
                            <span>12,354 students</span>
                        </div>
                    </div>
                    <button class="Course-cta-button" onclick="scrollToPricing()">
                        <i class="fas fa-shopping-cart"></i>
                        Buy Now
                    </button>
                </div>
                <div class="Course-hero-right">
                    <div class="Course-course-preview">
                        <div class="Course-video">
                            <iframe width="560" height="315"
                                src="https://www.youtube.com/embed/E3FysUo1RZ8?si=MCHvDG35md45UKQI"
                                title="YouTube video player" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen=""></iframe>
                            <a href="https://www.youtube.com/@jobvacancyresult" target="_blank"> <img
                                    src="images/JVR.png" alt="img" height="100px"></a>
                        </div>
                        <div class="Course-play-button">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Info Box -->
    <section class="Course-info-box">
        <div class="Course-container">
            <div class="Course-info-cards">
                <div class="Course-info-card">
                    <i class="fas fa-book-open"></i>
                    <div class="Course-info-content">
                        <span class="Course-info-label">Lessons</span>
                        <span class="Course-info-value">31</span>
                    </div>
                </div>
                <div class="Course-info-card">
                    <i class="fas fa-clock"></i>
                    <div class="Course-info-content">
                        <span class="Course-info-label">Duration</span>
                        <span class="Course-info-value">2.3 hours</span>
                    </div>
                </div>
                <div class="Course-info-card">
                    <i class="fas fa-signal"></i>
                    <div class="Course-info-content">
                        <span class="Course-info-label">Level</span>
                        <span class="Course-info-value">Beginner</span>
                    </div>
                </div>
                <div class="Course-info-card">
                    <i class="fas fa-certificate"></i>
                    <div class="Course-info-content">
                        <span class="Course-info-label">Certificate</span>
                        <span class="Course-info-value">Yes</span>
                    </div>
                </div>
                <div class="Course-info-card">
                    <i class="fas fa-user-tie"></i>
                    <div class="Course-info-content">
                        <span class="Course-info-label">Instructor</span>
                        <span class="Course-info-value">Sarah Johnson</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="Course-main-content">
        <div class="Course-container">
            <div class="Course-content-wrapper">
                <div class="Course-content-left">
                    <!-- About Course Tab -->
                    <div class="Course-tab-content active" id="about">
                        <h2>About This Course</h2>
                        <p>Transform your interview performance and land your dream job with this comprehensive
                            interview mastery course. Designed by industry experts with over 15 years of hiring
                            experience, this course will teach you everything you need to know to excel in any interview
                            situation.</p>

                        <h3>What You'll Learn:</h3>
                        <div class="Course-learning-outcomes">
                            <div class="Course-outcome">
                                <img src="https://images.pexels.com/photos/3184430/pexels-photo-3184430.jpeg?auto=compress&cs=tinysrgb&w=200" alt="Interview Questions">
                                <span>Master the art of answering tough interview questions</span>
                            </div>
                            <div class="Course-outcome">
                                <img src="https://images.pexels.com/photos/3184461/pexels-photo-3184461.jpeg?auto=compress&cs=tinysrgb&w=200" alt="Confidence Building">
                                <span>Build unshakeable confidence for any interview</span>
                            </div>
                            <div class="Course-outcome">
                                <img src="https://images.pexels.com/photos/3184339/pexels-photo-3184339.jpeg?auto=compress&cs=tinysrgb&w=200" alt="Resume Creation">
                                <span>Create compelling resumes that get noticed</span>
                            </div>
                            <div class="Course-outcome">
                                <img src="https://images.pexels.com/photos/3184338/pexels-photo-3184338.jpeg?auto=compress&cs=tinysrgb&w=200" alt="Salary Negotiation">
                                <span>Negotiate salary and benefits effectively</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="Course-sidebar">
                    <!-- Instructor Section -->
                    <div class="Course-instructor-card">
                        <h3>Your Instructor</h3>
                        <div class="Course-instructor-profile">
                            <img src="https://images.pexels.com/photos/3769021/pexels-photo-3769021.jpeg?auto=compress&cs=tinysrgb&w=200"
                                alt="Sarah Johnson" class="Course-instructor-photo">
                            <div class="Course-instructor-info">
                                <h4>Sarah Johnson</h4>
                                <p class="Course-instructor-title">Senior HR Director & Career Coach</p>
                                <div class="Course-instructor-stats">
                                    <div class="Course-stat">
                                        <i class="fas fa-users"></i>
                                        <span>45K+ Students</span>
                                    </div>
                                    <div class="Course-stat">
                                        <i class="fas fa-star"></i>
                                        <span>4.9 Rating</span>
                                    </div>
                                    <div class="Course-stat">
                                        <i class="fas fa-book"></i>
                                        <span>12 Courses</span>
                                    </div>
                                </div>
                                <p class="Course-instructor-bio">Sarah has 15+ years of experience in talent acquisition and
                                    has personally interviewed over 10,000 candidates. She's helped thousands of
                                    professionals land their dream jobs.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
    </section>

    <!-- Certificate Section  -->
    <div class="Course-cert-container">
        <div class="Course-cert-content">
            <h1 class="Course-cert-main-title">Benefits Of An Alison Certificate</h1>

            <div class="Course-cert-benefits-list">
                <div class="Course-cert-benefit-item">
                    <div class="Course-cert-icon-wrapper">
                        <svg class="Course-cert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="Course-cert-benefit-content">
                        <h3 class="Course-cert-benefit-title">Certify Your Skills</h3>
                        <p class="Course-cert-benefit-description">A CPD accredited Alison Certificate certifies the skills you've learned</p>
                    </div>
                </div>

                <div class="Course-cert-benefit-item">
                    <div class="Course-cert-icon-wrapper">
                        <svg class="Course-cert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12H15M12 9V15M3 12C3 13.1819 3.23279 14.3522 3.68508 15.4442C4.13738 16.5361 4.80031 17.5282 5.63604 18.364C6.47177 19.1997 7.46392 19.8626 8.55585 20.3149C9.64778 20.7672 10.8181 21 12 21C13.1819 21 14.3522 20.7672 15.4442 20.3149C16.5361 19.8626 17.5282 19.1997 18.364 18.364C19.1997 17.5282 19.8626 16.5361 20.3149 15.4442C20.7672 14.3522 21 13.1819 21 12C21 9.61305 20.0518 7.32387 18.364 5.63604C16.6761 3.94821 14.3869 3 12 3C9.61305 3 7.32387 3.94821 5.63604 5.63604C3.94821 7.32387 3 9.61305 3 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9 12H15M3 12C3 13.1819 3.23279 14.3522 3.68508 15.4442C4.13738 16.5361 4.80031 17.5282 5.63604 18.364C6.47177 19.1997 7.46392 19.8626 8.55585 20.3149C9.64778 20.7672 10.8181 21 12 21C13.1819 21 14.3522 20.7672 15.4442 20.3149C16.5361 19.8626 17.5282 19.1997 18.364 18.364C19.1997 17.5282 19.8626 16.5361 20.3149 15.4442C20.7672 14.3522 21 13.1819 21 12C21 9.61305 20.0518 7.32387 18.364 5.63604C16.6761 3.94821 14.3869 3 12 3C9.61305 3 7.32387 3.94821 5.63604 5.63604C3.94821 7.32387 3 9.61305 3 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <rect x="6" y="8" width="5" height="8" fill="white" rx="1" />
                            <rect x="13" y="8" width="5" height="8" fill="white" rx="1" />
                        </svg>
                    </div>
                    <div class="Course-cert-benefit-content">
                        <h3 class="Course-cert-benefit-title">Stand Out From The Crowd</h3>
                        <p class="Course-cert-benefit-description">Add your Alison Certification to your resumé and stay ahead of the competition</p>
                    </div>
                </div>

                <div class="Course-cert-benefit-item">
                    <div class="Course-cert-icon-wrapper">
                        <svg class="Course-cert-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 7H16M20 12H16M20 17H16M12 3H4C3.46957 3 2.96086 3.21071 2.58579 3.58579C2.21071 3.96086 2 4.46957 2 5V19C2 19.5304 2.21071 20.0391 2.58579 20.4142C2.96086 20.7893 3.46957 21 4 21H12M12 3L14 6M12 3V7C12 7.53043 12.2107 8.03914 12.5858 8.41421C12.9609 8.78929 13.4696 9 14 9H18M14 6L18 9M14 6L18 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="Course-cert-benefit-content">
                        <h3 class="Course-cert-benefit-title">Advance in Your Career</h3>
                        <p class="Course-cert-benefit-description">Share your Alison Certification with potential employers to show off your skills and capabilities</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="Course-cert-certificate-section">
            <div class="Course-cert-certificate-frame">
                <div class="Course-cert-certificate">
                    <div class="Course-cert-certificate-ribbon"></div>
                    <div class="Course-cert-certificate-badge">
                        <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="25" cy="25" r="20" fill="white" stroke="#ddd" stroke-width="1" />
                            <path d="M25 15L28 22L35 23L30 28L31 35L25 32L19 35L20 28L15 23L22 22L25 15Z" fill="#ccc" />
                        </svg>
                    </div>
                    <div class="Course-cert-certificate-header">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 30'%3E%3Ctext x='10' y='20' font-family='Arial' font-size='14' font-weight='bold' fill='%23333'%3EAlison%3C/text%3E%3C/svg%3E" alt="Alison" class="Course-cert-logo">
                        <p class="Course-cert-subtitle">Empower Yourself</p>
                    </div>
                    <h2 class="Course-cert-certificate-title">CERTIFICATE</h2>
                    <p class="Course-cert-certificate-text">This certificate is awarded to</p>
                    <p class="Course-cert-certificate-recipient">Jane Doe</p>
                    <p class="Course-cert-certificate-course">for successfully completing this course</p>
                    <div class="Course-cert-watermark">SAMPLE</div>
                    <div class="Course-cert-certificate-footer">
                        <div class="Course-cert-signature">
                            <div class="Course-cert-signature-line"></div>
                            <p class="Course-cert-signature-name">Marie Richardson</p>
                            <p class="Course-cert-signature-title">Director of Education</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Courses -->
    <section class="Course-related-courses">
        <div class="Course-container">
            <h2>You Might Also Like</h2>
            <div class="Course-courses-grid">
                <div class="Course-course-card">
                    <img src="https://images.pexels.com/photos/3760263/pexels-photo-3760263.jpeg?auto=compress&cs=tinysrgb&w=300"
                        alt="Resume Writing Course">
                    <div class="Course-course-info">
                        <h3>Resume Writing Mastery</h3>
                        <p class="Course-course-instructor">by John Smith</p>
                        <div class="Course-course-rating">
                            <span class="Course-stars">★★★★★</span>
                            <span>4.7 (1,234)</span>
                        </div>
                    </div>
                </div>

                <div class="Course-course-card">
                    <img src="https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=300"
                        alt="LinkedIn Optimization">
                    <div class="Course-course-info">
                        <h3>LinkedIn Profile Optimization</h3>
                        <p class="Course-course-instructor">by Maria Garcia</p>
                        <div class="Course-course-rating">
                            <span class="Course-stars">★★★★★</span>
                            <span>4.6 (892)</span>
                        </div>
                    </div>
                </div>

                <div class="Course-course-card">
                    <img src="https://images.pexels.com/photos/3184338/pexels-photo-3184338.jpeg?auto=compress&cs=tinysrgb&w=300"
                        alt="Salary Negotiation">
                    <div class="Course-course-info">
                        <h3>Salary Negotiation Secrets</h3>
                        <p class="Course-course-instructor">by Robert Lee</p>
                        <div class="Course-course-rating">
                            <span class="Course-stars">★★★★☆</span>
                            <span>4.5 (567)</span>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Faq Section  -->
    <div class="Course-faq-container">
        <div class="Course-faq-wrapper">
            <div class="Course-faq-header">
                <h1 class="Course-FaqH1l">Frequently asked <span class="Course-faq-highlight">questions</span></h1>
                <p class="Course-faq-subtitle">Choose a plan that fits your business needs and budget. No hidden fees, no
                    surprises - just straightforward pricing for powerful financial management.</p>
            </div>

            <div class="Course-faq-list">
                <div class="Course-faq-item">
                    <div class="Course-faq-question">
                        <span>What is Nicepay?</span>
                        <button class="Course-faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="Course-faq-answer">
                        <p>Nicepay is an all-in-one financial management platform designed to simplify payments,
                            automate invoicing, track expenses in real-time, and deliver powerful insights to businesses
                            of all sizes.</p>
                    </div>
                </div>

                <div class="Course-faq-item">
                    <div class="Course-faq-question">
                        <span>How does Nicepay work?</span>
                        <button class="Course-faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="Course-faq-answer">
                        <p>Nicepay integrates seamlessly with your existing systems to provide comprehensive financial
                            management tools, automated workflows, and real-time analytics to help you manage your
                            business finances efficiently.</p>
                    </div>
                </div>

                <div class="Course-faq-item">
                    <div class="Course-faq-question">
                        <span>Is Nicepay secure?</span>
                        <button class="Course-faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="Course-faq-answer">
                        <p>Yes, Nicepay employs bank-level encryption and security protocols to ensure your financial
                            data is protected at all times. We comply with industry standards and regulations to keep
                            your information safe.</p>
                    </div>
                </div>

                <div class="Course-faq-item">
                    <div class="Course-faq-question">
                        <span>Can Nicepay integrate with other accounting software?</span>
                        <button class="Course-faq-toggle-btn" aria-label="Toggle answer">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M19 9L12 16L5 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="Course-faq-answer">
                        <p>Absolutely! Nicepay offers integrations with popular accounting software and business tools,
                            allowing you to sync data seamlessly and maintain a unified workflow across all your
                            platforms.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Section -->
  <?php include ('includes\footer.php') ?>
  
    <script>
        // Tab functionality
        function showTab(tabId) {
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.Course-tab-button');
            tabButtons.forEach(button => button.classList.remove('active'));

            // Hide all tab contents
            const tabContents = document.querySelectorAll('.Course-tab-content');
            tabContents.forEach(content => content.classList.remove('active'));

            // Show selected tab content
            document.getElementById(tabId).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }





        // Smooth scroll to pricing section
        function scrollToPricing() {
            document.getElementById('pricing').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        // Intersection Observer for animations
        function initScrollAnimations() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe elements that should animate on scroll
            const animatedElements = document.querySelectorAll('.Course-info-card, .Course-course-card, .Course-review');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        }

        // Entrance animations for staggered reveals
        function initEntranceAnimations() {
            // Stagger animations for info-cards
            const infoCards = document.querySelectorAll('.Course-info-card');
            infoCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.animationDelay = `${index * 0.1}s`;
                    card.classList.add('animate-entrance');
                }, 100);
            });

            // Stagger animations for course-cards
            const courseCards = document.querySelectorAll('.Course-course-card');
            courseCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.animationDelay = `${index * 0.15}s`;
                    card.classList.add('animate-entrance');
                }, 200);
            });


        }

        // Progress bar animation for rating breakdown
        function animateRatingBars() {
            const bars = document.querySelectorAll('.fill');
            bars.forEach((bar, index) => {
                const targetWidth = bar.style.width;
                bar.style.width = '0%';

                setTimeout(() => {
                    bar.style.transition = 'width 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                    bar.style.width = targetWidth;
                }, 500 + index * 200); // Stagger the bars
            });
        }

        // Course preview play button functionality
        function initVideoPreview() {
            const playButton = document.querySelector('.Course-play-button');
            if (playButton) {
                playButton.addEventListener('click', () => {
                    // Add a subtle animation
                    playButton.style.transform = 'translate(-50%, -50%) scale(0.9)';
                    setTimeout(() => {
                        playButton.style.transform = 'translate(-50%, -50%) scale(1.1)';
                    }, 150);
                    setTimeout(() => {
                        // Start the video autoplay
                        const iframe = document.querySelector('.Course-video iframe');
                        if (iframe && !iframe.src.includes('autoplay=1')) {
                            iframe.src += '&autoplay=1';
                        }
                        playButton.style.transform = 'translate(-50%, -50%) scale(1)';
                        // Hide the play button to reveal the video
                        playButton.style.display = 'none';
                    }, 300);
                });
            }
        }

        function initCTATracking() {
            const ctaButtons = document.querySelectorAll('.Course-cta-button');
            ctaButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    // Add ripple effect
                    const ripple = document.createElement('span');
                    const rect = button.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;

                    button.style.position = 'relative';
                    button.style.overflow = 'hidden';
                    button.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        }

        // Add ripple animation CSS
        function addRippleStyles() {
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
        }

        // Course card hover effects
        function initCourseCardEffects() {
            const courseCards = document.querySelectorAll('.Course-course-card');
            courseCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-8px) scale(1.02)';
                });

                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0) scale(1)';
                });
            });
        }

        // Navbar scroll effect (if you want to add a fixed navbar later)
        function initNavbarScroll() {
            let lastScrollY = window.scrollY;

            window.addEventListener('scroll', () => {
                const currentScrollY = window.scrollY;
                // You can add navbar show/hide logic here if needed
                lastScrollY = currentScrollY;
            });
        }



        function initInfoCardTilt() {
            const cards = document.querySelectorAll('.Course-info-card');
            cards.forEach(card => {
                card.style.transition = 'transform 0.3s ease'; // smooth transition

                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'perspective(600px) rotateY(10deg)';
                });

                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(600px) rotateY(0deg)';
                });
            });
        }

        // Initialize all functionality when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            initScrollAnimations();
            initEntranceAnimations();
            initVideoPreview();
            initCTATracking();
            initCourseCardEffects();
            addRippleStyles();
            initNavbarScroll();
            initInfoCardTilt();

            // Animate rating bars after a delay
            setTimeout(animateRatingBars, 1000);

            // Add loading states
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                if (!img.complete) {
                    img.classList.add('loading');
                    img.addEventListener('load', () => {
                        img.classList.remove('loading');
                    });
                }
            });
        });

        // Keyboard navigation for accessibility
        document.addEventListener('keydown', (e) => {
            // Tab navigation for custom elements
            if (e.key === 'Enter' || e.key === ' ') {
                const activeElement = document.activeElement;

                if (activeElement.classList.contains('Course-tab-button')) {
                    e.preventDefault();
                    activeElement.click();
                }




            }
        });

        // Performance optimization: Lazy loading for images
        function initLazyLoading() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                imageObserver.unobserve(img);
                            }
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        }

        // Error handling for failed image loads
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'IMG') {
                e.target.style.display = 'none';
                console.log('Image failed to load:', e.target.src);
            }
        }, true);

        // Add smooth transitions to all interactive elements
        function initSmoothTransitions() {
            const style = document.createElement('style');
            style.textContent = `
        * {
            transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .reduced-motion * {
            transition: none !important;
            animation: none !important;
        }
    `;
            document.head.appendChild(style);

            // Respect user's motion preferences
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.body.classList.add('reduced-motion');
            }
        }



        // Call smooth transitions on load
        document.addEventListener('DOMContentLoaded', initSmoothTransitions);

        // Add focus trap for modals (if you add them later)
        function trapFocus(element) {
            const focusableElements = element.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            element.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            lastElement.focus();
                            e.preventDefault();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            firstElement.focus();
                            e.preventDefault();
                        }
                    }
                }

                if (e.key === 'Escape') {
                    // Close modal logic would go here
                }
            });
        }

        // Console easter egg for developers
        console.log(`
🎓 Course Landing Page
Built with vanilla JavaScript, CSS Grid, and lots of ❤️

Features:
✅ Responsive Design
✅ Smooth Animations  
✅ Accessibility Features
✅ Performance Optimized
✅ SEO Friendly

Want to customize? Check out the modular CSS and JS structure!
`);

        //FAQ Section//
        document.addEventListener('DOMContentLoaded', () => {
            const faqItems = document.querySelectorAll('.Course-faq-item');
            const header = document.querySelector('.Course-faq-header');

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
                const question = item.querySelector('.Course-faq-question');
                const answer = item.querySelector('.Course-faq-answer');

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

        // Certificate Section //
        document.addEventListener('DOMContentLoaded', function() {
            const benefitItems = document.querySelectorAll('.Course-cert-benefit-item');
            const certificateFrame = document.querySelector('.Course-cert-certificate-frame');
            const illustration = document.querySelector('.Course-cert-illustration');
            const isMobile = window.innerWidth <= 480;
            const isTablet = window.innerWidth <= 1024 && window.innerWidth > 480;

            benefitItems.forEach((item, index) => {
                item.addEventListener('mouseenter', function() {
                    if (isMobile) {
                        this.style.transform = 'translateY(-5px)';
                    } else {
                        this.style.transform = 'translateX(10px)';
                    }
                });

                item.addEventListener('mouseleave', function() {
                    if (isMobile) {
                        this.style.transform = 'translateY(0)';
                    } else {
                        this.style.transform = 'translateX(0)';
                    }
                });

                item.addEventListener('touchstart', function() {
                    if (isMobile) {
                        this.style.transform = 'translateY(-5px) scale(1.02)';
                    }
                });

                item.addEventListener('touchend', function() {
                    if (isMobile) {
                        setTimeout(() => {
                            this.style.transform = 'translateY(0) scale(1)';
                        }, 200);
                    }
                });
            });

            if (certificateFrame) {
                certificateFrame.addEventListener('mouseenter', function() {
                    if (!isMobile) {
                        this.style.transform = 'perspective(1000px) rotateY(0deg) scale(1.02)';
                        this.style.boxShadow = '0 20px 50px rgba(0, 0, 0, 0.4), 0 8px 20px rgba(0, 0, 0, 0.25)';
                    }
                });

                certificateFrame.addEventListener('mouseleave', function() {
                    if (!isMobile && !isTablet) {
                        this.style.transform = 'perspective(1000px) rotateY(-5deg)';
                        this.style.boxShadow = '0 15px 40px rgba(0, 0, 0, 0.3), 0 5px 15px rgba(0, 0, 0, 0.2)';
                    }
                });

                certificateFrame.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(1.05)';
                });

                certificateFrame.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            benefitItems.forEach(item => {
                observer.observe(item);
            });

            if (illustration && !isMobile) {
                const moveIllustration = (e) => {
                    const rect = illustration.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;

                    const deltaX = (e.clientX - centerX) / 50;
                    const deltaY = (e.clientY - centerY) / 50;

                    illustration.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
                };

                document.addEventListener('mousemove', moveIllustration);
            }

            const iconWrappers = document.querySelectorAll('.Course-cert-icon-wrapper');
            iconWrappers.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1) rotate(5deg)';
                    this.style.boxShadow = '0 6px 20px rgba(190, 134, 82, 0.4)';
                });

                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0deg)';
                    this.style.boxShadow = '0 4px 15px rgba(190, 134, 82, 0.3)';
                });

                icon.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(1.15) rotate(10deg)';
                    this.style.boxShadow = '0 8px 25px rgba(190, 134, 82, 0.5)';
                });

                icon.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.transform = 'scale(1) rotate(0deg)';
                        this.style.boxShadow = '0 4px 15px rgba(190, 134, 82, 0.3)';
                    }, 200);
                });
            });

            const certificate = document.querySelector('.Course-cert-certificate');
            if (certificate) {
                certificate.addEventListener('click', function() {
                    this.style.animation = 'none';
                    setTimeout(() => {
                        this.style.animation = '';
                    }, 10);
                });
            }

            window.addEventListener('resize', function() {
                const newIsMobile = window.innerWidth <= 480;
                const newIsTablet = window.innerWidth <= 1024 && window.innerWidth > 480;

                if (newIsMobile !== isMobile || newIsTablet !== isTablet) {
                    location.reload();
                }
            });
        });
    </script>
</body>

</html>