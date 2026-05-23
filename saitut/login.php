<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Paffi</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
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
            --aspect-ratio: 1.33;
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

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

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
            margin-bottom: 10px;
        }

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
        }
        
        .sign-in { margin-top: 5px; }

        .google-sign-in {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: none;
            color: white;
        }

        .button:hover {
            transform: translateY(-2px);
        }
        
        .sign-in:hover {
            background: #e25822;
            box-shadow:
                inset 0px 3px 6px rgba(255, 255, 255, 0.4),
                inset 0px -3px 6px rgba(0, 0, 0, 0.5),
                0px 6px 15px rgba(255, 123, 0, 0.5);
        }

        .google-sign-in:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .icon { height: 18px; }

        .footer {
            width: 100%;
            text-align: center;
            color: var(--footer-color);
            font-size: 13px;
            margin-top: 15px;
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
        <form class="form" action="php/login_user.php" method="POST">
          <div class="logo"></div>
          <span class="header">Welcome Back!</span>
          
          <input type="text" name="username" placeholder="Username" class="input" required />
          <input type="password" name="password" placeholder="Password" class="input" required />
          
          <button type="submit" class="button sign-in">Sign In</button>
          
          <button type="button" class="button google-sign-in" onclick="alert('Google SignIn coming soon!')">
            <svg
              class="icon"
              viewBox="-3 0 262 262"
              xmlns="http://www.w3.org/2000/svg"
              preserveAspectRatio="xMidYMid"
              fill="#000000"
            >
              <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
              <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
              <g id="SVGRepo_iconCarrier">
                <path d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" fill="#4285F4"></path>
                <path d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" fill="#34A853"></path>
                <path d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" fill="#FBBC05"></path>
                <path d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" fill="#EB4335"></path>
              </g>
            </svg>
            <span class="span two"> Sign in with Google </span>
          </button>
    
          <p class="footer">
            Don't have an account? <a href="register.php" class="link">Sign up, it's free!</a><br>
            <a href="forgot_password.php" class="link" style="color: rgba(255,255,255,0.5); font-size: 11px;">Forgot password?</a>
          </p>
        </form>
      </div>
    </div>
    
    <?php include "gdpr/gdpr_banner.php"; ?>
</body>
</html>