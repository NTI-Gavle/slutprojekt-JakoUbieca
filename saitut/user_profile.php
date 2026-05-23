<?php
session_start();
include "php/lang_config.php";
include "php/db.php";

$is_bot = preg_match('/bot|crawler|spider|discord|facebook|twitter|slack|whatsapp|telegram|skype|vkshare/i', $_SERVER['HTTP_USER_AGENT'] ?? '');

if (!isset($_SESSION['user_id']) && !$is_bot) {
    header("Location: login.php");
    exit;
}

$my_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($profile_id === 0) {
    die("Invalid User ID");
}

if ($profile_id === $my_id) {
    header("Location: profile.php");
    exit;
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");   // admin check
$stmt->bind_param("i", $my_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT username, profile_pic, points, bio, cover_url FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$stmt->bind_result($username, $profile_pic, $points, $bio, $cover_url);
if (!$stmt->fetch()) {
    die("User not found");
}
$stmt->close();

$display_pic = $profile_pic ? $profile_pic : "https://cdn-icons-png.flaticon.com/512/149/149071.png";

$quizzes = [];             // user created quizzes
$q_stmt = $conn->prepare("SELECT id, title FROM quizzes WHERE user_id = ? AND is_published = 1 ORDER BY id DESC");
$q_stmt->bind_param("i", $profile_id);
$q_stmt->execute();
$q_res = $q_stmt->get_result();
while ($row = $q_res->fetch_assoc()) {
    $quizzes[] = $row;
}
$q_stmt->close();

$achievements = [];
$a_stmt = $conn->prepare("SELECT a.name, a.description, a.icon FROM user_achievements ua JOIN achievements a ON ua.achievement_id = a.id WHERE ua.user_id = ? ORDER BY ua.awarded_at DESC");
$a_stmt->bind_param("i", $profile_id);
$a_stmt->execute();
$a_res = $a_stmt->get_result();
while ($row = $a_res->fetch_assoc()) {
    $achievements[] = $row;
}
$a_stmt->close();

$forum_threads = [];
$t_stmt = $conn->prepare("SELECT id, title, created_at FROM forum_threads WHERE user_id = ? ORDER BY created_at DESC");
$t_stmt->bind_param("i", $profile_id);
$t_stmt->execute();
$t_res = $t_stmt->get_result();
while ($row = $t_res->fetch_assoc()) {
    $forum_threads[] = $row;
}
$t_stmt->close();

$friendship_status = 'none';     // friendship status from viewing user 
$friendship_id = 0;
$f_stmt = $conn->prepare("SELECT id, status, user_id FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
$f_stmt->bind_param("iiii", $my_id, $profile_id, $profile_id, $my_id);
$f_stmt->execute();
$f_res = $f_stmt->get_result();
if ($row = $f_res->fetch_assoc()) {
    $friendship_id = $row['id'];
    if ($row['status'] == 'accepted') {
        $friendship_status = 'friends';
    } else {
        if ($row['user_id'] == $my_id) {
            $friendship_status = 'pending_sent';
        } else {
            $friendship_status = 'pending_received';
        }
    }
}
$f_stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($username); ?>'s Profile</title>
    
    <meta property="og:title" content="<?php echo htmlspecialchars($username); ?>'s Profile">            
    <meta property="og:description" content="See <?php echo htmlspecialchars($username); ?>'s freaky profile on Freaky Quiz!">
    <meta property="og:image" content="<?php echo htmlspecialchars($display_pic); ?>">
    <meta property="og:type" content="profile">
    <meta name="theme-color" content="#6c5ce7">
    <link rel="stylesheet" href="css/user_profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/global_neon.css?v=<?php echo time(); ?>">
    <style>
        .cover-photo {
            width: 100%;
            height: 250px;
            background-color: #34495e;
            background-image: url('<?php echo htmlspecialchars($cover_url ?? ""); ?>');
            background-size: cover;
            background-position: center;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            margin-bottom: -80px;
        }
        .profile-pic-large {
            border: 5px solid rgba(255, 255, 255, 0.8);
            z-index: 2;
            position: relative;
        }
        .bio-text {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            font-style: italic;
            max-width: 600px;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="neon-theme">

    <div class="bg-particles">
        <div class="particle" style="width: 300px; height: 300px; top: 10%; left: 20%;"></div>
        <div class="particle" style="width: 200px; height: 200px; top: 60%; left: 80%; animation-delay: -5s;"></div>
        <div class="particle" style="width: 250px; height: 250px; top: 80%; left: 30%; animation-delay: -10s;"></div>
    </div>

    <div class="neon-container" style="--form-width: 1200px; width: 95vw; max-width: 1200px; height: 95vh; margin: auto;">
        <div class="neon-glass-box">
            <a href="dashboard.php" class="back-link"><?php echo htmlspecialchars($lang['profile_back_dashboard']); ?></a>

            <div class="neon-profile-header">
                <div class="neon-profile-cover" style="background-image: url('<?php echo htmlspecialchars($cover_url ?? ""); ?>');"></div>
                
                <div class="neon-profile-nav">
                    <img src="<?php echo htmlspecialchars($display_pic); ?>" alt="Profile Picture" class="neon-profile-avatar">
                    
                    <div class="neon-profile-actions">
                        <?php if ($friendship_status == 'none'): ?>
                            <button class="neon-btn neon-btn-outline" onclick="sendFriendRequest(<?php echo $profile_id; ?>)"><?php echo htmlspecialchars($lang['profile_send_request']); ?></button>
                        <?php elseif ($friendship_status == 'pending_sent'): ?>
                            <button class="neon-btn neon-btn-outline" style="border-color: #95afc0; color: #95afc0; cursor: default;"><?php echo htmlspecialchars($lang['profile_request_sent']); ?></button>
                        <?php elseif ($friendship_status == 'pending_received'): ?>
                            <button class="neon-btn neon-btn-outline" style="border-color: #f0932b; color: #f0932b; cursor: default;"><?php echo htmlspecialchars($lang['profile_review_request']); ?></button>
                        <?php elseif ($friendship_status == 'friends'): ?>
                            <button class="neon-btn neon-btn-outline" 
                                    style="border-color: #22a6b3; color: #22a6b3;" 
                                    onmouseover="this.innerText='❌ <?php echo addslashes($lang['remove'] ?? 'Remove'); ?>'; this.style.borderColor='#ff4757'; this.style.color='#ff4757';" 
                                    onmouseout="this.innerText='<?php echo addslashes($lang['profile_friends']); ?>'; this.style.borderColor='#22a6b3'; this.style.color='#22a6b3';" 
                                    onclick="unfriendUser(<?php echo $friendship_id; ?>)">
                                <?php echo htmlspecialchars($lang['profile_friends']); ?>
                            </button>
                        <?php endif; ?>

                        <button class="neon-btn" onclick="shareContent('<?php echo htmlspecialchars(addslashes($username)); ?>\'s Profile', 'Check out this profile on the platform!')">🔗 <?php echo htmlspecialchars($lang['profile_share_profile']); ?></button>

                        <?php if ($is_admin == 1): ?>
                            <button class="neon-btn neon-btn-outline" style="border-color: #ffcc00; color: #ffcc00;" onclick="openMedalModal(<?php echo $profile_id; ?>)">
                                🏅 <?php echo htmlspecialchars($lang['profile_award_medal']); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="neon-profile-info">
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                    <p style="margin: 0; color: var(--neon-orange);">🏆 <?php echo htmlspecialchars($lang['total_points']); ?>: <strong><?php echo $points; ?></strong></p>
                    
                    <?php if (!empty($bio)): ?>
                        <div class="bio-text">
                            "<?php echo nl2br(htmlspecialchars($bio)); ?>"
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <div class="glass-panel">
            <h2 class="aero-title"><?php echo htmlspecialchars($lang['profile_medals_achievements']); ?></h2>
            <?php if (count($achievements) > 0): ?>
                <div class="achievements-flex">
                    <?php foreach ($achievements as $ach): ?>
                        <div class="ach-card" title="<?php echo htmlspecialchars($ach['description']); ?>">
                            <span class="icon"><?php echo htmlspecialchars($ach['icon']); ?></span>
                            <span class="name"><?php echo htmlspecialchars($ach['name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #fff; text-align: center;"><?php echo htmlspecialchars($lang['profile_no_achievements']); ?></p>
            <?php endif; ?>
        </div>
        <div class="glass-panel">
            <h2 class="aero-title"><?php echo htmlspecialchars($lang['profile_published_quizzes']); ?></h2>
            <?php if (count($quizzes) > 0): ?>
                <div class="quizzes-grid">
                    <?php foreach ($quizzes as $q): ?>
                        <div class="quiz-card-aero">
                            <h4><?php echo htmlspecialchars($q['title']); ?></h4>
                            <a href="quiz.php?id=<?php echo $q['id']; ?>" class="btn-aero"><?php echo htmlspecialchars($lang['play'] ?? 'Play Quiz'); ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #fff; text-align: center;"><?php echo htmlspecialchars($lang['profile_no_quizzes_published']); ?></p>
            <?php endif; ?>
        </div>

        <div class="glass-panel">
            <h2 class="aero-title"><?php echo htmlspecialchars($lang['profile_forum_activity']); ?></h2>
            <?php if (count($forum_threads) > 0): ?>
                <div class="quizzes-grid">
                    <?php foreach ($forum_threads as $t): ?>
                        <div class="quiz-card-aero">
                            <h4><?php echo htmlspecialchars($t['title']); ?></h4>
                            <p style="font-size:0.8rem; color:rgba(255,255,255,0.7);"><?php echo date("M d, Y", strtotime($t['created_at'])); ?></p>
                            <a href="forum/thread.php?id=<?php echo $t['id']; ?>" class="btn-aero" style="margin-top:10px;"><?php echo htmlspecialchars($lang['forum_view_thread']); ?></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #fff; text-align: center;"><?php echo htmlspecialchars($lang['profile_no_forum_activity']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($is_admin == 1): ?>
    <div id="medalModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: rgba(255, 255, 255, 0.9); padding: 25px; border-radius: 15px; width: 320px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <h2 style="margin-top: 0; color: #0984e3;">Award a Medal</h2>
            <p style="color: #2d3436;">Select a medal to give to <?php echo htmlspecialchars($username); ?>:</p>
            <select id="medalSelect" style="width: 100%; padding: 10px; margin: 15px 0; border-radius: 8px; border: 1px solid #74b9ff; background: #fff;">
                <option value="">Loading...</option>
            </select>
            <input type="hidden" id="medalUserId" value="<?php echo $profile_id; ?>">
            <div style="display: flex; justify-content: space-between; margin-top: 15px;">
                <button onclick="closeMedalModal()" style="padding: 10px 15px; background: #dfe6e9; border: none; border-radius: 8px; color: #2d3436; cursor: pointer; font-weight: bold;">Cancel</button>
                <button onclick="giveMedal()" style="padding: 10px 15px; background: #00cec9; border: none; border-radius: 8px; color: white; font-weight: bold; cursor: pointer; box-shadow: 0 4px 6px rgba(0,206,201,0.3);">Give Medal</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>

    document.querySelectorAll('.bubble-visual').forEach(bubble => {
        bubble.addEventListener('click', function() {
            this.classList.add('pop');
            setTimeout(() => {
                this.parentElement.remove();
            }, 300);
        });
    });

    function sendFriendRequest(targetId) {
        fetch('php/manage_friends.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'send_request', target_id: targetId })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            location.reload(); 
        });
    }

    <?php if ($is_admin == 1): ?>
    function openMedalModal(userId) {
        document.getElementById('medalModal').style.display = 'flex';
        const select = document.getElementById('medalSelect');
        select.innerHTML = '<option value="">Loading...</option>';
        
        fetch('admin/get_achievements.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    select.innerHTML = '<option value="">-- Select Medal --</option>';
                    data.data.forEach(ach => {
                        select.innerHTML += `<option value="${ach.id}">${ach.icon} ${ach.name}</option>`;
                    });
                } else {
                    select.innerHTML = '<option value="">Failed to load</option>';
                }
            });
    }

    function closeMedalModal() {
        document.getElementById('medalModal').style.display = 'none';
    }

    function giveMedal() {
        const userId = document.getElementById('medalUserId').value;
        const achId = document.getElementById('medalSelect').value;
        
        if (!achId) {
            alert("Please select a medal first!");
            return;
        }
        
        fetch('admin/award_achievement.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, achievement_id: achId })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload(); 
            }
        });
    }
    <?php endif; ?>

    function unfriendUser(id) {
        if (confirm("Are you sure you want to remove this user from your friends?")) {
            fetch('php/manage_friends.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ action: 'unfriend', friendship_id: id })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }
    </script>
    <script src="js/share.js"></script>
</body>
</html>
