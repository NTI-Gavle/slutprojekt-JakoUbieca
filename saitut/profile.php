<?php
session_start();
include "php/lang_config.php";
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT profile_pic, points, email, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $points, $user_email, $username);
$stmt->fetch();
$stmt->close();

$quiz_query = "SELECT id, title, pin, is_published FROM quizzes WHERE user_id = ? ORDER BY id DESC";
$q_stmt = $conn->prepare($quiz_query);
$q_stmt->bind_param("i", $user_id);
$q_stmt->execute();
$quizzes_result = $q_stmt->get_result();

$display_pic = $profile_pic ? $profile_pic : "https://cdn-icons-png.flaticon.com/512/149/149071.png";
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

    <?php include "php/lang_ui.php"; ?>

    <div class="grass-floor"></div>

    <div class="container">
        <div class="water-drop text-center">
            <a href="dashboard.php" class="back-link"><?php echo htmlspecialchars($lang['main_menu']); ?></a>
            <h1><?php echo htmlspecialchars($lang['profile']); ?> <span class="highlight-text"><?php echo htmlspecialchars($username); ?></span></h1>
            
            <img id="current-profile-pic" src="<?php echo $display_pic; ?>" alt="Profile" class="profile-main-pic">
            
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
</body>
</html>