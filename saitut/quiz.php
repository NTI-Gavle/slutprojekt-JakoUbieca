<?php
session_start();
include "php/lang_config.php";
$is_bot = preg_match('/bot|crawler|spider|discord|facebook|twitter|slack|whatsapp|telegram|skype|vkshare/i', $_SERVER['HTTP_USER_AGENT'] ?? '');

if (!isset($_SESSION['user_id']) && !$is_bot) {
    header("Location: login.php");
    exit;
}

$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

include "php/db.php";
$quiz_title = "Quiz Maker";
if ($quiz_id > 0) {
    $stmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->bind_result($q_title);
    if ($stmt->fetch()) {
        $quiz_title = $q_title;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play - <?php echo htmlspecialchars($quiz_title); ?></title>
    
    <meta property="og:title" content="<?php echo htmlspecialchars($quiz_title); ?>">       <!-- Meta Tag2 -->
    <meta property="og:description" content="Play <?php echo htmlspecialchars($quiz_title); ?> on Freaky Quiz! Join the best Quiz Website on the Universy!">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#6c5ce7">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body class="quiz-page">

<?php include "php/lang_ui.php"; ?>
<?php include "Rapport/ui.php"; ?>

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

    <div class="exit-wrapper" style="display: flex; gap: 15px; justify-content: center;">
        <button id="share-quiz" class="exit-btn" style="background: #6c5ce7; box-shadow: 0 6px 0 #4834d4;" onclick="shareContent('Play this Quiz!', 'Can you beat my score on this awesome quiz?')">🔗 Share Quiz</button>
        <button id="exit-quiz" class="exit-btn">❌ Exit Quiz</button>
    </div>

</div>

<script>
    const quizId = <?php echo $quiz_id; ?>;
    const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>;
</script>

<script src="js/effects.js"></script>
<script src="js/quiz.js?v=3"></script>

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

<script src="js/share.js"></script>

</body>
</html>