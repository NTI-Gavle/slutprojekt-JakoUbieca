<?php
session_start();
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
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

$stmt = $conn->prepare("SELECT username, profile_pic, points FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$stmt->bind_result($username, $profile_pic, $points);
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

$friendship_status = 'none';     // friendship status from viewing user 
$f_stmt = $conn->prepare("SELECT status, user_id FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
$f_stmt->bind_param("iiii", $my_id, $profile_id, $profile_id, $my_id);
$f_stmt->execute();
$f_res = $f_stmt->get_result();
if ($row = $f_res->fetch_assoc()) {
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
    <link rel="stylesheet" href="css/user_profile.css?v=<?php echo time(); ?>">
</head>
<body class="aero-body">

    <?php for($i=0; $i<15; $i++): 
        $drift = rand(-100, 100); 
        $size = rand(20, 80); 
    ?>
        <div class="bubble-container" style="left: <?php echo rand(5, 95); ?>%; animation-duration: <?php echo rand(8, 15); ?>s; animation-delay: <?php echo rand(0, 5); ?>s; --drift: <?php echo $drift; ?>px;">
            <div class="bubble-visual" style="width: <?php echo $size; ?>px; height: <?php echo $size; ?>px;"></div>
        </div>
    <?php endfor; ?>

    <div class="aero-container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="glass-panel">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($display_pic); ?>" alt="Profile Picture" class="profile-pic-large">
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                    <p>🏆 Points: <strong><?php echo $points; ?></strong></p>
                    
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <?php if ($friendship_status == 'none'): ?>
                            <button class="btn-aero" onclick="sendFriendRequest(<?php echo $profile_id; ?>)">➕ Add Friend</button>
                        <?php elseif ($friendship_status == 'pending_sent'): ?>
                            <button class="btn-aero" style="background: #95afc0; cursor: default;">⏳ Request Sent</button>
                        <?php elseif ($friendship_status == 'pending_received'): ?>
                            <button class="btn-aero" style="background: #f0932b; cursor: default;">📥 Review Request in Dashboard</button>
                        <?php elseif ($friendship_status == 'friends'): ?>
                            <button class="btn-aero" style="background: #22a6b3; cursor: default;">🤝 Friends</button>
                        <?php endif; ?>

                        <?php if ($is_admin == 1): ?>
                            <button class="btn-aero btn-admin-action" onclick="openMedalModal(<?php echo $profile_id; ?>)">
                                🏅 Give Medal (Admin)
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel">
            <h2 class="aero-title">🏅 Medals & Achievements</h2>
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
                <p style="color: #fff; text-align: center;">This user hasn't earned any medals yet.</p>
            <?php endif; ?>
        </div>
        <div class="glass-panel">
            <h2 class="aero-title">🎮 Published Quizzes</h2>
            <?php if (count($quizzes) > 0): ?>
                <div class="quizzes-grid">
                    <?php foreach ($quizzes as $q): ?>
                        <div class="quiz-card-aero">
                            <h4><?php echo htmlspecialchars($q['title']); ?></h4>
                            <a href="quiz.php?id=<?php echo $q['id']; ?>" class="btn-aero">Play Quiz</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #fff; text-align: center;">No published quizzes found.</p>
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
    </script>
</body>
</html>
