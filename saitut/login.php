<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter- Quiz Maker</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/quiz.css"> </head>
<body class="auth-page quiz-page">

    <div class="container main-quiz-island quiz-card" id="login-container" style="max-width: 450px; margin: 100px auto; padding: 40px;">
        <h1 style="margin-bottom: 30px; color: #ffcc00; text-shadow: 0 0 15px rgba(255, 204, 0, 0.4);">Login</h1>
        
        <form action="php/login_user.php" method="POST">
            <div style="margin-bottom: 20px;">
                <input type="text" name="username" placeholder="username" required class="auth-input" 
                       style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white;">
            </div>
            
            <div style="margin-bottom: 30px;">
                <input type="password" name="password" placeholder="password" required class="auth-input" 
                       style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white;">
            </div>

            <button type="submit" class="auth-button confirm-btn" style="width: 100%; padding: 15px; cursor: pointer;">Login</button>
        </form>

        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <p class="auth-text" style="color: rgba(255,255,255,0.7);">
                You dont have an account? <a href="register.php" class="auth-link-main" style="color: #ffcc00; text-decoration: none; font-weight: bold;">Register</a>
            </p>
            <p style="margin-top: 15px;">
                <a href="forgot_password.php" class="auth-link-sub" style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">Forgot Password?</a>
            </p>
        </div>
    </div>

    <script src="js/effects.js"></script>
</body>
</html>