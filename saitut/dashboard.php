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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; font-src https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; script-src 'self' 'unsafe-inline'; img-src 'self' data: https://cdn-icons-png.flaticon.com;">
    <title>Quiz Master - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor">
        <?php for($i=0; $i<100; $i++): ?>
            <div class="blade" style="left: <?php echo ($i); ?>%; height: <?php echo rand(60, 120); ?>px; animation-delay: <?php echo rand(0, 40)/10; ?>s;"></div>
        <?php endfor; ?>
    </div>

    <?php include "php/lang_ui.php"; ?>

    <div class="dashboard-container">
        
        <div class="tile tile-home" onclick="switchSection('main')">
            <span class="icon-lg">🏠</span>
            <small><?php echo htmlspecialchars($lang['home']); ?></small>
        </div>

        <div class="tile tile-user-profile" onclick="window.location='profile.php'">
            <img src="<?php echo htmlspecialchars($display_pic); ?>" alt="User">
            <small class="mt-5"><?php echo htmlspecialchars($username); ?></small>
        </div>

        <div class="tile tile-chat" onclick="switchSection('chat')">
            <span>💬</span>
            <small><?php echo htmlspecialchars($lang['chat']); ?></small>
        </div>

        <div class="tile tile-search" onclick="switchSection('user-search')">
            <span>🔍</span>
            <small><?php echo htmlspecialchars($lang['search']); ?></small>
        </div>

        <div class="tile tile-leaderboard">
            <h4 class="leaderboard-title"><?php echo htmlspecialchars($lang['top_10']); ?></h4>
            <div id="leaderboard-list"></div>
        </div>

        <div class="tile tile-requests-small" onclick="switchSection('requests')">
            <span>📩</span>
            <small><?php echo htmlspecialchars($lang['requests']); ?></small>
        </div>

        <div class="tile tile-logout" onclick="window.location='logout.php'">
            <span>🚪</span>
            <small><?php echo htmlspecialchars($lang['logout']); ?></small>
        </div>

        <div class="tile tile-main" id="main-island">
            
            <div class="mode-selection-container">
                <a href="multiplayer/lobby.php" class="mode-island">
                    <span class="icon-xl">⚔️</span>
                    <b class="mode-title"><?php echo htmlspecialchars($lang['multiplayer_mode']); ?></b>
                </a>
            </div>

            <div class="main-content-scrollable">
                <div id="section-main" class="content-section active-section">
                    <h1><?php echo htmlspecialchars($lang['available_quizzes']); ?></h1>
                    <input type="text" id="quizSearch" class="quiz-search-bar" placeholder="<?php echo htmlspecialchars($lang['search_quiz_placeholder']); ?>" onkeyup="filterQuizzes()">
                    
                    <div id="quiz-list-container">
                        <?php while($quiz = $quizzes_result->fetch_assoc()): ?>
                        <div class="quiz-card quiz-card-styled">
                            <div>
                                <b class="quiz-title quiz-title-styled"><?php echo htmlspecialchars($quiz['title']); ?></b>
                                <small class="quiz-author"><?php echo htmlspecialchars($lang['author']); ?> <?php echo htmlspecialchars($quiz['author']); ?></small>
                            </div>
                            <a href="quiz.php?id=<?php echo (int)$quiz['id']; ?>" class="btn-play"><?php echo htmlspecialchars($lang['play']); ?></a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div id="section-user-search" class="content-section">
                    <h1><?php echo htmlspecialchars($lang['find_friends']); ?></h1>
                    <input type="text" id="mainUserSearch" class="search-box-large" placeholder="<?php echo htmlspecialchars($lang['search_name_placeholder']); ?>">
                    <div id="mainSearchResults" class="mt-20"></div>
                </div>

                <div id="section-chat" class="content-section">
                    <h1><?php echo htmlspecialchars($lang['chat_title']); ?></h1>
                    <div class="chat-container">
                        <div id="chat-friends-list" class="chat-friends-list-styled">
                            <p class="loading-text"><?php echo htmlspecialchars($lang['loading']); ?></p>
                        </div>
                        <div id="chat-window" class="chat-window-styled">
                            <p class="empty-chat-msg"><?php echo htmlspecialchars($lang['choose_friend_chat']); ?></p>
                        </div>
                    </div>
                </div>

                <div id="section-requests" class="content-section">
                    <h1><?php echo htmlspecialchars($lang['friend_requests_title']); ?></h1>
                    <div id="friend-requests-list">
                        <p><?php echo htmlspecialchars($lang['no_new_requests']); ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        let currentChatId = null;
        let chatInterval = null;

        function filterQuizzes() {
            let input = document.getElementById('quizSearch').value.toLowerCase();
            let cards = document.getElementsByClassName('quiz-card');
            for (let i = 0; i < cards.length; i++) {
                let title = cards[i].querySelector('.quiz-title').innerText.toLowerCase();
                cards[i].style.display = title.includes(input) ? "flex" : "none";
            }
        }

        function switchSection(sectionId) {                   
            clearInterval(chatInterval);
            document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active-section'));
            document.getElementById('section-' + sectionId).classList.add('active-section');
            if(sectionId === 'requests') loadFriendRequests();
            if(sectionId === 'chat') loadChatFriends();
        }

        function loadLeaderboard() {
            fetch('php/get_leaderboard.php')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('leaderboard-list');
                    list.innerHTML = '';
                    data.forEach((user, index) => {
                        const div = document.createElement('div');
                        div.className = 'leader-item';
                        div.innerHTML = `<span>${index + 1}. ${document.createTextNode(user.username).textContent}</span><span>${user.points} <?php echo htmlspecialchars($lang['pts']); ?></span>`;
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
                        div.className = 'user-result-item';
                        div.innerHTML = `<span>${document.createTextNode(u.username).textContent}</span>
                                         <button class="btn-friend" onclick="sendFriendRequest(${u.id})"><?php echo htmlspecialchars($lang['add']); ?></button>`;
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
                    if(data.length === 0) { list.innerHTML = '<p><?php echo htmlspecialchars($lang['everything_reviewed']); ?></p>'; return; }
                    list.innerHTML = '';
                    data.forEach(req => {
                        const div = document.createElement('div');
                        div.className = 'user-result-item';
                        div.innerHTML = `<span>${document.createTextNode(req.username).textContent}</span>
                            <div>
                                <button onclick="respondRequest(${req.request_id}, 'accept')" class="btn-friend btn-accept"><?php echo htmlspecialchars($lang['accept']); ?></button>
                                <button onclick="respondRequest(${req.request_id}, 'decline')" class="btn-friend btn-decline"><?php echo htmlspecialchars($lang['decline']); ?></button>
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

        function loadChatFriends() {
            fetch('php/manage_friends.php?action=get_friends')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('chat-friends-list');
                    if (data.length === 0) { list.innerHTML = '<p class="empty-msg"><?php echo htmlspecialchars($lang['no_friends_yet']); ?></p>'; return; }
                    list.innerHTML = '';
                    data.forEach(f => {
                        const div = document.createElement('div');
                        div.className = "friend-item";
                        div.onclick = () => openChat(f.user_id, f.username);
                        div.innerHTML = `<div class="friend-avatar">${f.username[0].toUpperCase()}</div>
                                         <span class="friend-name-styled">${document.createTextNode(f.username).textContent}</span>`;
                        list.appendChild(div);
                    });
                });
        }

        function openChat(friendId, friendName) {                   
            currentChatId = friendId;
            const chatWindow = document.getElementById('chat-window');
            chatWindow.innerHTML = `
                <h3 class="chat-header"><?php echo htmlspecialchars($lang['chat_with']); ?> ${document.createTextNode(friendName).textContent}</h3>
                <div id="messages-container" class="messages-container-styled"></div>
                <div class="chat-input-area">
                    <input type="text" id="chatInput" class="search-box-large chat-input-styled" placeholder="<?php echo htmlspecialchars($lang['write_message_placeholder']); ?>" onkeypress="if(event.key==='Enter') sendMessage()">
                    <button onclick="sendMessage()" class="btn-send">🚀</button>
                </div>`;
            loadMessages();
            clearInterval(chatInterval);
            chatInterval = setInterval(loadMessages, 3000);
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const msg = input.value.trim();
            if (!msg || !currentChatId) return;
            fetch('php/manage_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'send', friend_id: currentChatId, message: msg })
            }).then(() => { input.value = ''; loadMessages(); });
        }

        function loadMessages() {
            if (!currentChatId) return;
            fetch(`php/manage_chat.php?action=get&friend_id=${currentChatId}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('messages-container');
                    if (!container) return;
                    container.innerHTML = '';
                    data.forEach(m => {
                        const isMe = m.sender_id == <?php echo $user_id; ?>;
                        const div = document.createElement('div');
                        div.className = `msg ${isMe ? 'msg-me' : 'msg-them'}`;
                        div.textContent = m.message;
                        container.appendChild(div);
                    });
                    container.scrollTop = container.scrollHeight;
                });
        }

        document.addEventListener('DOMContentLoaded', loadLeaderboard);

        document.querySelectorAll('.tile').forEach(tile => {
            tile.addEventListener('mousemove', e => {
                const rect = tile.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const rY = ((x / rect.width) - 0.5) * 10;
                const rX = ((y / rect.height) - 0.5) * -10;
                tile.style.transform = `perspective(1000px) scale(1.02) rotateX(${rX}deg) rotateY(${rY}deg)`;
            });
            tile.addEventListener('mouseleave', () => tile.style.transform = `perspective(1000px) scale(1) rotateX(0) rotateY(0)`);
        });
    </script>
</body>
</html>