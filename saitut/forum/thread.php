<?php
session_start();
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title><?php echo htmlspecialchars($thread['title']); ?> - Forum</title>
    <meta property="og:title" content="<?php echo htmlspecialchars($thread['title']); ?>">
    <meta property="og:description" content="Discussion on Freaky Quiz Forum">
    <meta name="theme-color" content="#00aaff">
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>

    <div class="forum-container">
        <div class="breadcrumb">
            <a href="index.php">Forum</a>
            <span>›</span>
            <a href="category.php?id=<?php echo $thread['cat_id']; ?>"><?php echo htmlspecialchars($thread['cat_icon'] . ' ' . $thread['cat_name']); ?></a>
            <span>›</span>
            <span><?php echo htmlspecialchars($thread['title']); ?></span>
        </div>

        <div class="forum-header">
            <h1>
                <?php if ($thread['is_pinned']): ?><span title="Pinned">📌</span><?php endif; ?>
                <?php if ($thread['is_locked']): ?><span title="Locked">🔒</span><?php endif; ?>
                <?php echo htmlspecialchars($thread['title']); ?>
            </h1>
            <div class="forum-nav">
                <button class="btn-forum" onclick="shareContent('<?php echo htmlspecialchars(addslashes($thread['title'])); ?>', 'Check out this discussion on Freaky Quiz Forum!')">🔗 Share</button>
                <a href="category.php?id=<?php echo $thread['cat_id']; ?>" class="btn-forum">← Back</a>
            </div>
        </div>

        <div class="post-card" style="border-left: 4px solid #0984e3;">
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
                    <span style="opacity: 0.5; font-size: 0.8rem;">👁 <?php echo $thread['views']; ?> views</span>
                </div>
            </div>
            <div class="post-body"><?php echo nl2br(htmlspecialchars($thread['body'])); ?></div>
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

                <div class="post-body" id="post-body-<?php echo $post['id']; ?>"><?php echo nl2br(htmlspecialchars($post['body'])); ?></div>

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
                <h3 style="color: #fff; margin-top: 0;">Reply to this thread</h3>
                <textarea class="reply-textarea" id="reply-body" placeholder="Write your reply... You can mention users with @username"></textarea>
                <div style="margin-top: 12px; display: flex; gap: 10px;">
                    <button class="btn-forum btn-forum-primary" onclick="submitReply()">📤 Post Reply</button>
                </div>
            </div>
        <?php endif; ?>

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
    </script>
    <script src="../js/share.js"></script>
    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
