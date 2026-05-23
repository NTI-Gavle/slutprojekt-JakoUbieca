<?php
session_start();
include "php/lang_config.php";
include "php/db.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT profile_pic, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $username);
$stmt->fetch();
$stmt->close();

$display_pic = $profile_pic ? $profile_pic : "https://cdn-icons-png.flaticon.com/512/149/149071.png";


$is_published = 1;
$sql_quizzes = "SELECT q.id, q.title, q.pin, u.username AS author 
                FROM quizzes q 
                JOIN users u ON q.user_id = u.id 
                WHERE q.is_published = ? 
                ORDER BY q.id DESC";
$stmt_q = $conn->prepare($sql_quizzes);
$stmt_q->bind_param("i", $is_published);
$stmt_q->execute();
$quizzes_result = $stmt_q->get_result();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; font-src https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https://cdn-icons-png.flaticon.com https://*.fbcdn.net;">
    <title>Freaky Quiz - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard_neon.css?v=<?php echo time(); ?>">
    <link rel="manifest" href="PWA/manifest.json">
    <meta name="theme-color" content="#FF6600">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
</head>
<body>

    <canvas id="bg-canvas"></canvas>

    <?php include "php/lang_ui.php"; ?>
    <?php include "Rapport/ui.php"; ?>

    <div class="dashboard-layout">
        
        <nav class="sidebar-left glass-panel">
            <div class="logo-container">
                <h1>FREAKY QUIZ</h1>
            </div>
            <div class="nav-menu">
                <a class="nav-item" onclick="openPopup('user-search')">
                    <span class="icon">🔍</span>
                    <span><?php echo htmlspecialchars($lang['search']); ?></span>
                </a>
                <a class="nav-item" onclick="openPopup('requests')">
                    <span class="icon">📩</span>
                    <span><?php echo htmlspecialchars($lang['requests']); ?></span>
                </a>

                <a class="nav-item" href="hub.php">
                    <span class="icon">🌌</span>
                    <span><?php echo htmlspecialchars($lang['hub_nav']); ?></span>
                </a>
                <a class="nav-item" href="logout.php">
                    <span class="icon">🚪</span>
                    <span><?php echo htmlspecialchars($lang['logout']); ?></span>
                </a>
            </div>
        </nav>

        <main class="main-area">

            <header class="top-hud glass-panel">
                <div class="hud-user">
                    <img src="<?php echo htmlspecialchars($display_pic); ?>" alt="Avatar" class="avatar">
                    <div class="user-info">
                        <h2><?php echo htmlspecialchars($username); ?></h2>
                        <p>Status: Online</p>
                    </div>
                </div>
                <div class="hud-stats">

                </div>
            </header>

            <section class="content-wrapper" id="central-wrapper" style="position: relative;">
                
                <canvas id="brain-canvas"></canvas>

                <div id="liquid-glass-popup" class="liquid-glass-panel" style="display: none;">
                    <button class="btn-close-popup" onclick="closePopup()">✖</button>                           
                    
                    <div class="popup-scroll-area"> 
                        <div id="section-main" class="content-section">      
                            <h2 style="margin-bottom: 20px; color: var(--neon-orange);"><?php echo htmlspecialchars($lang['available_quizzes']); ?></h2>
                            <input type="text" id="quizSearch" class="search-box-large" placeholder="<?php echo htmlspecialchars($lang['search_quiz_placeholder']); ?>" onkeyup="filterQuizzes()">
                            
                            <div id="quiz-list-container" class="quiz-grid">
                                <?php while($quiz = $quizzes_result->fetch_assoc()): ?>
                                <div class="quiz-card-glass quiz-card" data-tilt data-tilt-max="15" data-tilt-speed="400" data-tilt-glare data-tilt-max-glare="0.3">
                                    <div class="quiz-card-content">
                                        <div class="quiz-card-title"><?php echo htmlspecialchars($quiz['title']); ?></div>
                                        <div class="quiz-card-author"><?php echo htmlspecialchars($lang['author']); ?> <?php echo htmlspecialchars($quiz['author']); ?></div>
                                    </div>
                                    <div class="quiz-card-actions">
                                        <button onclick="shareContent('<?php echo htmlspecialchars(addslashes($quiz['title'])); ?>', 'Play this awesome quiz!', '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/quiz.php?id=' . (int)$quiz['id']; ?>')" style="background: transparent; border: none; font-size: 1.2rem; cursor: pointer;" title="<?php echo htmlspecialchars($lang['share'] ?? 'Share'); ?>">🔗</button>
                                        <a href="quiz.php?id=<?php echo (int)$quiz['id']; ?>" class="btn-play"><?php echo htmlspecialchars($lang['play']); ?></a>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
 
                        <div id="section-multiplayer" class="content-section">      
                            <h2 style="margin-bottom: 20px; color: var(--neon-orange);">Multiplayer Lobby</h2>
                            <div style="text-align: center; margin-top: 40px;">
                                <a href="multiplayer/lobby.php" style="display: inline-block; padding: 15px 40px; font-size: 1.2rem; background: var(--neon-orange); color: #000; text-decoration: none; border-radius: 30px; font-weight: bold; box-shadow: 0 0 20px var(--neon-orange); transition: all 0.3s ease;">
                                    🚀 Go to Multiplayer Lobby
                                </a>
                            </div>
                        </div>

                        <div id="section-third" class="content-section">
                            <h2 style="margin-bottom: 20px; color: var(--neon-orange);">Create quiz</h2>
                            <p style="color: var(--text-secondary); text-align: center; margin-bottom: 30px;">Create quiz here.</p>
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="quiz_maker/create.php" style="display: inline-block; padding: 15px 40px; font-size: 1.2rem; background: var(--neon-orange); color: #000; text-decoration: none; border-radius: 30px; font-weight: bold; box-shadow: 0 0 20px var(--neon-orange); transition: all 0.3s ease;">
                                    📝 Create a Quiz
                                </a>
                            </div>
                        </div>

                        <div id="section-user-search" class="content-section">
                            <h2 style="margin-bottom: 20px; color: var(--neon-orange);"><?php echo htmlspecialchars($lang['find_friends']); ?></h2>
                            <input type="text" id="mainUserSearch" class="search-box-large" placeholder="<?php echo htmlspecialchars($lang['search_name_placeholder']); ?>">
                            <div id="mainSearchResults"></div>
                        </div>

                        <div id="section-requests" class="content-section">
                            <h2 style="margin-bottom: 20px; color: var(--neon-orange);"><?php echo htmlspecialchars($lang['friend_requests_title']); ?></h2>
                            <div id="friend-requests-list">
                                <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($lang['no_new_requests']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </main>

        <aside class="sidebar-right glass-panel">
            <h3 class="leaderboard-header"><?php echo htmlspecialchars($lang['top_users']); ?></h3>
            <div id="leaderboard-list">
            </div>
        </aside>

    </div>


    <script src="js/dashboard_neon.js?v=<?php echo time(); ?>"></script>
    <script src="js/dashboard_brain.js?v=<?php echo time(); ?>"></script>
    <script>
        function openPopup(sectionId) {
            document.getElementById('liquid-glass-popup').style.display = 'block';
            document.getElementById('liquid-glass-popup').classList.add('popup-open');
            
            const scrollArea = document.querySelector('.popup-scroll-area');
            if (scrollArea) scrollArea.scrollTop = 0;         // like a check for reset scroll position so it doesnt appear pushed down look a like from a previous scroll
            
            switchSection(sectionId);
        }

        function closePopup() {
            document.getElementById('liquid-glass-popup').style.display = 'none';
            document.getElementById('liquid-glass-popup').classList.remove('popup-open');
            
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        }
        function filterQuizzes() {
            let input = document.getElementById('quizSearch').value.toLowerCase();
            let cards = document.getElementsByClassName('quiz-card-glass');
            for (let i = 0; i < cards.length; i++) {
                let title = cards[i].querySelector('.quiz-card-title').innerText.toLowerCase();
                cards[i].style.display = title.includes(input) ? "flex" : "none";
            }
        }

        function switchSection(sectionId) {                   
            document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active-section'));
            document.getElementById('section-' + sectionId).classList.add('active-section');
            
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active')); 
            const activeNav = Array.from(document.querySelectorAll('.nav-item')).find(n => n.getAttribute('onclick') === `openPopup('${sectionId}')`);
            if (activeNav) activeNav.classList.add('active');

            if(sectionId === 'requests') loadFriendRequests();
        }

        function loadLeaderboard() {
            fetch('php/get_leaderboard.php')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('leaderboard-list');
                    list.innerHTML = '';
                    const topUsers = data.slice(0, 5);
                    topUsers.forEach((user, index) => {
                        const div = document.createElement('div');
                        div.className = 'leader-item';
                        div.innerHTML = `<span>#${index + 1} ${document.createTextNode(user.username).textContent}</span><span>${user.points} pts</span>`;
                        list.appendChild(div);
                    });
                });
        }

        document.getElementById('mainUserSearch').addEventListener('input', function() {
            const query = this.value;
            if(query.length < 2) { document.getElementById('mainSearchResults').innerHTML = ''; return; }
            fetch(`php/search_users.php?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(users => {
                    const results = document.getElementById('mainSearchResults');
                    results.innerHTML = '';
                    users.forEach(u => {
                        const div = document.createElement('div');
                        div.className = 'user-result-item glass-panel';
                        div.innerHTML = `<a href="user_profile.php?id=${u.id}" style="text-decoration: none; color: white; font-weight: bold; width: 100%;">${document.createTextNode(u.username).textContent}</a>`;
                        results.appendChild(div);
                    });
                });
        });

        function sendFriendRequest(targetId) {
            fetch('php/manage_friends.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'send_request', target_id: targetId })
            }).then(res => res.json()).then(data => alert(data.message));
        }

        function loadFriendRequests() {
            fetch('php/manage_friends.php?action=get_requests')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('friend-requests-list');
                    if(data.length === 0) { list.innerHTML = '<p style="color: var(--text-secondary);"><?php echo htmlspecialchars($lang['everything_reviewed'] ?? 'No requests'); ?></p>'; return; }
                    list.innerHTML = '';
                    data.forEach(req => {
                        const div = document.createElement('div');
                        div.className = 'user-result-item glass-panel';
                        div.innerHTML = `<span>${document.createTextNode(req.username).textContent}</span>
                            <div style="display:flex; gap:10px;">
                                <button onclick="respondRequest(${req.request_id}, 'accept')" class="btn-friend btn-accept"><?php echo htmlspecialchars($lang['accept'] ?? 'Accept'); ?></button>
                                <button onclick="respondRequest(${req.request_id}, 'decline')" class="btn-friend btn-decline"><?php echo htmlspecialchars($lang['decline'] ?? 'Decline'); ?></button>
                            </div>`;
                        list.appendChild(div);
                    });
                });
        }

        function respondRequest(reqId, status) {
            fetch('php/manage_friends.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'respond_request', request_id: reqId, status: status })
            }).then(() => loadFriendRequests());
        }

        document.addEventListener('DOMContentLoaded', loadLeaderboard);
    </script>
    <script src="js/share.js"></script>
    <?php include_once __DIR__ . "/Rapport/ui.php"; ?>
</body>
</html>