<?php
session_start();
include "php/lang_config.php";
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT profile_pic, points, email, username, is_admin, bio, cover_url FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $points, $user_email, $username, $is_admin, $bio, $cover_url);
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
    <link rel="stylesheet" href="css/global_neon.css?v=<?php echo time(); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <style>
        ::placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
            opacity: 1; 
        }
        :-ms-input-placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        ::-ms-input-placeholder {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        
        #cropperModal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }
        .cropper-container-modal {
            background: #1A1511;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid rgba(255, 102, 0, 0.3);
            width: 90%;
            max-width: 800px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.9);
        }
        .img-container {
            width: 100%;
            height: 60vh; 
            margin-bottom: 25px;
            background-color: #000;
        }
        .img-container img {
            display: block;
            max-width: 100%;
        }
        #cropperModal .neon-btn {
            font-size: 1.1rem;
            padding: 12px 25px;
        }
    </style>
</head>
<body class="neon-theme">

    <?php include "php/lang_ui.php"; ?>
    <?php include "Rapport/ui.php"; ?>

    <div class="bg-particles">
        <div class="particle" style="width: 300px; height: 300px; top: 10%; left: 20%;"></div>
        <div class="particle" style="width: 200px; height: 200px; top: 60%; left: 80%; animation-delay: -5s;"></div>
        <div class="particle" style="width: 250px; height: 250px; top: 80%; left: 30%; animation-delay: -10s;"></div>
    </div>

    <div class="neon-container" style="--form-width: 1200px; width: 95vw; max-width: 1200px; height: 95vh; margin: auto;">
        <div class="neon-glass-box">
            <div class="container" style="background: transparent; box-shadow: none; max-width: 100%;">
            <a href="dashboard.php" class="back-link" style="display: block; margin-bottom: 15px;">← <?php echo htmlspecialchars($lang['main_menu']); ?></a>
            <div class="neon-profile-header">
            <div class="neon-profile-cover" style="background-image: url('<?php echo htmlspecialchars($cover_url ?? ""); ?>');"></div>
            
            <div class="neon-profile-nav">
                <img id="current-profile-pic" src="<?php echo $display_pic; ?>" alt="Profile" class="neon-profile-avatar">
                
                <div class="neon-profile-actions">
                    <button class="neon-btn" onclick="shareContent('<?php echo htmlspecialchars(addslashes($username)); ?>\'s Profile', 'Check out my profile on the platform!', '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/user_profile.php?id=' . $user_id; ?>')">🔗 <?php echo htmlspecialchars($lang['share'] ?? 'Share Profile'); ?></button>
                </div>
            </div>

            <div class="neon-profile-info">
                <h1><?php echo htmlspecialchars($username); ?></h1>
                <p style="margin: 0; color: var(--neon-orange);">🏆 <?php echo htmlspecialchars($lang['total_points']); ?>: <strong><?php echo ($points ? $points : 0); ?></strong></p>
                <?php if (!empty($bio)): ?>
                    <div class="bio-text">
                        "<?php echo nl2br(htmlspecialchars($bio)); ?>"
                    </div>
                <?php endif; ?>
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

        <div class="water-drop" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,102,0,0.2);">
            <h3 class="text-center"><?php echo htmlspecialchars($lang['profile_achievements']); ?></h3>
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
                <p class="text-center" style="color: #ccc;"><?php echo htmlspecialchars($lang['profile_no_achievements']); ?></p>
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
                <h3><?php echo htmlspecialchars($lang['avatar']); ?> & Profile Data</h3>
                <form id="profileUpdateForm" enctype="multipart/form-data">
                    <label style="display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--neon-orange);"><?php echo htmlspecialchars($lang['profile_picture_upload'] ?? 'Profile Picture (URL or File)'); ?></label>
                    <input type="text" name="profile_url" value="<?php echo htmlspecialchars($profile_pic ?? ''); ?>" placeholder="<?php echo htmlspecialchars($lang['image_url']); ?>" class="input-field" style="margin-bottom: 5px;">
                    <div style="margin-bottom: 15px;">
                        <input type="file" id="profile_pic_input" accept="image/*" style="display:none;">
                        <label for="profile_pic_input" class="input-field" style="display: flex; align-items: center; cursor: pointer; background: rgba(0,0,0,0.5); margin: 0; padding: 10px;">
                            <span style="background: #e0e0e0; color: #000; padding: 3px 8px; font-size: 0.85rem; border: 1px solid #767676; border-radius: 3px; margin-right: 10px;"><?php echo htmlspecialchars($lang['choose_file'] ?? 'Choose File'); ?></span>
                            <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;"><?php echo htmlspecialchars($lang['no_file_chosen'] ?? 'No file chosen'); ?></span>
                        </label>
                    </div>
                    <span id="profile_pic_status" style="display:none; color: #28a745; font-size: 0.8rem;"><?php echo htmlspecialchars($lang['avatar_ready'] ?? '✅ Avatar ready to save'); ?></span>
                    
                    <label style="display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--neon-orange);"><?php echo htmlspecialchars($lang['cover_photo_upload'] ?? 'Cover Photo (URL or File)'); ?></label>
                    <input type="text" name="cover_url" value="<?php echo htmlspecialchars($cover_url ?? ''); ?>" placeholder="<?php echo htmlspecialchars($lang['image_url']); ?>" class="input-field" style="margin-bottom: 5px;">
                    <div style="margin-bottom: 15px;">
                        <input type="file" id="cover_pic_input" accept="image/*" style="display:none;">
                        <label for="cover_pic_input" class="input-field" style="display: flex; align-items: center; cursor: pointer; background: rgba(0,0,0,0.5); margin: 0; padding: 10px;">
                            <span style="background: #e0e0e0; color: #000; padding: 3px 8px; font-size: 0.85rem; border: 1px solid #767676; border-radius: 3px; margin-right: 10px;"><?php echo htmlspecialchars($lang['choose_file'] ?? 'Choose File'); ?></span>
                            <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;"><?php echo htmlspecialchars($lang['no_file_chosen'] ?? 'No file chosen'); ?></span>
                        </label>
                    </div>
                    <span id="cover_pic_status" style="display:none; color: #28a745; font-size: 0.8rem;"><?php echo htmlspecialchars($lang['cover_ready'] ?? '✅ Cover ready to save'); ?></span>
                    
                    <label style="display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--neon-orange);"><?php echo htmlspecialchars($lang['bio_label'] ?? 'Bio'); ?></label>
                    <textarea name="bio" id="bioInput" placeholder="<?php echo htmlspecialchars($lang['bio_placeholder'] ?? 'Write a short bio (max 100 words)...'); ?>" class="input-field" rows="4" style="margin-bottom: 10px; resize: vertical;"><?php echo htmlspecialchars($bio ?? ''); ?></textarea>
                    <p id="bioWordCount" style="font-size: 0.8rem; opacity: 0.7; text-align: right; margin-top: -5px; margin-bottom: 10px;">0 / 100 words</p>
                    
                    <button type="submit" class="btn-update neon-btn" style="width: 100%;"><?php echo htmlspecialchars($lang['update']); ?></button>
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

        <div class="water-drop">
            <h3 style="color: #ff4757;">Data & Privacy</h3>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 15px;">Manage your GDPR rights and personal data.</p>
            <div style="display: flex; gap: 10px; flex-direction: column;">
                <a href="gdpr/export_data.php" class="btn-update btn-outline" style="text-align: center; color: #2ed573; border-color: #2ed573; text-decoration: none;">Download My Data</a>
                <button type="button" onclick="deleteAccount()" class="btn-update btn-outline" style="color: #ff4757; border-color: #ff4757;">Delete My Account</button>
            </div>
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

        let cropper = null;
        let currentCropType = null; 
        let croppedAvatarBlob = null;
        let croppedCoverBlob = null;

        const cropperModal = document.getElementById('cropperModal');
        const imageToCrop = document.getElementById('imageToCrop');
        const profileInput = document.getElementById('profile_pic_input');
        const coverInput = document.getElementById('cover_pic_input');

        function openCropper(file, type) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imageToCrop.src = e.target.result;
                cropperModal.style.display = 'flex';
                currentCropType = type;
                
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(imageToCrop, {
                    aspectRatio: type === 'avatar' ? 1 / 1 : 1200 / 300,
                    viewMode: 2,
                    background: false,
                });
            };
            reader.readAsDataURL(file);
        }

        profileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                openCropper(e.target.files[0], 'avatar');
                this.value = ''; 
            }
        });

        coverInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                openCropper(e.target.files[0], 'cover');
                this.value = ''; 
            }
        });

        document.getElementById('btnCancelCrop').addEventListener('click', () => {
            cropperModal.style.display = 'none';
            if (cropper) cropper.destroy();
        });

        document.getElementById('btnSaveCrop').addEventListener('click', () => {
            if (!cropper) return;
            
            cropper.getCroppedCanvas().toBlob((blob) => {
                if (currentCropType === 'avatar') {
                    croppedAvatarBlob = blob;
                    document.getElementById('profile_pic_status').style.display = 'block';
                } else {
                    croppedCoverBlob = blob;
                    document.getElementById('cover_pic_status').style.display = 'block';
                }
                cropperModal.style.display = 'none';
                cropper.destroy();
            }, 'image/jpeg', 0.9);
        });

        document.getElementById('profileUpdateForm').onsubmit = function(e) {
            e.preventDefault();
            const bioWords = document.getElementById('bioInput').value.trim().split(/\s+/).filter(w => w.length > 0).length;
            if(bioWords > 100) { alert("Bio must be 100 words or less."); return; }
            
            const formData = new FormData(this);
            
            if (croppedAvatarBlob) {
                formData.append('profile_pic', croppedAvatarBlob, 'avatar.jpg');
            }
            if (croppedCoverBlob) {
                formData.append('cover_pic', croppedCoverBlob, 'cover.jpg');
            }

            fetch('php/update_profile.php', { method: 'POST', body: formData }).then(() => location.reload());
        };

        const bioInput = document.getElementById('bioInput');
        const bioCount = document.getElementById('bioWordCount');
        if (bioInput) {

            const initialWords = bioInput.value.trim().split(/\s+/).filter(w => w.length > 0).length;
            bioCount.innerText = initialWords + " / 100 words";
            
            bioInput.addEventListener('input', function() {
                const words = this.value.trim().split(/\s+/).filter(w => w.length > 0).length;
                bioCount.innerText = words + " / 100 words";
                if(words > 100) {
                    bioCount.style.color = '#ff4757';
                } else {
                    bioCount.style.color = 'inherit';
                }
            });
        }

       
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
        
        function deleteAccount() {
            if (confirm("Are you absolutely sure you want to delete your account? This action cannot be undone and all your data will be permanently erased.")) {
                fetch('gdpr/delete_account.php', { method: 'POST' })
                .then(r => r.text())
                .then(res => {
                    if (res.trim() === 'success') {
                        alert("Account deleted. We're sorry to see you go!");
                        window.location.href = 'index.php';
                    } else {
                        alert("Error deleting account. Please try again.");
                    }
                });
            }
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
    window.onload = loadFriends;
    </script>
    <script src="js/share.js"></script>
    <?php include_once __DIR__ . "/Rapport/ui.php"; ?>
        </div>
    </div>

    <div id="cropperModal">
        <div class="cropper-container-modal">
            <h3 style="color: white; margin-top: 0;"><?php echo htmlspecialchars($lang['crop_image'] ?? 'Crop Image'); ?></h3>
            <div class="img-container">
                <img id="imageToCrop" src="">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="neon-btn neon-btn-outline" id="btnCancelCrop"><?php echo htmlspecialchars($lang['cancel'] ?? 'Cancel'); ?></button>
                <button type="button" class="neon-btn" id="btnSaveCrop"><?php echo htmlspecialchars($lang['crop_select'] ?? 'Crop & Select'); ?></button>
            </div>
        </div>
    </div>
    <button id="dyslexia-toggle-btn" onclick="toggleDyslexia()" style="position:fixed;bottom:20px;left:20px;z-index:9999;padding:12px 24px;color:#fff;border:none;border-radius:25px;font-weight:bold;cursor:pointer;box-shadow:0 4px 15px rgba(0,0,0,0.5); font-family: 'Lexend', 'Comic Sans MS', sans-serif; transition: 0.3s;"></button>
</body>
</html>