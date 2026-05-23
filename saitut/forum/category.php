<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id === 0) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

$cat_stmt = $conn->prepare("SELECT name, icon, description FROM forum_categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_stmt->bind_result($cat_name, $cat_icon, $cat_desc);
if (!$cat_stmt->fetch()) {
    die("Category not found.");
}
$cat_stmt->close();

$is_subscribed = false;
$sub_stmt = $conn->prepare("SELECT id FROM forum_subscriptions WHERE category_id = ? AND user_id = ?");
$sub_stmt->bind_param("ii", $category_id, $user_id);
$sub_stmt->execute();
if ($sub_stmt->fetch()) {
    $is_subscribed = true;
}
$sub_stmt->close();

$threads = [];                                             // pined thread first
$t_sql = "SELECT ft.*, u.username, u.profile_pic,
    (SELECT COUNT(*) FROM forum_posts fp WHERE fp.thread_id = ft.id) AS reply_count
    FROM forum_threads ft
    JOIN users u ON ft.user_id = u.id
    WHERE ft.category_id = ?
    ORDER BY ft.is_pinned DESC, ft.created_at DESC";
$t_stmt = $conn->prepare($t_sql);
$t_stmt->bind_param("i", $category_id);
$t_stmt->execute();
$t_res = $t_stmt->get_result();
while ($row = $t_res->fetch_assoc()) {
    $threads[] = $row;
}
$t_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title><?php echo htmlspecialchars($cat_name); ?> - Forum</title>
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
            <span><?php echo htmlspecialchars($cat_icon . ' ' . $cat_name); ?></span>
        </div>

        <div class="intercom-hero" style="padding: 40px;">
            <h1><?php echo htmlspecialchars($cat_icon . ' ' . $cat_name); ?></h1>
            <div class="forum-nav" style="justify-content: center; margin-top: 20px;">
                <button id="btn-subscribe" class="btn-forum" style="background: <?php echo $is_subscribed ? '#ff7675' : '#FF7B00'; ?>;" onclick="toggleSubscription(<?php echo $category_id; ?>)">
                    <?php echo $is_subscribed ? '🔔 ' . htmlspecialchars($lang['forum_unsubscribe']) : '🔕 ' . htmlspecialchars($lang['forum_subscribe']); ?>
                </button>
                <a href="create_thread.php?category_id=<?php echo $category_id; ?>" class="btn-forum btn-forum-primary"><?php echo htmlspecialchars($lang['forum_new_thread']); ?></a>
            </div>
        </div>

        <div class="intercom-layout">
            <div class="intercom-sidebar">
                <div class="intercom-sidebar-title"><?php echo htmlspecialchars($lang['forum_navigation']); ?></div>
                <a href="index.php" class="intercom-nav-link"><?php echo htmlspecialchars($lang['forum_all_categories']); ?></a>
                <a href="../dashboard.php" class="intercom-nav-link">🏠 <?php echo htmlspecialchars($lang['forum_dashboard']); ?></a>
            </div>

            <div class="intercom-main">
                <div class="glass-panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
                    <?php if (count($threads) > 0): ?>
                        <div class="thread-list">
                            <?php foreach ($threads as $t): ?>
                                <?php
                                    $card_class = 'thread-card';
                                    if ($t['is_pinned']) $card_class .= ' pinned';
                                    if ($t['is_locked']) $card_class .= ' locked';
                                ?>
                                <a href="thread.php?id=<?php echo $t['id']; ?>" class="<?php echo $card_class; ?>">
                                    <div class="thread-info">
                                        <div class="thread-title">
                                            <?php if ($t['is_pinned']): ?><span title="<?php echo htmlspecialchars($lang['forum_pinned']); ?>">📌</span><?php endif; ?>
                                            <?php if ($t['is_locked']): ?><span title="<?php echo htmlspecialchars($lang['forum_locked']); ?>">🔒</span><?php endif; ?>
                                            <?php echo htmlspecialchars($t['title']); ?>
                                        </div>
                                        <div class="thread-meta">
                                            <span><?php echo htmlspecialchars($lang['forum_by']); ?> <?php echo htmlspecialchars($t['username']); ?></span>
                                            <span><?php echo date('M j, Y', strtotime($t['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="thread-stats">
                                        <span>💬 <?php echo $t['reply_count']; ?></span>
                                        <span>👁 <?php echo $t['views']; ?></span>

                                        <?php if ($is_admin == 1): ?>
                                            </a>
                                            <div class="post-actions" style="margin-left: auto;">
                                                <button class="post-action-btn" onclick="event.preventDefault(); modAction('pin', <?php echo $t['id']; ?>, 'thread')" title="<?php echo $t['is_pinned'] ? 'Unpin' : 'Pin'; ?>">
                                                    📌
                                                </button>
                                                <button class="post-action-btn" onclick="event.preventDefault(); modAction('lock', <?php echo $t['id']; ?>, 'thread')" title="<?php echo $t['is_locked'] ? 'Unlock' : 'Lock'; ?>">
                                                    🔒
                                                </button>
                                                <button class="post-action-btn" onclick="event.preventDefault(); modAction('delete_thread', <?php echo $t['id']; ?>, 'thread')" title="Delete">
                                                    🗑️
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php if ($is_admin != 1): ?></a><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="glass-panel empty-state"><?php echo htmlspecialchars($lang['forum_no_threads']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <script>
        const categoryId = <?php echo $category_id; ?>;
        
        function toggleSubscription(catId) {
            fetch('php/subscribe.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ category_id: catId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || "Error toggling subscription");
                }
            });
        }
    </script>
    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
