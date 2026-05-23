<?php
session_start();
include "../php/lang_config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/global_neon.css?v=<?php echo time(); ?>">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #1A1511;
            background-image: linear-gradient(180deg, #1A1511 0%, #FF7B00 100%);
            background-attachment: fixed;
            background-size: cover;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            color: #EAE4D9;
        }
        .privacy-container {
            width: 90%;
            max-width: 900px;
            margin: 50px auto;
            background: rgba(26, 21, 17, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 123, 0, 0.15);
            border: 1px solid rgba(255, 123, 0, 0.2);
        }
        .privacy-container h1 {
            color: #FF7B00;
            text-align: center;
            margin-bottom: 30px;
        }
        .privacy-container h2 {
            color: #FF9B44;
            margin-top: 30px;
            border-bottom: 1px solid rgba(255, 123, 0, 0.2);
            padding-bottom: 10px;
        }
        .privacy-container p, .privacy-container li {
            line-height: 1.6;
            font-size: 1.05rem;
            opacity: 0.9;
        }
        .privacy-container ul {
            padding-left: 20px;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: #FF7B00;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .back-btn:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="privacy-container">
        <a href="../index.php" class="back-btn">&larr; Back to Home</a>
        <h1>Privacy Policy</h1>
        
        <h2>1. Information We Collect</h2>
        <p>When you register and use our platform, we collect and store the following information:</p>
        <ul>
            <li>Your email address and an encrypted hash of your password.</li>
            <li>Profile information including your username, avatar, and background images.</li>
            <li>Messages sent in direct chats and public forum posts.</li>
            <li>Data related to quiz scores, experience points (XP), and achievements.</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>The information we collect is strictly used to provide, maintain, and improve our services. Specifically, we use your data to:</p>
        <ul>
            <li>Authenticate your account and ensure platform security.</li>
            <li>Enable communication with other users via chat and forums.</li>
            <li>Maintain leaderboards and quiz statistics.</li>
        </ul>

        <h2>3. Data Storage & Security</h2>
        <p>All data is securely stored on our servers. Passwords are cryptographically hashed and never stored in plain text. We employ modern security practices to protect your data from unauthorized access.</p>

        <h2>4. Your GDPR Rights</h2>
        <p>Under the General Data Protection Regulation (GDPR), you have complete control over your data. You have the right to:</p>
        <ul>
            <li><strong>Access:</strong> Download a copy of all your personal data directly from your profile settings.</li>
            <li><strong>Erasure:</strong> Request permanent deletion of your account and all associated data ("Right to be Forgotten") using the deletion tool in your profile.</li>
            <li><strong>Consent:</strong> Manage your cookie preferences at any time.</li>
        </ul>

        <h2>5. Cookies</h2>
        <p>We use essential cookies and local storage mechanisms to keep you logged in and to remember preferences such as accessibility settings (e.g., Dyslexia Mode). You can manage non-essential cookies via our Cookie Consent Banner.</p>
        
        <h2>6. Contact Us</h2>
        <p>If you have any questions regarding this Privacy Policy or your personal data, please contact the administrators through the platform or our support channels.</p>
    </div>
    
    <?php include "gdpr_banner.php"; ?>
</body>
</html>
