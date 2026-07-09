<?php
session_start();
// Include database connection with graceful error handling
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Refer & Earn - Start Earning Today!</title>
  <link rel="stylesheet" href="refer_&_earn.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* CSS Custom Properties for Consistent Theming */
    :root {
      --primary-gradient: linear-gradient(135deg, #be8652 0%, #000000 100%);
      --secondary-gradient: linear-gradient(135deg, #be8652, #be8652);
      --accent-gradient: linear-gradient(135deg, #FACC15, #FACC15);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
      --shadow-light: 0 4px 24px rgba(0, 0, 0, 0.06);
      --shadow-medium: 0 8px 32px rgba(190, 134, 82, 0.15);
      --shadow-strong: 0 20px 60px rgba(190, 134, 82, 0.25);
      --border-radius: 20px;
      --border-radius-large: 32px;
      --transition-fast: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      --transition-slow: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Reset and Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      /* font-family: 'Poppins', sans-serif; */
      line-height: 1.6;
      color: #be8652;
      overflow-x: hidden;
      /* background: #000000;
    background: -moz-linear-gradient(left, #000000 0, #be8652 100%);
    background: -webkit-linear-gradient(left, #000000 0, #be8652 100%);
    background: linear-gradient(to right, #000000 0, #be8652 100%); */
      font-size: 16px;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .container {
      max-width: 90% !important;
      /* max-width: 1200px; */
      margin: 0 auto;
      padding: 0 24px;
    }

    /* Professional Button Styles */
    .btn {
      padding: 14px 28px;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      position: relative;
      overflow: hidden;
      min-height: 48px;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn-primary {
      background: linear-gradient(135deg, #be8652 0%, #000000 100%);
      /* background: linear-gradient(135deg, #be8652 0%, #be8652 100%); */
      color: white;
      box-shadow: 0 4px 15px rgba(235, 190, 129, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(235, 190, 129, 0.4);
    }

    .btn-outline {
      background: transparent;
      border: 2px solid #e1e8ed;
      color: #be8652;
      box-shadow: 0 2px 8px rgba(235, 190, 129, 0.1);
    }

    .btn-outline:hover {
      background: #be8652;
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 4px 20px rgba(235, 190, 129, 0.3);
    }

    .btn-outline-secondary {
      background: transparent;
      border: 2px solid #be8652;
      color: #be8652;
    }

    .btn-outline-secondary:hover {
      background: linear-gradient(135deg, #be8652 0%, #000000 100%);
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 4px 20px rgba(235, 190, 129, 0.3);
    }

    .btn-large {
      padding: 18px 36px;
      font-size: 16px;
      font-weight: 600;
      min-height: 56px;
    }

    /* Header */
    .header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      border-bottom: 1px solid rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .header .container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 20px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 24px;
      font-weight: 700;
      color: #be8652;
    }

    .logo-icon {
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, #be8652, #be8652);
      border-radius: 8px;
      position: relative;
    }

    .logo-icon::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 16px;
      height: 16px;
      background: white;
      border-radius: 4px;
    }

    .nav-menu {
      display: flex;
      gap: 32px;
    }

    .nav-link {
      text-decoration: none;
      color: #666;
      font-weight: 500;
      transition: color 0.3s ease;
      position: relative;
    }

    .nav-link:hover,
    .nav-link.active {
      color: #be8652;
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, #be8652, #be8652);
      border-radius: 1px;
    }

    .nav-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .mobile-menu-toggle {
      display: none;
      flex-direction: column;
      background: none;
      border: none;
      cursor: pointer;
      padding: 5px;
    }

    .mobile-menu-toggle span {
      width: 25px;
      height: 3px;
      background: #333;
      margin: 3px 0;
      transition: 0.3s;
      border-radius: 2px;
    }

    /* Professional Hero Section */
    .hero {
      background: linear-gradient(to right, #000000 0, #be8652 100%);
      padding: 146px 0 40px;
      /* padding: 40px 0 40px; */
      position: relative;
      overflow: hidden;
      min-height: 80vh;
      display: flex;
      align-items: center;
    }

    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 20%, rgba(245, 158, 11, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(202, 138, 4, 0.05) 0%, transparent 50%);
      pointer-events: none;
    }

    .hero-bg-elements {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      pointer-events: none;
      z-index: 1;
    }

    .floating-element {
      position: absolute;
      border-radius: 50%;
      opacity: 0.06;
      animation: professionalFloat 20s ease-in-out infinite;
    }

    .element-1 {
      width: 120px;
      height: 120px;
      background: linear-gradient(135deg, #be8652, #be8652);
      top: 10%;
      left: 5%;
      animation-delay: 0s;
    }

    .element-2 {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #be8652, #be8652);
      top: 60%;
      right: 10%;
      animation-delay: -7s;
    }

    .element-3 {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #be8652, #be8652);
      bottom: 15%;
      left: 15%;
      animation-delay: -14s;
    }

    .dots-pattern {
      position: absolute;
      width: 200px;
      height: 200px;
      background-image: radial-gradient(circle, rgba(102, 126, 234, 0.1) 1px, transparent 1px);
      background-size: 30px 30px;
      opacity: 0.4;
      animation: professionalFloat 25s ease-in-out infinite reverse;
    }

    .dots-1 {
      top: 20%;
      right: 5%;
      animation-delay: -5s;
    }

    .dots-2 {
      bottom: 20%;
      left: 0%;
      animation-delay: -12s;
    }

    @keyframes professionalFloat {

      0%,
      100% {
        transform: translateY(0px) translateX(0px);
      }

      33% {
        transform: translateY(-15px) translateX(10px);
      }

      66% {
        transform: translateY(5px) translateX(-5px);
      }
    }

    .hero-content {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
      position: relative;
      z-index: 2;
    }

    .hero-title {
      font-size: 3.2rem;
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 28px;
      color: #ffffff;
      letter-spacing: -0.02em;

    }

    .highlight {
      background: linear-gradient(135deg, #be8652 0%, #be8652 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
    }

    .highlight::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #be8652, #be8652);
      border-radius: 2px;
      opacity: 0.3;
    }

    .hero-subtitle {
      font-size: 1.25rem;
      color: #ffffff;
      margin-bottom: 40px;
      line-height: 1.6;
      font-weight: 400;

    }

    .hero-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;

    }

    .hero-visual {
      position: relative;
      z-index: 2;
    }

    .hero-illustration {
      position: relative;
      width: 100%;
      max-width: 610px;
      height: auto;
      margin: 0 auto;
      /* background: linear-gradient(90deg, rgba(235, 190, 129, 0.08), rgba(190, 134, 82, 0.08)); */
      background-color: rgba(237, 193, 120, 0.364);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 20px 60px rgba(235, 190, 129, 0.15);
      border: 1px solid rgba(235, 190, 129, 0.1);
      backdrop-filter: blur(10px);
      animation: floatHero 5s ease-in-out infinite;
    }

    img.hero-illustration {
      width: 100%;
      height: auto;
      display: block;
      border-radius: 24px;
      object-fit: cover;
    }

    .refer-img {
      position: relative;
      width: 100%;
      max-width: 610px;
      height: 380px;
      margin: 0 auto;
      /* background: linear-gradient(90deg, rgba(235, 190, 129, 0.08), rgba(190, 134, 82, 0.08)); */

      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 20px 60px rgba(235, 190, 129, 0.15);

      backdrop-filter: blur(10px);
    }

    .person-container {
      position: relative;
      width: 240px;
      height: 240px;
    }

    .person {
      width: 140px;
      height: 140px;
      background: linear-gradient(135deg, #be8652, #be8652);
      border-radius: 50%;
      position: absolute;
      top: 30px;
      left: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 32px rgba(235, 190, 129, 0.3);
      animation: gentlePulse 4s ease-in-out infinite;
      overflow: hidden;
    }

    .person::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="35" r="15" fill="white" opacity="0.9"/><path d="M25 85 Q25 65 50 65 Q75 65 75 85" fill="white" opacity="0.9"/></svg>') center/cover;
      border-radius: 50%;
    }



    .device {
      width: 90px;
      height: 55px;
      background: linear-gradient(135deg, #ffffff, #f8fafc);
      border-radius: 12px;
      position: absolute;
      bottom: 25px;
      right: 15px;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
      border: 2px solid #e2e8f0;
    }

    .device::before {
      content: '💻';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 28px;
    }

    .sharing-icons {
      position: absolute;
      top: -15px;
      right: -15px;
    }

    .share-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      animation: professionalPulse 3s ease-in-out infinite;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }

    .share-1 {
      background: linear-gradient(135deg, #2563EB, #2563EB);
      top: 0;
      right: 0;
      animation-delay: 0s;
    }

    .share-1::before {
      content: '📤';
    }

    .share-2 {
      background: linear-gradient(135deg, #2563EB, #2563EB);
      top: 25px;
      right: 25px;
      animation-delay: 0.7s;
    }

    .share-2::before {
      content: '🔗';
    }

    .share-3 {
      background: linear-gradient(135deg, #2563EB, #2563EB);
      top: 50px;
      right: 0;
      animation-delay: 1.4s;
    }

    .share-3::before {
      content: '💰';
    }

    .coin {
      position: absolute;
      width: 32px;
      height: 32px;
      background: linear-gradient(135deg, #FACC15, #FACC15);
      border-radius: 50%;
      animation: professionalCoinFloat 4s ease-in-out infinite;
      box-shadow: 0 4px 16px rgba(250, 204, 21, 0.3);
    }

    .coin::before {
      content: '$';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-weight: bold;
      font-size: 16px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .coin-1 {
      top: 60px;
      left: -25px;
      animation-delay: 0s;
    }

    .coin-2 {
      top: 120px;
      right: -25px;
      animation-delay: 1.3s;
    }

    .coin-3 {
      bottom: 90px;
      left: 60px;
      animation-delay: 2.6s;
    }

    @keyframes gentlePulse {

      0%,
      100% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.05);
      }
    }

    @keyframes professionalPulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
      }

      50% {
        transform: scale(1.1);
        opacity: 0.9;
      }
    }

    @keyframes professionalCoinFloat {

      0%,
      100% {
        transform: translateY(0px) rotate(0deg);
      }

      33% {
        transform: translateY(-12px) rotate(5deg);
      }

      66% {
        transform: translateY(-6px) rotate(-3deg);
      }
    }

    @keyframes floatHero {

      0%,
      100% {
        transform: translateY(0px) translateX(0px) rotate(0deg);
      }

      20% {
        transform: translateY(-3px) translateX(1px) rotate(0.3deg);
      }

      40% {
        transform: translateY(-6px) translateX(2px) rotate(0.6deg);
      }

      60% {
        transform: translateY(-6px) translateX(-2px) rotate(-0.6deg);
      }

      80% {
        transform: translateY(-3px) translateX(-1px) rotate(-0.3deg);
      }
    }

    /* Professional How It Works Section */
    .how-it-works {
      padding: 40px 0;
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      position: relative;
    }

    .how-it-works::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 50%, rgba(102, 126, 234, 0.02) 0%, transparent 70%);
      pointer-events: none;
    }

    .section-header {
      text-align: center;
      margin-bottom: 40px;
      position: relative;
      z-index: 2;
    }

    .section-title {
      font-size: 2.75rem;
      font-weight: 700;
      color: #000000ee;
      margin-bottom: 20px;
      letter-spacing: -0.02em;
    }

    .section-title i {
      margin-right: 12px;
      color: #be8652;
      font-size: 2.5rem;
      animation: gentlePulse 3s ease-in-out infinite;
    }

    .section-subtitle {
      font-size: 1.25rem;
      color: #9a9b9c;
      font-weight: 400;
      max-width: 600px;
      margin: 0 auto;
    }

    .section-subtitle i {
      margin-right: 8px;
      color: #be8652;
      font-size: 1.2rem;
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 32px;
      position: relative;
      z-index: 2;
    }

    .step-card {
      text-align: center;
      padding: 48px 32px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(102, 126, 234, 0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .step-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #be8652, #be8652);
      transform: scaleX(0);
      transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 2px 2px 0 0;
    }

    .step-card::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: radial-gradient(circle, rgba(235, 190, 129, 0.1) 0%, transparent 70%);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      transform: translate(-50%, -50%);
      border-radius: 50%;
      z-index: 0;
    }

    .step-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 60px rgba(190, 134, 82, 0.15);
      border-color: rgba(190, 134, 82, 0.2);
    }

    .step-card:hover::before {
      transform: scaleX(1);
    }

    .step-card:hover::after {
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(190, 134, 82, 0.1) 0%, transparent 70%);
    }

    .step-icon {
      width: 88px;
      height: 88px;
      border-radius: 50%;
      margin: 0 auto 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      font-weight: 700;
      color: white;
      position: relative;
      z-index: 2;
      box-shadow: 0 8px 32px rgba(235, 190, 129, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }

    .step-icon::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .step-card:hover .step-icon::before {
      transform: translate(-50%, -50%) scale(1.3);
      opacity: 0.2;
    }

    .step-card:hover .step-icon {
      transform: scale(1.1);
      box-shadow: 0 12px 40px rgba(190, 134, 82, 0.4);
    }

    .step-1 {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .step-2 {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .step-3 {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .step-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 18px;
      color: #1e293b;
      position: relative;
      z-index: 2;
    }

    .step-description {
      color: #64748b;
      line-height: 1.7;
      font-size: 1.05rem;
      position: relative;
      z-index: 2;
    }

    /* Professional Benefits Section */
    .benefits {
      padding: 20px 0;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
      position: relative;
    }

    .benefits::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 20% 80%, rgba(102, 126, 234, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.03) 0%, transparent 50%);
      pointer-events: none;
    }

    .benefits-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
      position: relative;
      z-index: 2;
    }

    .benefit-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      padding: 40px 32px;
      border-radius: 20px;
      border: 1px solid rgba(235, 190, 129, 0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    }

    .benefit-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(190, 134, 82, 0.02), rgba(190, 134, 82, 0.02));
      opacity: 0;
      transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .benefit-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 60px rgba(190, 134, 82, 0.15);
      border-color: rgba(190, 134, 82, 0.2);
    }

    .benefit-card:hover::before {
      opacity: 1;
    }

    .benefit-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      margin-bottom: 28px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 32px rgba(235, 190, 129, 0.2);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }

    .benefit-icon::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 32px;
      height: 32px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .benefit-card:hover .benefit-icon::before {
      transform: translate(-50%, -50%) scale(1.5);
      opacity: 0.1;
    }

    .benefit-card:hover .benefit-icon {
      transform: scale(1.1);
      box-shadow: 0 12px 40px rgba(190, 134, 82, 0.3);
    }

    .benefit-passive {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .benefit-commission {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .benefit-recurring {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .benefit-payout {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .benefit-support {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .benefit-global {
      background: linear-gradient(135deg, #be8652, #be8652);
    }

    .benefit-icon i {
      font-size: 28px;
      color: white;
      filter: brightness(1.1);
      position: relative;
      z-index: 2;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .benefit-card:hover .benefit-icon i {
      transform: scale(1.1);
    }

    .benefit-title {
      font-size: 1.35rem;
      font-weight: 600;
      margin-bottom: 16px;
      color: #1e293b;
      letter-spacing: -0.01em;
    }

    .benefit-description {
      color: #64748b;
      line-height: 1.7;
      font-size: 1.05rem;
    }



    /* Professional CTA Section */
    .cta-section {
      padding: 40px 0;
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
      color: white;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .cta-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 20%, rgba(235, 190, 129, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 70% 80%, rgba(190, 134, 82, 0.1) 0%, transparent 50%);
      pointer-events: none;
    }

    .cta-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      margin: 0 auto;
    }

    .cta-title {
      font-size: 2.75rem;
      font-weight: 700;
      margin-bottom: 24px;
      letter-spacing: -0.02em;
      line-height: 1.2;
      margin-top: 15px;
    }

    .cta-subtitle {
      font-size: 1.25rem;
      opacity: 0.9;
      margin-bottom: 48px;
      line-height: 1.6;
      font-weight: 400;
    }

    .cta-note {
      margin-top: 24px;
      opacity: 0.8;
      font-size: 1rem;
      font-weight: 500;
    }

    .cta-title i {
      margin-right: 12px;
      color: #be8652;
      font-size: 2.5rem;
      animation: gentlePulse 3s ease-in-out infinite;
    }

    .cta-note i {
      margin-right: 8px;
      color: #be8652;
      font-size: 1.1rem;
    }

    .cta-content .btn i {
      margin-left: 8px;
      transition: transform 0.3s ease;
    }

    .cta-content .btn:hover i {
      transform: translateX(4px);
    }

    .cta-stats {
      display: flex;
      justify-content: center;
      gap: 24px;
      margin: 24px 0;
      /* height: 60px; */
    }

    .stat-item {
      text-align: center;
      padding: 12px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      min-width: 80px;
      /* min-width: 80px; */
    }

    .stat-item:hover {
      transform: translateY(-4px);
      background: rgba(190, 134, 82, 0.15);


    }

    .stat-number {
      display: block;
      font-size: 1rem;
      font-weight: 700;
      color: #be8652;
      margin-bottom: 4px;
    }

    .stat-label {
      font-size: 0.75rem;
      color: #1e293b;
      font-weight: 500;
      margin-top: 8px;
    }

    .stat-item i {
      font-size: 1rem;
      color: #be8652;
      margin-bottom: 8px;
      display: block;
    }


    .cta-testimonial {
      text-align: center;
      margin: 40px 0;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 20px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }

    .cta-testimonial:hover {
      background: rgba(190, 134, 82, 0.15);
      transform: translateY(-4px);
    }

    .testimonial-quote {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .testimonial-quote i {
      font-size: 1.5rem;
      color: #be8652;
    }

    .testimonial-quote p {
      font-style: italic;
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
      margin: 0;
    }

    .testimonial-author {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
    }

    .testimonial-author i {
      font-size: 1.2rem;
      color: #be8652;
    }

    .cta-section {
      position: relative;
      overflow: hidden;
    }

    .cta-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
      background-size: 50px 50px;
      animation: professionalFloat 20s ease-in-out infinite;
      pointer-events: none;
    }

    .cta-title {
      animation: fadeInUp 1s ease-out;
    }

    .cta-subtitle {
      animation: fadeInUp 1s ease-out 0.2s both;
    }

    .cta-stats {
      animation: fadeInUp 1s ease-out 0.4s both;
    }

    .cta-content .btn {
      animation: fadeInUp 1s ease-out 0.6s both;
    }

    .cta-note {
      animation: fadeInUp 1s ease-out 0.8s both;
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

    /* Professional Preview Section */
    .preview-section {
      padding: 40px 0;
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      position: relative;
      overflow-x: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .preview-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 50%, rgba(235, 190, 129, 0.02) 0%, transparent 70%);
      pointer-events: none;
    }

    .preview-content {
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .preview-content .decorative-elements {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      pointer-events: none;
      z-index: 1;
    }

    .preview-content .decorative-elements .coin {
      position: absolute;
      width: 24px;
      height: 24px;
      background: linear-gradient(135deg, #FACC15, #FACC15);
      border-radius: 50%;
      animation: professionalCoinFloat 4s ease-in-out infinite;
      box-shadow: 0 4px 16px rgba(250, 204, 21, 0.3);
    }

    .preview-content .decorative-elements .coin::before {
      content: '$';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-weight: bold;
      font-size: 12px;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .preview-content .decorative-elements .coin-1 {
      top: 20%;
      left: 10%;
      animation-delay: 0s;
    }

    .preview-content .decorative-elements .coin-2 {
      top: 70%;
      right: 15%;
      animation-delay: 1.3s;
    }

    .preview-content .decorative-elements .coin-3 {
      bottom: 30%;
      left: 20%;
      animation-delay: 2.6s;
    }

    .dashboard-preview {
      max-width: 900px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 24px;
      border: 1px solid rgba(235, 190, 129, 0.1);
      box-shadow: 0 25px 80px rgba(235, 190, 129, 0.15);
      overflow: hidden;
      position: relative;
      z-index: 2;
      width: 100%;
      display: flex;
      flex-direction: column;
    }

    .dashboard-header {
      background: linear-gradient(135deg, #be8652 0%, #be8652 100%);
      color: white;
      padding: 32px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
    }

    .dashboard-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></svg>');
      background-size: 30px 30px;
    }

    .dashboard-title {
      font-size: 1.6rem;
      font-weight: 600;
      position: relative;
      z-index: 2;
      letter-spacing: -0.01em;
    }

    .dashboard-status {
      background: rgba(255, 255, 255, 0.2);
      padding: 8px 16px;
      border-radius: 24px;
      font-size: 0.85rem;
      backdrop-filter: blur(10px);
      position: relative;
      z-index: 2;
      font-weight: 500;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .dashboard-stats {
      padding: 48px 40px;
      display: flex;
      flex-direction: row;
      gap: 24px;
      justify-content: center;
    }

    .stat-item {
      display: flex;
      align-items: center;
      gap: 20px;
      padding: 28px 24px;
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      border-radius: 16px;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(102, 126, 234, 0.05);
      position: relative;
      overflow: hidden;
      min-height: 80px;
      flex-wrap: wrap;
      justify-content: flex-start;
    }

    .stat-item i {
      font-size: 2rem;
      color: #be8652;
      margin-bottom: 8px;
      display: block;
      text-align: center;
    }

    .stat-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(190, 134, 82, 0.02), rgba(190, 134, 82, 0.02));
      opacity: 0;
      transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(190, 134, 82, 0.15);
      border-color: rgba(190, 134, 82, 0.1);
    }

    .stat-item:hover::before {
      opacity: 1;
    }

    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      position: relative;
      z-index: 2;
      box-shadow: 0 4px 20px rgba(235, 190, 129, 0.2);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }

    .stat-icon::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 24px;
      height: 24px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-item:hover .stat-icon::before {
      transform: translate(-50%, -50%) scale(1.4);
      opacity: 0.1;
    }

    .stat-item:hover .stat-icon {
      transform: scale(1.1);
      box-shadow: 0 8px 30px rgba(190, 134, 82, 0.3);
    }

    .stat-invites {
      background: linear-gradient(135deg, #fef7e6, #fde68a);
    }

    .stat-invites::before {
      content: '👥';
    }

    .stat-referrals {
      background: linear-gradient(135deg, #fef7e6, #fde68a);
    }

    .stat-referrals::before {
      content: '✅';
    }

    .stat-earnings {
      background: linear-gradient(135deg, #fef3c7, #fde68a);
    }

    .stat-earnings::before {
      content: '💰';
    }

    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      position: relative;
      z-index: 2;
      letter-spacing: -0.02em;
    }

    .stat-label {
      font-size: 0.95rem;
      color: #64748b;
      position: relative;
      z-index: 2;
      font-weight: 500;
    }

    .referral-link-box {
      padding: 40px;
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      border-top: 1px solid rgba(235, 190, 129, 0.1);
      position: relative;
      text-align: center;
    }

    .link-label {
      display: block;
      font-weight: 600;
      margin-bottom: 16px;
      color: #1e293b;
      font-size: 1.1rem;
    }

    .link-label i {
      margin-right: 8px;
      color: #be8652;
      font-size: 1.1rem;
    }

    .link-input {
      display: flex;
      gap: 16px;
      align-items: stretch;
    }

    .link-input input {
      flex: 1;
      padding: 16px 20px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 15px;
      background: white;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-family: 'Inter', sans-serif;
    }

    .link-input input:focus {
      outline: none;
      border-color: #be8652;
      box-shadow: 0 0 0 3px rgba(235, 190, 129, 0.1);
    }

    .copy-btn {
      padding: 16px 24px;
      background: linear-gradient(135deg, #be8652, #be8652);
      color: white;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 15px;
      box-shadow: 0 4px 20px rgba(190, 134, 82, 0.3);
    }

    .copy-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(190, 134, 82, 0.4);
    }

    /* Professional FAQ Section */
    /* .faq-section {
    padding: 40px 0;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
    position: relative;
}

.faq-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 80%, rgba(235, 190, 129, 0.02) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(190, 134, 82, 0.02) 0%, transparent 50%);
    pointer-events: none;
}

.faq-container {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.faq-item {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    margin-bottom: 20px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(102, 126, 234, 0.05);
}

.faq-item:hover {
    box-shadow: 0 12px 40px rgba(190, 134, 82, 0.12);
    transform: translateY(-2px);
    border-color: rgba(190, 134, 82, 0.1);
}

.faq-question {
    padding: 32px 40px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    color: #1e293b;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.faq-question:hover {
    color: #be8652;
}

.faq-question::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #be8652, #be8652);
    transform: scaleX(0);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 0 0 2px 2px;
}

.faq-item:hover .faq-question::before {
    transform: scaleX(1);
}

.faq-icon {
    font-size: 1.5rem;
    color: #be8652;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: rgba(190, 134, 82, 0.1);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.faq-item.active .faq-icon {
    transform: rotate(45deg);
    background: rgba(190, 134, 82, 0.2);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.faq-item.active .faq-answer {
    max-height: 300px;
}

.faq-answer p {
    padding: 0 40px 32px;
    color: #64748b;
    line-height: 1.7;
    font-size: 1.05rem;
} */



    /* Professional Footer */
    .footer {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
      color: #cbd5e1;
      padding: 40px 0 40px;
      position: relative;
      overflow: hidden;
    }

    .footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 30% 20%, rgba(102, 126, 234, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 70% 80%, rgba(118, 75, 162, 0.05) 0%, transparent 50%);
      pointer-events: none;
    }

    .footer-content {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 80px;
      margin-bottom: 56px;
      position: relative;
      z-index: 2;
    }

    .footer-brand .logo {
      color: #2563EB;
      margin-bottom: 20px;
      font-size: 28px;
    }

    .footer-description {
      max-width: 320px;
      line-height: 1.7;
      opacity: 0.9;
      font-size: 1.05rem;
      color: #94a3b8;
    }

    .footer-links {
      display: flex;
      gap: 80px;
    }

    .footer-column {
      min-width: 160px;
    }

    .footer-title {
      font-weight: 600;
      color: white;
      margin-bottom: 24px;
      font-size: 1.1rem;
      letter-spacing: -0.01em;
    }

    .footer-link {
      display: block;
      color: #cbd5e1;
      text-decoration: none;
      margin-bottom: 14px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-size: 0.95rem;
    }

    .footer-link:hover {
      color: #be8652;
      transform: translateX(4px);
    }

    .social-links {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .social-link {
      color: #cbd5e1;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      padding: 12px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
    }

    .social-link:hover {
      color: #be8652;
      background: rgba(235, 190, 129, 0.1);
      border-color: rgba(235, 190, 129, 0.3);
      transform: translateY(-2px);
    }

    .footer-bottom {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 32px;
      text-align: center;
      opacity: 0.8;
      position: relative;
      z-index: 2;
    }

    .footer-bottom p {
      font-size: 0.95rem;
      color: #94a3b8;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
      .hero-content {
        grid-template-columns: 1fr;
        gap: 60px;
        text-align: center;
      }

      .hero-visual {
        text-align: center;
      }

      .hero-illustration {
        /* max-width: 400px; */
        height: auto;
      }

      .hero-title {
        font-size: 3rem;
      }

      .preview-section {
        padding: 32px 20px;
      }

      .dashboard-preview {
        max-width: 800px;
        border-radius: 20px;
      }

      .dashboard-header {
        padding: 28px 32px;
      }

      .dashboard-title {
        font-size: 1.5rem;
      }

      .dashboard-stats {
        padding: 40px 32px;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
      }

      .stat-item {
        padding: 24px 20px;
        gap: 16px;
      }

      .achievement-badge {
        right: 100px;
      }



      .review-item {
        padding: 28px;
        gap: 16px;
      }

      .review-stars {
        font-size: 1.2rem;
      }

      .review-text {
        font-size: 1rem;
      }

      .review-author {
        font-size: 0.95rem;
      }

      .cta-stats {
        gap: 30px;
      }

      .cta-title {
        font-size: 2.5rem;
      }

      .cta-subtitle {
        font-size: 1.2rem;
      }
    }

    @media (max-width: 1024px) and (min-width: 769px) {
      .hero-actions {
        gap: 14px;
        justify-content: center;
      }

      .btn-large {
        padding: 16px 32px;
        font-size: 15px;
      }
    }

    @media (max-width: 768px) {
      .nav-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      }

      .nav-menu.active {
        display: flex;
      }

      .mobile-menu-toggle {
        display: flex;
      }

      .nav-actions {
        display: none;
      }

      .hero {
        padding: 100px 0 40px;
      }

      .hero-title {
        font-size: 2rem;
      }

      .hero-subtitle {
        font-size: 1.1rem;
      }

      .hero-actions {
        justify-content: center;
        gap: 12px;
      }

      .btn-large {
        padding: 14px 28px;
        font-size: 14px;
        min-height: 48px;
      }

      .hero-visual {
        text-align: center;
      }

      .hero-illustration {
        max-width: 300px;
        height: auto;
        margin: 0 auto;
      }

      .steps-grid {
        grid-template-columns: 1fr;
        gap: 24px;
      }

      .benefits-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .benefit-card {
        padding: 24px 16px;
      }

      .benefit-icon {
        width: 60px;
        height: 60px;
      }

      .benefit-icon i {
        font-size: 24px;
      }

      .benefit-title {
        font-size: 1.25rem;
      }

      .benefit-description {
        font-size: 1rem;
      }

      .section-title {
        font-size: 1.75rem;
      }

      .section-subtitle {
        font-size: 1.1rem;
      }

      .cta-title {
        font-size: 1.75rem;
      }

      .cta-subtitle {
        font-size: 1.1rem;
      }

      .cta-stats {
        gap: 16px;
        /* flex-wrap: wrap; */
        justify-content: center;
      }

      .stat-item {
        padding: 16px;
        min-width: 100px;
      }

      .stat-number {
        font-size: 1.25rem;
      }

      .cta-testimonial {
        padding: 16px;
        margin: 24px 0;
      }

      .testimonial-quote p {
        font-size: 0.95rem;
      }

      .preview-section {
        padding: 24px 16px;
      }

      .dashboard-preview {
        max-width: 700px;
        border-radius: 16px;
      }

      .dashboard-header {
        padding: 20px 24px;
        flex-direction: column;
        gap: 12px;
        text-align: center;
      }

      .dashboard-title {
        font-size: 1.3rem;
      }

      .dashboard-status {
        font-size: 0.75rem;
        padding: 5px 10px;
      }

      .dashboard-stats {
        padding: 24px 20px;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
      }

      .stat-item {
        padding: 16px 12px;
        gap: 12px;
        flex-direction: column;
        text-align: center;
        min-height: 80px;
      }

      .stat-item i {
        margin-bottom: 6px;
        font-size: 1.5rem;
      }

      .stat-number {
        font-size: 1.5rem;
      }

      .stat-label {
        font-size: 0.8rem;
      }

      .referral-link-box {
        padding: 24px 20px;
      }

      .link-input {
        gap: 10px;
      }

      .link-input input {
        padding: 12px 16px;
        font-size: 14px;
      }

      .copy-btn {
        padding: 12px 20px;
        font-size: 14px;
      }

      .trust-logos {
        gap: 16px;
      }

      .trust-logo {
        padding: 12px 16px;
        font-size: 0.8rem;
        min-height: 44px;
      }

      .testimonial-card {
        padding: 24px 20px;
        margin: 0 auto;
        border-radius: 20px;
        max-width: calc(100% - 24px);
      }

      .testimonial-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
      }

      .user-profile {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        margin: 0 auto;
      }

      .user-info {
        text-align: center;
      }

      .user-name {
        font-size: 1.2rem;
      }

      .user-stats {
        justify-content: center;
      }

      .testimonial-text {
        font-size: 1rem;
        text-align: center;
      }

      .testimonial-text::before,
      .testimonial-text::after {
        display: none;
      }

      .testimonial-footer {
        flex-direction: column;
        gap: 10px;
        text-align: center;
      }

      .testimonial-rating {
        padding: 6px 12px;
      }

      .testimonial-decoration .floating-element {
        display: none;
      }

      .achievement-badge {
        top: 16px;
        right: 16px;
        font-size: 0.6rem;
        padding: 4px 8px;
      }

      .user-stats {
        gap: 10px;
      }

      .stat {
        padding: 2px 6px;
        font-size: 0.7rem;
      }

      .trust-icon {
        width: 24px;
        height: 24px;
        font-size: 14px;
      }

      .trust-icon::before {
        width: 12px;
        height: 12px;
      }

      .footer-content {
        grid-template-columns: 1fr;
        gap: 32px;
        text-align: center;
      }

      .footer-links {
        justify-content: center;
        gap: 32px;
      }

      .link-input {
        flex-direction: column;
      }

      .reviews-section {
        display: flex;
        overflow-x: auto;
        gap: 20px;
        max-width: 100%;
        padding: 0 16px;
        scrollbar-width: none;
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
      }

      .reviews-section.dragging {
        cursor: grabbing;
      }

      .reviews-section::-webkit-scrollbar {
        display: none;
      }

      .review-item {
        flex-direction: column;
        text-align: center;
        gap: 12px;
        width: 240px;
        flex-shrink: 0;
        scroll-snap-align: start;
      }

      .review-profile {
        align-self: center;
      }

      .review-avatar {
        width: 40px;
        height: 40px;
      }

      .review-avatar::before {
        width: 24px;
        height: 24px;
      }

      .review-stars {
        font-size: 1rem;
      }

      .review-text {
        font-size: 0.9rem;
      }

      .review-author {
        font-size: 0.85rem;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 0 16px;
      }

      .hero-illustration {
        max-width: 320px;
        height: auto;
      }

      .cta-section {
        padding: 32px 0;
      }

      .cta-title {
        font-size: 1.75rem;
        line-height: 1.3;
      }

      .cta-subtitle {
        font-size: 1.1rem;
        margin-bottom: 32px;
      }

      .cta-stats {
        /* display: grid; */
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        justify-items: center;
      }

      .stat-item:nth-child(3) {
        grid-column: 1 / -1;
        justify-self: center;
      }

      .stat-item {
        min-width: auto;
        padding: 16px 20px;
        width: 100%;
        max-width: 200px;
      }

      .stat-number {
        font-size: 1.25rem;
      }

      .cta-testimonial {
        padding: 16px;
        margin: 24px 0;
      }

      .testimonial-quote p {
        font-size: 1rem;
        line-height: 1.5;
      }

      .testimonial-author {
        font-size: 0.85rem;
      }

      .cta-note {
        font-size: 0.95rem;
        margin-top: 16px;
      }

      .preview-section {
        padding: 24px 12px;
      }

      .dashboard-preview {
        margin: 0 auto;
        border-radius: 16px;
        max-width: 100%;
        width: calc(100% - 24px);
      }

      .dashboard-header {
        padding: 20px 16px;
      }

      .dashboard-title {
        font-size: 1.2rem;
      }

      .dashboard-status {
        font-size: 0.75rem;
        padding: 4px 8px;
      }

      .dashboard-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        padding: 24px 16px;
        gap: 16px;
        justify-items: center;
      }

      .dashboard-stats .stat-item:nth-child(3) {
        grid-column: 1 / -1;
        justify-self: center;
      }

      .preview-content {
        justify-content: center;
        align-items: center;
      }

      .stat-item {
        padding: 20px 16px;
        gap: 12px;
        flex-direction: column;
        text-align: center;
        min-height: 90px;
      }

      .stat-item i {
        margin-bottom: 6px;
      }

      /* .stat-number {
        font-size: 1.6rem;
      } */

      .stat-label {
        font-size: 0.85rem;
      }

      .referral-link-box {
        padding: 20px 16px;
      }

      .link-label {
        font-size: 1rem;
        margin-bottom: 12px;
      }

      .link-input {
        flex-direction: column;
        gap: 12px;
      }

      .link-input input {
        padding: 12px 16px;
        font-size: 14px;
      }

      .copy-btn {
        padding: 12px 20px;
        font-size: 14px;
      }

      .hero-title {
        font-size: 2rem;
      }

      .btn-large {
        padding: 14px 24px;
        font-size: 15px;
      }

      .hero-actions {
        flex-direction: column;
        gap: 12px;
        justify-content: center;
      }

      .hero-actions .btn {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
      }

      .section-title {
        font-size: 1.75rem;
      }

      .step-card,
      .benefit-card {
        padding: 32px 20px;
      }

      .dashboard-preview {
        margin: 0 auto;
        border-radius: 12px;
        max-width: 100%;
        width: calc(100% - 32px);
      }

      .dashboard-stats {
        padding: 24px 16px;
        align-items: center;
      }

      .referral-link-box {
        padding: 24px 20px;
      }

      .faq-question {
        padding: 20px 24px;
      }

      .faq-answer p {
        padding: 0 24px 20px;
      }

      .trust-logos {
        gap: 12px;
        justify-content: center;
      }

      .trust-logo {
        font-size: 0.8rem;
        padding: 8px 16px;
        min-height: 48px;
      }

      .testimonial-card {
        padding: 24px 20px;
        margin: 0 12px;
      }

      .user-avatar {
        width: 60px;
        height: 60px;
      }

      .avatar-content {
        font-size: 18px;
      }

      .user-name {
        font-size: 1.1rem;
      }

      .testimonial-text {
        font-size: 1rem;
      }

      .testimonial-rating {
        flex-direction: column;
        gap: 8px;
      }

      .testimonial-card {
        padding: 20px 16px;
        margin: 0 auto;
        max-width: calc(100% - 24px);
      }

      .user-avatar {
        width: 50px;
        height: 50px;
      }

      .avatar-content {
        font-size: 16px;
      }

      .user-name {
        font-size: 1rem;
      }

      .user-title {
        font-size: 0.9rem;
        margin-bottom: 8px;
      }

      .user-stats {
        gap: 8px;
      }

      .stat {
        padding: 2px 6px;
        font-size: 0.7rem;
      }

      .testimonial-text {
        font-size: 0.95rem;
      }

      .testimonial-rating {
        padding: 6px 12px;
      }

      .stars i {
        font-size: 1rem;
      }

      .rating-text {
        font-size: 0.9rem;
      }

      .testimonial-footer {
        padding-top: 16px;
      }

      .testimonial-date,
      .testimonial-platform {
        font-size: 0.8rem;
      }

      .achievement-badge {
        display: none;
        top: 15px;
        right: 15px;
        font-size: 0.6rem;
        padding: 4px 8px;
      }

      .trust-icon {
        width: 24px;
        height: 24px;
        font-size: 14px;
      }

      .trust-icon::before {
        width: 14px;
        height: 14px;
      }

      .footer-links {
        flex-direction: column;
        gap: 30px;
      }

      /* Hide or adjust floating elements on very small screens to prevent overflow */
      .cta-section .hero-bg-elements .floating-element,
      .cta-section .hero-bg-elements .dots-pattern {
        display: none;
      }

      /* Extra small screens */
      @media (max-width: 360px) {
        .preview-section {
          padding: 20px 8px;
        }

        .dashboard-preview {
          margin: 0 auto;
          border-radius: 12px;
          width: calc(100% - 16px);
        }

        .dashboard-header {
          padding: 16px 12px;
        }

        .dashboard-title {
          font-size: 1.1rem;
        }

        .dashboard-status {
          font-size: 0.7rem;
          padding: 3px 6px;
        }

        .dashboard-stats {
          padding: 20px 12px;
          gap: 12px;
        }

        .stat-item {
          padding: 16px 12px;
          gap: 8px;
          min-height: 80px;
        }

        /* .stat-number {
          font-size: 1.4rem;
        } */

        .stat-label {
          font-size: 0.8rem;
        }

        /* Landscape mode adjustments */
        @media (max-height: 500px) and (orientation: landscape) {
          .preview-section {
            padding: 16px 8px;
          }

          .dashboard-stats {
            padding: 20px 12px;
          }

          .stat-item {
            padding: 14px 10px;
            min-height: 70px;
          }
        }

        .referral-link-box {
          padding: 16px 12px;
        }

        .link-label {
          font-size: 0.9rem;
          margin-bottom: 8px;
        }

        .link-input {
          flex-direction: column;
          gap: 8px;
        }

        .link-input input {
          padding: 10px 12px;
          font-size: 12px;
        }

        .copy-btn {
          padding: 10px 16px;
          font-size: 12px;
        }
      }
    }


    /* Testimonials */

    /* * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background-color: #ffffff;
  color: #333;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
} */

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


    /* FAQ Section */

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes shimmer {
      0% {
        background-position: -1000px 0;
      }

      100% {
        background-position: 1000px 0;
      }
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.8;
      }
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      /* background: linear-gradient(135deg, #e8e4f0 0%, #d4cfe0 100%); */
      /* min-height: 100vh; */
      /* display: flex; */
      align-items: center;
      justify-content: center;
      /* padding: 40px 20px; */
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .faq-container {
      width: 100%;
      max-width: 1580px;
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



    /* How it works? - Simple Steps to Success */

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
      max-width: 1200px;
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




    /* Why Choose Section */
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
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: 80px 20px;
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




    /* Courses Section */
    .courses {
      background: #f8f9fa;
    }

    .course-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .course-card {
      background: white;
      border-radius: 20px;
      padding: 1.2rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      position: relative;
      border: 2px solid transparent;
    }

    .course-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .course-card.popular {
      border-color: #be8652;
      transform: scale(1.05);
    }

    .popular-badge {
      position: absolute;
      top: -15px;
      left: 50%;
      transform: translateX(-50%);
      background: #be8652;
      color: white;
      padding: 8px 20px;
      border-radius: 25px;
      font-size: 0.9rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .card-header h3 {
      color: #333;
      margin-bottom: 1rem;
    }

    .price {
      font-size: 2.5rem;
      font-weight: 700;
      color: #be8652;
      margin-bottom: 1rem;
    }

    .rating {
      display: flex;
      justify-content: center;
      gap: 0.25rem;
      margin-bottom: 1rem;
    }

    .rating i {
      color: #ffc107;
    }

    .features {
      list-style: none;
      margin-bottom: 2rem;
    }

    .features li {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.5rem 0;
      color: #666;
    }

    .features i {
      color: #28a745;
      font-size: 0.9rem;
    }
  </style>
</head>

<body>
  <!-- header  -->
  <?php include('includes/header.php') ?>

  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-bg-elements">
      <div class="floating-element element-1"></div>
      <div class="floating-element element-2"></div>
      <div class="floating-element element-3"></div>
      <div class="dots-pattern dots-1"></div>
      <div class="dots-pattern dots-2"></div>
    </div>
    <div class="container">
      <div class="hero-content">
        <div class="hero-text">
          <h1 class="hero-title">
            Invite Friends,
            <span class="highlight">Earn Rewards!</span>
          </h1>
          <p class="hero-subtitle">
            Start earning passive income today by sharing your unique referral link.
            Join thousands of users already earning with our referral program.
          </p>
          <div class="hero-actions">
            <a href="pricings.php" class="btn btn-primary btn-large">Join Now – It's Free</a>
           
          </div>
        </div>
        <div class="hero-visual">
          <div class="hero-illustration1">
            <img src="referral.jpg" alt="" class="hero-illustration">

          </div>
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


  <!-- How it works? - Simple Steps to Success -->

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

  <!-- CTA Section -->
  <section class="cta-section">
    <div class="hero-bg-elements">
      <div class="floating-element element-1"></div>
      <div class="floating-element element-2"></div>
      <div class="floating-element element-3"></div>
      <div class="dots-pattern dots-1"></div>
      <div class="dots-pattern dots-2"></div>
    </div>
    <div class="container">
      <div class="cta-content">
        <h2 class="cta-title"><i class="fas fa-rocket"></i> Ready to Start Earning?</h2>
        <p class="cta-subtitle">Join thousands of users already earning with our referral program</p>
        <div class="cta-stats">
          <div class="stat-item">
            <i class="fas fa-users"></i>
            <span class="stat-number">10,000+</span>
            <!-- <span class="stat-label">Active Users</span> -->
          </div>
          <div class="stat-item">
            <i class="fas fa-dollar-sign"></i>
            <span class="stat-number">$500K+</span>
            <!-- <span class="stat-label">Paid Out</span> -->
          </div>
          <div class="stat-item">
            <i class="fas fa-star"></i>
            <span class="stat-number">4.9/5</span>
            <!-- <span class="stat-label">User Rating</span> -->
          </div>
        </div>
        <div class="cta-testimonial">
          <div class="testimonial-quote">
            <i class="fas fa-quote-left"></i>
            <p>"This program changed my life! I earned $2,000 in my first month."</p>
            <i class="fas fa-quote-right"></i>
          </div>
          <div class="testimonial-author">
            <i class="fas fa-user-circle"></i>
            <span>- Alex Johnson, Top Earner</span>
          </div>
        </div>
        <button class="btn btn-primary btn-large">Join Now – Start Earning <i class="fas fa-arrow-right"></i></button>
        <!-- <p class="cta-note"><i class="fas fa-check-circle"></i> It's free and easy to get started!</p> -->
      </div>
    </div>
  </section>

  <!-- Preview Section -->
  <section class="preview-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-eye"></i> See What Awaits You</h2>
        <p class="section-subtitle"><i class="fas fa-chart-line"></i> Preview your earnings dashboard</p>
      </div>
      <div class="preview-content">
        <div class="dashboard-preview">
          <div class="dashboard-header">
            <div class="dashboard-title">Your Referral Dashboard</div>
            <div class="dashboard-status">Live Preview</div>
          </div>
          <div class="dashboard-stats">
            <div class="stat-item">
              <i class="fas fa-users"></i>
              <div class="stat-content">
                <div class="stat-number">47</div>
                <div class="stat-label">Total Invites</div>
              </div>
            </div>
            <div class="stat-item">
              <i class="fas fa-check-circle"></i>
              <div class="stat-content">
                <div class="stat-number">23</div>
                <div class="stat-label">Successful Referrals</div>
              </div>
            </div>
            <div class="stat-item">
              <i class="fas fa-dollar-sign"></i>
              <div class="stat-content">
                <div class="stat-number">$385</div>
                <div class="stat-label">Total Earnings</div>
              </div>
            </div>
          </div>

        </div>
        <div class="decorative-elements">
          <div class="coin coin-1"></div>
          <div class="coin coin-2"></div>
          <div class="coin coin-3"></div>
        </div>
      </div>
    </div>
  </section>

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
    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navMenu');

    if (mobileMenuToggle && navMenu) {
      mobileMenuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileMenuToggle.classList.toggle('active');
      });
    }

    // Smooth Scrolling
    function scrollToSection(sectionId) {
      const section = document.getElementById(sectionId);
      if (section) {
        section.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    }

    // Header Scroll Effect
    window.addEventListener('scroll', () => {
      const header = document.querySelector('.header');
      if (window.scrollY > 100) {
        header.style.background = 'rgba(255, 255, 255, 0.98)';
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
      } else {
        header.style.background = 'rgba(255, 255, 255, 0.95)';
        header.style.boxShadow = 'none';
      }
    });




    // Testimonials

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


    // FAQ Section

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


    // How it works? - Simple Steps to Success

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
  </script>
</body>

</html>