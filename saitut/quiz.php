<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play - Quiz Maker</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body class="quiz-page">

<div class="container main-quiz-island" id="quiz-container">

    <div id="quiz-media" class="quiz-media-container"></div>

    <h2 id="question-text" class="question-display">Loading question...</h2>

    <div id="answers-list" class="answers-grid"></div>

    <hr class="quiz-divider">

    <div class="quiz-footer">
        <p class="score-display">Score: <span id="score">0</span></p>
    </div>

    <div id="live-rankings" class="live-rankings">
        <h3>📊 Live Rankings</h3>
        <div id="rankings-content"></div>
    </div>

    <div class="exit-wrapper">
        <button id="exit-quiz" class="exit-btn">❌ Exit Quiz</button>
    </div>

</div>

<script>
    const quizId = <?php echo $quiz_id; ?>;
    const userId = <?php echo $_SESSION['user_id']; ?>;
</script>

<script src="js/effects.js"></script>
<script src="js/quiz.js"></script>

<script>
    const params = new URLSearchParams(window.location.search);
    const isMulti = params.get('mode') === 'multi';
    const sId = params.get('session_id');

    if (isMulti && sId) {
        const rankingsDiv = document.getElementById('live-rankings');
        const rankingsContent = document.getElementById('rankings-content');
        rankingsDiv.style.display = 'block';

        setInterval(() => {
            fetch(`multiplayer/php/manage_lobby.php?action=get_players&session_id=${sId}`)
                .then(res => res.json())
                .then(players => {
                    players.sort((a, b) => b.points - a.points);
                    
                    rankingsContent.innerHTML = "";
                    players.forEach((p, index) => {
                        const isMe = (p.user_id == userId);
                        const itemClass = isMe ? "ranking-item ranking-me" : "ranking-item";
                        const crown = index === 0 ? "👑 " : "";

                        rankingsContent.innerHTML += `
                            <div class="${itemClass}">
                                ${index + 1}. ${crown}${p.username}: ${p.points} pts ${isMe ? "(You)" : ""}
                            </div>`;
                    });
                })
                .catch(err => console.error("Leaderboard error:", err));
        }, 3000);
    }
</script>

</body>
</html>