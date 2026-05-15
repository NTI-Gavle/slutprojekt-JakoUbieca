<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quiz Maker</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/quiz.css"> </head>
<body class="auth-page quiz-page">

    <div class="container main-quiz-island quiz-card" id="register-container" style="max-width: 450px; margin: 80px auto; padding: 40px;">
        <h1 style="margin-bottom: 30px; color: #ffcc00; text-shadow: 0 0 15px rgba(255, 204, 0, 0.4);">Register</h1>
        
        <form action="php/register_user.php" method="POST">
            <div style="margin-bottom: 15px;">
                <input type="text" name="username" placeholder="Username" required class="auth-input" 
                       style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white;">
            </div>

            <div style="margin-bottom: 15px;">
                <input type="email" name="email" placeholder="Email address" required class="auth-input" 
                       style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white;">
            </div>

            <div style="margin-bottom: 25px;">
                <input type="password" name="password" id="register_password" placeholder="Password" required class="auth-input" 
                       style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: white;">
                <div class="glass-tube" id="password-strength-tube" style="margin-top: 10px; margin-bottom: 0;">
                    <div class="wave-fluid" id="password-strength-wave"></div>
                </div>
            </div>

            <button type="submit" class="auth-button confirm-btn" style="width: 100%; padding: 15px; cursor: pointer;">Register</button>
        </form>

        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <p class="auth-text" style="color: rgba(255,255,255,0.7);">
                Have an account? <a href="login.php" class="auth-link-main" style="color: #ffcc00; text-decoration: none; font-weight: bold;">Login</a>
            </p>
        </div>
    </div>

    <script src="js/effects.js"></script>
    <script>
        const passwordInput = document.getElementById('register_password');   //funcktion for password strength 
        const glassTube = document.getElementById('password-strength-tube');
        const waveFluid = document.getElementById('password-strength-wave');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const val = this.value;
                if (val.length === 0) {
                    glassTube.style.display = 'none'; // hide the bar 
                    return;
                }
                glassTube.style.display = 'block';

                let strength = 0;
                if (val.length >= 6) strength += 20;    // strength criteria 
                if (val.length >= 10) strength += 20; 
                if (/[A-Z]/.test(val)) strength += 20; 
                if (/[0-9]/.test(val)) strength += 20; 
                if (/[^A-Za-z0-9]/.test(val)) strength += 20; 

                let color = '#ff4757'; 
                let shadow = 'rgba(255, 71, 87, 0.6)';
                
                if (strength > 40 && strength <= 60) {
                    color = '#ffa502'; 
                    shadow = 'rgba(255, 165, 2, 0.6)';
                } else if (strength > 60 && strength <= 80) {
                    color = '#2ed573';
                    shadow = 'rgba(46, 213, 115, 0.6)';
                } else if (strength > 80) {
                    color = '#1e90ff';
                    shadow = 'rgba(30, 144, 255, 0.6)';
                }
                waveFluid.style.width = strength + '%';
                waveFluid.style.backgroundColor = color;
                waveFluid.style.boxShadow = `0 0 10px ${shadow}`;
            });
        }
    </script>
</body>
</html>