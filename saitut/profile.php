<?php
session_start();
include "php/lang_config.php";
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT profile_pic, points, email, username, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $points, $user_email, $username, $is_admin);
$stmt->fetch();
$stmt->close();

$quiz_query = "SELECT id, title, pin, is_published FROM quizzes WHERE user_id = ? ORDER BY id DESC";
$q_stmt = $conn->prepare($quiz_query);
$q_stmt->bind_param("i", $user_id);
$q_stmt->execute();
$quizzes_result = $q_stmt->get_result();

$ach_query = "SELECT a.name, a.description, a.icon, ua.awarded_at FROM user_achievements ua JOIN achievements a ON ua.achievement_id = a.id WHERE ua.user_id = ? ORDER BY ua.awarded_at DESC";
$ach_stmt = $conn->prepare($ach_query);
$ach_stmt->bind_param("i", $user_id);
$ach_stmt->execute();
$achievements_result = $ach_stmt->get_result();

$display_pic = $profile_pic ? $profile_pic : "https://cdn-icons-png.flaticon.com/512/149/149071.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/profile.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include "php/lang_ui.php"; ?>
    <?php include "Rapport/ui.php"; ?>

    <div class="grass-floor"></div>

    <div class="container">
        <div class="water-drop text-center">
            <a href="dashboard.php" class="back-link"><?php echo htmlspecialchars($lang['main_menu']); ?></a>
            <h1><?php echo htmlspecialchars($lang['profile']); ?> <span class="highlight-text"><?php echo htmlspecialchars($username); ?></span></h1>
            
            <img id="current-profile-pic" src="<?php echo $display_pic; ?>" alt="Profile" class="profile-main-pic">
            
            <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button onclick="shareContent('<?php echo htmlspecialchars(addslashes($username)); ?>\'s Profile', 'Check out my profile on the platform!', '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/user_profile.php?id=' . $user_id; ?>')" style="background: #6c5ce7; color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4); display: inline-block; border: none; cursor: pointer;">🔗 <?php echo htmlspecialchars($lang['share'] ?? 'Share Profile'); ?></button>
                <?php if ($is_admin == 1): ?>
                <a href="admin/panel.php" class="btn-admin" style="background: #ff4757; color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 15px rgba(255, 71, 87, 0.4); display: inline-block;">🛠️ Admin Panel</a>
                <?php endif; ?>
            </div>

            <div class="mt-20">
                <span class="points-label"><?php echo htmlspecialchars($lang['total_points']); ?></span>
                <h2 class="points-value"><?php echo ($points ? $points : 0); ?> 🏆</h2>
            </div>
        </div>

        <div class="water-drop">
            <h3 class="text-center"><?php echo htmlspecialchars($lang['game_statistics']); ?></h3>
            <div id="stats-container" class="stats-grid">
                <div class="stat-card"><?php echo htmlspecialchars($lang['quizzes_stat']); ?><br><span id="stat-quizzes" class="color-primary">0</span></div>
                <div class="stat-card"><?php echo htmlspecialchars($lang['correct_stat']); ?><br><span id="stat-correct" class="color-success">0</span></div>
                <div class="stat-card"><?php echo htmlspecialchars($lang['incorrect_stat']); ?><br><span id="stat-wrong" class="color-danger">0</span></div>
                <div class="stat-card"><?php echo htmlspecialchars($lang['rank_stat']); ?><br><span class="color-info"><?php echo htmlspecialchars($lang['expert']); ?></span></div>
            </div>
        </div>

        <div class="water-drop text-center">
            <a href="forum/index.php" style="text-decoration: none; color: #005580; font-weight: bold; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; gap: 10px;">📋 Forum</a>
        </div>

        <div class="water-drop">
            <h3 class="text-center">🏆 Achievements</h3>
            <?php if ($achievements_result->num_rows > 0): ?>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 15px;">
                    <?php while ($ach = $achievements_result->fetch_assoc()): ?>
                        <div style="background: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 12px; text-align: center; width: 120px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="<?php echo htmlspecialchars($ach['description']); ?>">
                            <div style="font-size: 40px; margin-bottom: 10px;"><?php echo htmlspecialchars($ach['icon']); ?></div>
                            <strong style="font-size: 14px; display: block; color: #fff;"><?php echo htmlspecialchars($ach['name']); ?></strong>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-center" style="color: #ccc;">You haven't earned any achievements yet. Keep playing!</p>
            <?php endif; ?>
        </div>

        <div class="water-drop">
            <div class="flex-between">
                <h3><?php echo htmlspecialchars($lang['my_quizzes']); ?></h3>
                <a href="quiz_maker/create.php" class="btn-add"><?php echo htmlspecialchars($lang['new_quiz']); ?></a>
            </div>
            
            <div class="table-responsive">
                <table class="quiz-table">
                    <thead>
                        <tr>
                            <th><?php echo htmlspecialchars($lang['title']); ?></th>
                            <th><?php echo htmlspecialchars($lang['pin']); ?></th>
                            <th><?php echo htmlspecialchars($lang['status']); ?></th>
                            <th class="text-right"><?php echo htmlspecialchars($lang['actions']); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($quizzes_result->num_rows > 0): ?>
                            <?php while ($q = $quizzes_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-bold"><?php echo htmlspecialchars($q['title']); ?></td>
                                    <td><code class="pin-code"><?php echo $q['pin']; ?></code></td>
                                    <td>
                                        <?php echo $q['is_published'] ? '<span class="status-published">' . htmlspecialchars($lang['published']) . '</span>' : '<span class="status-draft">' . htmlspecialchars($lang['draft']) . '</span>'; ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if (!$q['is_published']): ?>
                                            <a href="php/publish_quiz.php?id=<?php echo $q['id']; ?>" title="<?php echo htmlspecialchars($lang['publish_now']); ?>" class="action-link">🚀</a>
                                        <?php else: ?>
                                            <span class="action-link-disabled" title="<?php echo htmlspecialchars($lang['already_published']); ?>">🚀</span>
                                        <?php endif; ?>

                                        <a href="quiz_maker/edit.php?id=<?php echo $q['id']; ?>" class="action-link-edit" title="<?php echo htmlspecialchars($lang['edit']); ?>">✏️</a>
                                        <a href="php/delete_quiz.php?id=<?php echo $q['id']; ?>" onclick="return confirm('<?php echo htmlspecialchars($lang['delete_confirm']); ?>')" class="action-link-delete" title="<?php echo htmlspecialchars($lang['delete']); ?>">🗑️</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="empty-table"><?php echo htmlspecialchars($lang['no_quizzes']); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="water-drop">
            <h3><?php echo htmlspecialchars($lang['friends_list']); ?> (<span id="friendsCount">0</span>)</h3>
            <div id="myFriendsList"></div>
        </div>

        <div class="grid-2-col">
            <div class="water-drop">
                <h3><?php echo htmlspecialchars($lang['avatar']); ?></h3>
                <form id="profileUpdateForm">
                    <input type="text" name="profile_url" placeholder="<?php echo htmlspecialchars($lang['image_url']); ?>" class="input-field">
                    <button type="submit" class="btn-update"><?php echo htmlspecialchars($lang['update']); ?></button>
                </form>
            </div>

            <div class="water-drop">
                <h3><?php echo htmlspecialchars($lang['security']); ?></h3>
                <form id="emailUpdateForm">
                    <input type="email" name="new_email" value="<?php echo htmlspecialchars($user_email); ?>" class="input-field" required>
                    <input type="password" name="confirm_pass" placeholder="<?php echo htmlspecialchars($lang['password_confirm_placeholder']); ?>" class="input-field" required>
                    <button type="submit" class="btn-update btn-outline"><?php echo htmlspecialchars($lang['save_email']); ?></button>
                </form>
                <p id="emailUpdateMessage"></p>
            </div>
        </div>

        <div class="water-drop">
            <h3><?php echo htmlspecialchars($lang['change_password']); ?></h3>
            <form id="changePasswordForm">
                <input type="password" name="old_password" placeholder="<?php echo htmlspecialchars($lang['old_password']); ?>" required class="input-field">
                <input type="password" id="new_password" name="new_password" placeholder="<?php echo htmlspecialchars($lang['new_password']); ?>" required class="input-field">
                <div class="glass-tube" id="profile-password-strength-tube" style="margin-top: 5px; margin-bottom: 10px;">
                    <div class="wave-fluid" id="profile-password-strength-wave"></div>
                </div>

                <input type="password" id="confirm_password" placeholder="<?php echo htmlspecialchars($lang['confirm_new_password']); ?>" required class="input-field">
                <button type="submit" class="btn-update btn-dark"><?php echo htmlspecialchars($lang['update_password']); ?></button>
            </form>
            <p id="passwordMessage"></p>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", () => {

        fetch("php/get_user_stats.php").then(res => res.json()).then(data => {
            if (data.success) {
                document.getElementById("stat-quizzes").innerText = data.quizzes_played;
                document.getElementById("stat-correct").innerText = data.correct_answers;
                document.getElementById("stat-wrong").innerText = data.wrong_answers;
            }
        });

        loadFriends();

      
        document.getElementById('profileUpdateForm').onsubmit = function(e) {
            e.preventDefault();
            fetch('php/update_profile.php', { method: 'POST', body: new FormData(this) }).then(() => location.reload());
        };

       
        document.getElementById('emailUpdateForm').onsubmit = function(e) {
            e.preventDefault();
            fetch('php/update_email.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json()).then(data => {
                const m = document.getElementById('emailUpdateMessage');
                m.innerText = data.success ? "✅ Success!" : "❌ " + data.message;
                m.style.color = data.success ? "#28a745" : "#ff4444";
            });
        };

        const profilePasswordInput = document.getElementById('new_password');    // password strength bar in profile pg
        const profileGlassTube = document.getElementById('profile-password-strength-tube');
        const profileWaveFluid = document.getElementById('profile-password-strength-wave');

        if (profilePasswordInput) {
            profilePasswordInput.addEventListener('input', function() {
                const val = this.value;
                if (val.length === 0) {
                    profileGlassTube.style.display = 'none';
                    return;
                }
                profileGlassTube.style.display = 'block';

                let strength = 0;
                if (val.length >= 6) strength += 20; 
                if (val.length >= 10) strength += 20; 
                if (/[A-Z]/.test(val)) strength += 20; 
                if (/[0-9]/.test(val)) strength += 20; 
                if (/[^A-Za-z0-9]/.test(val)) strength += 20; 

                let color = '#ff4757';
                let shadow = 'rgba(255, 71, 87, 0.6)';
                
                if (strength > 40 && strength <= 60) {
                    color = '#ffa502';
                    shadow = 'rgba(255, 165, 2, 0.6)';
                } else if (strength > 60 && strength <= 80) {
                    color = '#2ed573';
                    shadow = 'rgba(46, 213, 115, 0.6)';
                } else if (strength > 80) {
                    color = '#1e90ff';
                    shadow = 'rgba(30, 144, 255, 0.6)';
                }

                profileWaveFluid.style.width = strength + '%';
                profileWaveFluid.style.backgroundColor = color;
                profileWaveFluid.style.boxShadow = `0 0 10px ${shadow}`;
            });
        }
    });

    function loadFriends() {
        fetch('php/manage_friends.php?action=get_friends').then(res => res.json()).then(data => {
            const list = document.getElementById('myFriendsList');
            document.getElementById('friendsCount').innerText = data.length;
            list.innerHTML = data.length ? "" : "<p><?php echo htmlspecialchars($lang['no_friends']); ?></p>";
            data.forEach(f => {
                const pic = f.profile_pic || "https://cdn-icons-png.flaticon.com/512/149/149071.png";
                list.innerHTML += `
                    <div class="friend-row">
                        <div style="display: flex; align-items: center;">
                            <img src="${pic}" class="friend-pic">
                            <span class="friend-name">${f.username}</span>
                        </div>
                        <button class="unfriend-btn" onclick="unfriend(${f.friendship_id})"><?php echo htmlspecialchars($lang['remove']); ?></button>
                    </div>`;
            });
        });
    }

    function unfriend(id) {
        if (confirm("<?php echo htmlspecialchars($lang['remove_confirm']); ?>")) {
            fetch('php/manage_friends.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify({ action: 'unfriend', friendship_id: id })
            }).then(() => loadFriends());
        }
    }
    </script>
    <script src="js/share.js"></script>
</body>
</html>