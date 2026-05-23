<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($thread_id === 0) {
    header("Location: index.php");
    exit;
}

$adm_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$adm_stmt->bind_param("i", $user_id);
$adm_stmt->execute();
$adm_stmt->bind_result($is_admin);
$adm_stmt->fetch();
$adm_stmt->close();

$conn->query("UPDATE forum_threads SET views = views + 1 WHERE id = $thread_id");        //views counter

$t_stmt = $conn->prepare("SELECT ft.*, u.username, u.profile_pic, fc.name AS cat_name, fc.icon AS cat_icon, fc.id AS cat_id  
    FROM forum_threads ft
    JOIN users u ON ft.user_id = u.id
    JOIN forum_categories fc ON ft.category_id = fc.id
    WHERE ft.id = ?");
$t_stmt->bind_param("i", $thread_id);
$t_stmt->execute();
$thread = $t_stmt->get_result()->fetch_assoc();
$t_stmt->close();

if (!$thread) {
    die("Thread not found.");
}

$default_pic = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

$posts = [];                                                                                         // posts table
$p_sql = "SELECT fp.*, u.username, u.profile_pic,
    COALESCE((SELECT SUM(vote) FROM forum_votes fv WHERE fv.post_id = fp.id), 0) AS score,
    (SELECT vote FROM forum_votes fv2 WHERE fv2.post_id = fp.id AND fv2.user_id = ?) AS my_vote
    FROM forum_posts fp
    JOIN users u ON fp.user_id = u.id
    WHERE fp.thread_id = ?
    ORDER BY fp.is_correct DESC, fp.created_at ASC";
$p_stmt = $conn->prepare($p_sql);
$p_stmt->bind_param("ii", $user_id, $thread_id);
$p_stmt->execute();
$p_res = $p_stmt->get_result();
while ($row = $p_res->fetch_assoc()) {
    $posts[] = $row;
}
$p_stmt->close();

$is_thread_author = ($thread['user_id'] == $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title><?php echo htmlspecialchars($thread['title']); ?> - Forum</title>
    <meta name="theme-color" content="#FF7B00">
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/intercom.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>

    <div class="forum-container">
        <div class="breadcrumb" style="margin-bottom: 20px;">
            <a href="index.php"><?php echo htmlspecialchars($lang['forum_community']); ?></a>
            <span>›</span>
            <a href="category.php?id=<?php echo $thread['cat_id']; ?>"><?php echo htmlspecialchars($thread['cat_name']); ?></a>
            <span>›</span>
            <span style="opacity: 0.7;"><?php echo htmlspecialchars($thread['title']); ?></span>
        </div>

        <div class="intercom-hero" style="padding: 40px; text-align: left;">
            <h1 style="font-size: 2rem; margin-bottom: 10px;">
                <?php if ($thread['is_pinned']): ?><span title="Pinned">📌</span><?php endif; ?>
                <?php if ($thread['is_locked']): ?><span title="Locked">🔒</span><?php endif; ?>
                <?php echo htmlspecialchars($thread['title']); ?>
            </h1>
            <p style="margin: 0;">Posted in <a href="category.php?id=<?php echo $thread['cat_id']; ?>" style="color: var(--primary-color); font-weight: bold;"><?php echo htmlspecialchars($thread['cat_name']); ?></a></p>
        </div>

        <div class="intercom-layout">
            <div class="intercom-sidebar">
                <div class="intercom-sidebar-title"><?php echo htmlspecialchars($lang['forum_navigation']); ?></div>
                <a href="category.php?id=<?php echo $thread['cat_id']; ?>" class="intercom-nav-link">← <?php echo htmlspecialchars($thread['cat_name']); ?></a>
                <a href="index.php" class="intercom-nav-link">🏠 <?php echo htmlspecialchars($lang['forum_all_categories']); ?></a>
                
                <?php if ($is_admin == 1 || $is_thread_author): ?>
                    <div class="intercom-sidebar-title" style="margin-top: 30px;">Manage Thread</div>
                    <?php if ($is_admin == 1): ?>
                        <a href="#" class="intercom-nav-link" onclick="event.preventDefault(); modAction('pin', <?php echo $thread_id; ?>, 'thread')"><?php echo $thread['is_pinned'] ? 'Unpin Thread' : '📌 Pin Thread'; ?></a>
                        <a href="#" class="intercom-nav-link" onclick="event.preventDefault(); modAction('lock', <?php echo $thread_id; ?>, 'thread')"><?php echo $thread['is_locked'] ? 'Unlock Thread' : '🔒 Lock Thread'; ?></a>
                    <?php endif; ?>
                    <a href="#" class="intercom-nav-link" style="color: var(--danger);" onclick="event.preventDefault(); modAction('delete_thread', <?php echo $thread_id; ?>, 'thread')">🗑️ Delete Thread</a>
                <?php endif; ?>
                
                <div class="intercom-sidebar-title" style="margin-top: 30px;">Share</div>
                <button class="btn-forum" style="width: 100%; justify-content: center;" onclick="shareContent('<?php echo htmlspecialchars(addslashes($thread['title'])); ?>', 'Check out this discussion on Freaky Quiz Forum!')">🔗 Share Link</button>
            </div>

            <div class="intercom-main">
                <div class="glass-panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
                    
                    <div class="post-card" style="border: 2px solid var(--primary-color);">
                        <div class="post-header">
                            <div class="post-author">
                                <img src="<?php echo htmlspecialchars($thread['profile_pic'] ?: $default_pic); ?>" class="post-avatar" alt="avatar">
                                <div class="post-author-info">
                                    <span class="post-author-name">
                                        <a href="../user_profile.php?id=<?php echo $thread['user_id']; ?>"><?php echo htmlspecialchars($thread['username']); ?></a>
                                    </span>
                                    <span class="post-date"><?php echo date('M j, Y \a\t H:i', strtotime($thread['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="post-actions">
                                <span style="opacity: 0.5; font-size: 0.8rem;">👁 <?php echo $thread['views']; ?> <?php echo htmlspecialchars($lang['forum_views']); ?></span>
                            </div>
                        </div>
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($thread['body'])); ?>
                            <?php if (!empty($thread['image_url'])): ?>
                                <br><img src="../<?php echo htmlspecialchars($thread['image_url']); ?>" alt="Thread Image" style="max-width: 100%; border-radius: 10px; margin-top: 15px;">
                            <?php endif; ?>
                        </div>
                    </div>

        <?php if (count($posts) > 0): ?>
            <h3 style="color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-bottom: 15px;">💬 Replies (<?php echo count($posts); ?>)</h3>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <div class="post-card <?php echo $post['is_correct'] ? 'correct-answer' : ''; ?>" id="post-<?php echo $post['id']; ?>">
                
                <?php if ($post['is_correct']): ?>
                    <div class="correct-mark">✅ Correct Answer</div>
                <?php endif; ?>

                <div class="post-header">
                    <div class="post-author">
                        <img src="<?php echo htmlspecialchars($post['profile_pic'] ?: $default_pic); ?>" class="post-avatar" alt="avatar">
                        <div class="post-author-info">
                            <span class="post-author-name">
                                <a href="../user_profile.php?id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a>
                            </span>
                            <span class="post-date">
                                <?php echo date('M j, Y \a\t H:i', strtotime($post['created_at'])); ?>
                                <?php if ($post['edited_at']): ?>
                                    <em>(edited)</em>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="post-actions">
                        <?php if (($is_thread_author || $is_admin == 1) && !$post['is_correct']): ?>
                            <button class="post-action-btn" onclick="modAction('mark_correct', <?php echo $post['id']; ?>, 'post')" title="Mark as correct">✅</button>
                        <?php endif; ?>
                        <button class="post-action-btn" onclick="openReportModal(<?php echo $post['id']; ?>)" title="Report">🚩</button>

                        <?php if ($is_admin == 1): ?>
                            <button class="post-action-btn" onclick="editPost(<?php echo $post['id']; ?>)" title="Edit">✏️</button>                    
                            <button class="post-action-btn" onclick="modAction('delete_post', <?php echo $post['id']; ?>, 'post')" title="Delete">🗑️</button>
                        <?php endif; ?>

                        <?php if ($post['user_id'] == $user_id && $is_admin != 1): ?>
                            <button class="post-action-btn" onclick="modAction('delete_post', <?php echo $post['id']; ?>, 'post')" title="Delete">🗑️</button>        
                        <?php endif; ?>
                    </div>
                </div>

                <div class="post-body" id="post-body-<?php echo $post['id']; ?>">
                    <?php echo nl2br(htmlspecialchars($post['body'])); ?>
                    <?php if (!empty($post['image_url'])): ?>
                        <br><img src="../<?php echo htmlspecialchars($post['image_url']); ?>" alt="Reply Image" style="max-width: 100%; border-radius: 10px; margin-top: 15px;">
                    <?php endif; ?>
                </div>

                <div class="vote-section">
                    <button class="vote-btn <?php echo ($post['my_vote'] == 1) ? 'active-up' : ''; ?>" onclick="vote(<?php echo $post['id']; ?>, 1)" title="Upvote">▲</button>
                    <span class="vote-score" id="score-<?php echo $post['id']; ?>"><?php echo $post['score']; ?></span>
                    <button class="vote-btn <?php echo ($post['my_vote'] == -1) ? 'active-down' : ''; ?>" onclick="vote(<?php echo $post['id']; ?>, -1)" title="Downvote">▼</button>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($thread['is_locked']): ?>
            <div class="glass-panel locked-msg">
                🔒 This thread is locked. No new replies can be posted.
            </div>
        <?php else: ?>
            <div class="glass-panel reply-form">
                <h3 style="color: #fff; margin-top: 0;"><?php echo htmlspecialchars($lang['forum_reply']); ?></h3>
                <textarea class="reply-textarea" id="reply-body" placeholder="Write your reply... You can mention users with @username"></textarea>
                
                <input type="file" id="forumImageInput" accept="image/*" style="display:none;" onchange="uploadForumImage()">
                
                <div style="display: flex; gap: 10px; margin-top: 10px; margin-bottom: 10px;">
                    <button class="btn-forum" id="forumImageBtn" style="background: #fdcb6e; color: #2d3436;" onclick="document.getElementById('forumImageInput').click()">📎 Attach Image</button>
                    <button class="btn-forum" style="background: #e84393; color: white;" onclick="document.getElementById('forum-emoji-picker-container').style.display = document.getElementById('forum-emoji-picker-container').style.display === 'none' ? 'block' : 'none'">😊 Emojis</button>
                </div>

                <div id="forum-emoji-picker-container" style="display: none; margin-bottom: 15px;">
                    <emoji-picker class="dark"></emoji-picker>
                </div>

                <div style="margin-top: 12px; display: flex; gap: 10px;">
                    <button class="btn-forum btn-forum-primary" onclick="submitReply()">📤 <?php echo htmlspecialchars($lang['forum_post_reply']); ?></button>
                </div>
            </div>
        <?php endif; ?>

            </div> 
        </div> 

    </div>
    
    <div class="modal-overlay" id="reportModal">
        <div class="modal-content">
            <h3>🚩 Report Post</h3>
            <p>Why are you reporting this post?</p>
            <textarea id="report-reason" placeholder="Describe the issue..."></textarea>
            <input type="hidden" id="report-post-id">
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="closeReportModal()">Cancel</button>
                <button class="modal-btn modal-btn-danger" onclick="submitReport()">Report</button>
            </div>
        </div>
    </div>

    <script>
        const threadId = <?php echo $thread_id; ?>;
        const threadAuthorId = <?php echo $thread['user_id']; ?>;
        const currentUserId = <?php echo $user_id; ?>;
        
        document.addEventListener('DOMContentLoaded', () => {
            const picker = document.querySelector('emoji-picker');
            if (picker) {
                picker.addEventListener('emoji-click', event => {
                    const input = document.getElementById('reply-body');
                    input.value += event.detail.unicode;
                    input.focus();
                });
            }
        });
    </script>
    <script src="../js/share.js"></script>
    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>
</body>
</html>
