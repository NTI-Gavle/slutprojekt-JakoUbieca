<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Забравена парола</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <h1 style="font-size: 2.5rem;">Password Recovery</h1>
        <p style="color: rgba(255,255,255,0.8); margin-bottom: 20px;">Enter your email to receive a reset link.</p>
        <form action="php/send_reset_link.php" method="POST">
            <input type="email" name="email" placeholder="Your email" required class="auth-input">
            <button type="submit" class="auth-button">Send Link</button>
        </form>
        <p style="margin-top: 20px;">
            <a href="login.php" class="auth-link-sub">← Back to login</a>
        </p>
    </div>
</body>
</html>