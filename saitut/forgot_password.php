<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/quiz.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        
        body {
            background-color: #1A1511;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255,123,0,0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(255,155,68,0.15) 0px, transparent 50%);
            background-size: 200% 200%;
            animation: gradientMove 10s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .bg-particles {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            background: rgba(255, 123, 0, 0.5);
            border-radius: 50%;
            filter: blur(40px);
            animation: float 20s infinite ease-in-out alternate;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(150px, -150px) scale(1.5); }
        }

        .container {
            --form-width: 400px;
            --aspect-ratio: 1.2; 
            --login-box-color: rgba(26, 21, 17, 0.7);
            --input-color: rgba(255, 255, 255, 0.05);
            --button-color: #FF7B00;
            --footer-color: rgba(255, 255, 255, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: var(--login-box-color);
            border-radius: 24px;
            width: calc(var(--form-width) + 2px);
            height: calc(var(--form-width) * var(--aspect-ratio) + 2px);
            z-index: 8;
            box-shadow:
                0 4px 8px rgba(0, 0, 0, 0.2),
                0 8px 16px rgba(0, 0, 0, 0.4),
                0 0 15px rgba(255, 123, 0, 0.2),
                0 0 30px rgba(255, 123, 0, 0.1);
        }

        .container::before {
            content: "";
            position: absolute;
            inset: -150px;
            z-index: -2;
            background: conic-gradient(
                from 45deg,
                transparent 75%,
                #FF7B00,
                #FF9B44,
                transparent 100%
            );
            animation: spin 3s linear infinite;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }

        .login-box {
            background: var(--login-box-color);
            border-radius: 24px;
            padding: 35px 28px;
            width: var(--form-width);
            height: calc(var(--form-width) * var(--aspect-ratio));
            position: absolute;
            z-index: 10;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            box-shadow:
                inset 0 40px 60px -8px rgba(255, 123, 0, 0.05),
                inset 4px 0 12px -6px rgba(255, 123, 0, 0.05),
                inset 0 0 12px -4px rgba(255, 123, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 15px;
            height: 100%;
        }

        .logo {
            width: 80px;
            height: 80px;
            background-image: url('logo/TorusKNOTTT-1.png');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            margin-bottom: 5px;
            border-radius: 20px;
        }

        .header {
            width: 100%;
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            padding: 6px;
            color: white;
            margin-bottom: 5px;
        }

        .desc {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            text-align: center;
            margin-bottom: 5px;
        }

        .input-group { width: 100%; display: flex; flex-direction: column; gap: 8px; }

        .input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: var(--input-color);
            color: white;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .input::placeholder { color: rgba(255,255,255,0.4); }

        .input:focus {
            border: 1px solid #FF7B00;
            background: rgba(255, 123, 0, 0.05);
            box-shadow: 0 0 10px rgba(255, 123, 0, 0.2);
        }

        .button {
            width: 100%;
            height: 45px;
            border: none;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background: var(--button-color);
            color: white;
            transition: 0.3s;
            box-shadow:
                inset 0px 3px 6px -4px rgba(255, 255, 255, 0.4),
                inset 0px -3px 6px -2px rgba(0, 0, 0, 0.5),
                0 4px 10px rgba(255, 123, 0, 0.3);
            margin-top: 10px;
        }
        
        .button:hover { transform: translateY(-2px); }
        .sign-in:hover {
            background: #e25822;
            box-shadow: inset 0px 3px 6px rgba(255, 255, 255, 0.4), inset 0px -3px 6px rgba(0, 0, 0, 0.5), 0px 6px 15px rgba(255, 123, 0, 0.5);
        }

        .footer {
            width: 100%;
            text-align: center;
            color: var(--footer-color);
            font-size: 13px;
            margin-top: 5px;
            line-height: 1.6;
        }

        .footer .link {
            position: relative;
            color: #FF7B00;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer .link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            border-radius: 6px;
            height: 1px;
            background: currentColor;
            transition: width 0.3s ease;
        }

        .footer .link:hover { color: #FF9B44; }
        .footer .link:hover::after { width: 100%; }
    </style>
</head>
<body>

    <div class="bg-particles">
        <div class="particle" style="width: 400px; height: 400px; top: -100px; left: -100px; animation-duration: 25s; animation-delay: 0s;"></div>
        <div class="particle" style="width: 300px; height: 300px; bottom: -50px; right: 5%; animation-duration: 20s; animation-delay: 2s; background: rgba(255, 155, 68, 0.3);"></div>
        <div class="particle" style="width: 250px; height: 250px; top: 40%; left: 30%; animation-duration: 18s; animation-delay: 5s; background: rgba(226, 88, 34, 0.2);"></div>
    </div>

    <div class="container">
      <div class="login-box">
        <form class="form" action="php/send_reset_link.php" method="POST">
          <div class="logo"></div>
          <span class="header">Recovery</span>
          <p class="desc">Enter your email to receive a reset link.</p>
          
          <div class="input-group">
            <input type="email" name="email" placeholder="Your email address" class="input" required />
          </div>
          
          <button type="submit" class="button sign-in">Send Link</button>
          
          <p class="footer">
            Remembered your password?<br>
            <a href="login.php" class="link">Back to login</a>
          </p>
        </form>
      </div>
    </div>
    
</body>
</html>