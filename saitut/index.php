<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freaky Quiz</title>
    <meta property="og:title" content="Freaky Quiz">
    <meta property="og:description" content="Join the best Quiz Website on the Universy!">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#6c5ce7">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=3">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"></script>

</head>

<body class="quiz-page index-dark-theme">

<canvas id="c"></canvas>

<div class="ui">
  <p class="zoom"><span class="zoom zoomin">+</span><span class="zoom zoomout">-</span></p>
  <p class="zoomlevel"><span class="percent">100</span> % - (<span class="width"></span>px)(<span class="height"></span>px)</p>
  <p>Dead: <span class="dead">0</span></p>
  <p>Alive: <span class="alive">0</span></p>
  <p>Drawn: <span class="drawn">0</span></p>
  <p><span class="fps">0</span> FPS</p>
  <a class="save" href="" download="capture.png">Save</a>
</div>

<div class="index-wrapper">

  <div class="index-box">
    

    <div class="hero-section">
        <h1>
           Freaky Quiz
        </h1>
        <p>The most freaky web platform on the internet!</p>
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
</div>

<script src="js/effects.js"></script>

<script src="js/leaderboard.js"></script>
<script src="js/index_canvas.js?v=2"></script>

<script>
    
    if (window.innerWidth < 768) {
        document.querySelector('.quiz-card').classList.remove('quiz-card');
    }
</script>
<?php include "gdpr/gdpr_banner.php"; ?>
</body>
</html>