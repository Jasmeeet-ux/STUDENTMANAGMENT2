<?php
// Maintenance mode page - shown when database is unavailable
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance - Culture of Internet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .maintenance-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }

        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            opacity: 0.8;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .status-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .status-info h3 {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .contact-info h3 {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .contact-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .contact-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .contact-links a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border: 2px solid white;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .back-link:hover {
            background: white;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .maintenance-container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .contact-links {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>

        <h1>Site Under Maintenance</h1>

        <p>We're currently performing some important updates to improve your experience. Our services will be back online shortly.</p>

        <div class="status-info">
            <h3><i class="fas fa-info-circle"></i> What does this mean?</h3>
            <p>Some features may be temporarily unavailable while we work on enhancements. Your data is safe and will be fully accessible once maintenance is complete.</p>
        </div>

        <div class="contact-info">
            <h3><i class="fas fa-envelope"></i> Need Help?</h3>
            <p>If you have urgent questions, feel free to reach out to our support team.</p>

            <div class="contact-links">
                <a href="mailto:contactcultureofinternet@gmail.com">
                    <i class="fas fa-envelope"></i> Email Support
                </a>
                <a href="tel:8130840080">
                    <i class="fas fa-phone"></i> Call Us
                </a>
            </div>
        </div>

        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Go Back
        </a>
    </div>
</body>
</html>
