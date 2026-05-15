<?php
session_start();
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login - Freaky Quiz</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body class="auth-page quiz-page">

    <div class="container main-quiz-island quiz-card" id="login-container" style="max-width: 450px; margin: 80px auto; padding: 40px;">
        <h1 style="margin-bottom: 30px; color: #ffcc00; text-shadow: 0 0 15px rgba(255, 204, 0, 0.4);">Verification</h1>
        
        <p style="color: rgba(255,255,255,0.8); margin-bottom: 25px;">
        You're logging in from a new device. We've sent a 6-digit code to your email. Please enter it below
        </p>

        <form action="php/verify_code.php" method="POST">
            <div style="margin-bottom: 25px;">
                <input type="text" name="code" placeholder="6-digit code" required class="auth-input" pattern="[0-9]{6}" title="Please enter 6 digits"
                       style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white; text-align: center; font-size: 24px; letter-spacing: 5px;">
            </div>

            <button type="submit" class="auth-button confirm-btn" style="width: 100%; padding: 15px; cursor: pointer;">Потвърди</button>
        </form>

        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <p class="auth-text" style="color: rgba(255,255,255,0.7);">
                <a href="login.php" class="auth-link-main" style="color: #ffcc00; text-decoration: none; font-weight: bold;">Обратно към логин</a>
            </p>
        </div>
    </div>

    <script src="js/effects.js"></script>
</body>
</html>
