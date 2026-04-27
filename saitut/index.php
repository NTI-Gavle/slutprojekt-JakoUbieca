<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freaky Quiz</title>
    <link rel="stylesheet" href="css/style.css">

</head>
<body class="quiz-page">

<div class="container main-quiz-island index-island quiz-card">
    
    <div class="hero-section">
        <h1>
           Freaky Quiz
        </h1>
        <p>Become freaky!</p>
    </div>

    <div class="main-buttons">
        <a href="register.php">
            <button class="confirm-btn">
                Create Account
            </button>
        </a>

        <a href="login.php">
            <button class="answer-btn">
                Login
            </button>
        </a>
    </div>

    <hr class="index-divider">

    <div class="leaderboard-section">
        <h2>
            🏆 Global Leaderboard
        </h2>
        
        <div id="leaderboard" class="leaderboard-container">
            <p>Loading top players...</p>
        </div>
    </div>

</div>

<script src="js/effects.js"></script>
<script src="js/leaderboard.js"></script>

<script>
    
    if (window.innerWidth < 768) {
        document.querySelector('.quiz-card').classList.remove('quiz-card');
    }
</script>

</body>
</html>