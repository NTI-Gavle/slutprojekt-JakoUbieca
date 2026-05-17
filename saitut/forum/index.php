<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

$categories = [];      // load categories with thread and post count
$cat_sql = "SELECT fc.*, 
    (SELECT COUNT(*) FROM forum_threads ft WHERE ft.category_id = fc.id) AS thread_count,
    (SELECT COUNT(*) FROM forum_posts fp JOIN forum_threads ft2 ON fp.thread_id = ft2.id WHERE ft2.category_id = fc.id) AS post_count
    FROM forum_categories fc ORDER BY fc.sort_order ASC";
$cat_res = $conn->query($cat_sql);
while ($row = $cat_res->fetch_assoc()) {
    $categories[] = $row;
}

$notif_stmt = $conn->prepare("SELECT COUNT(*) FROM forum_notifications WHERE user_id = ? AND is_read = 0");  //notf
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_stmt->bind_result($unread_count);
$notif_stmt->fetch();
$notif_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Forum - Freaky Quiz</title>
    <meta property="og:title" content="Freaky Quiz Forum">
    <meta property="og:description" content="Join the discussion on Freaky Quiz Forum!">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#00aaff">
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>

    <div class="forum-container">

        <div class="forum-header">
            <h1>📋 Forum</h1>
            <div class="forum-nav">
                <a href="../dashboard.php" class="btn-forum">← Dashboard</a>
                <a href="request_category.php" class="btn-forum">📁 Request Category</a>
                <?php if ($is_admin == 1): ?>
                    <a href="admin.php" class="btn-forum btn-forum-danger">⚙️ Admin Panel</a>
                <?php endif; ?>
                
                <div class="notif-wrapper">
                    <button class="btn-forum" id="notif-btn" onclick="toggleNotifications()">
                        🔔 Notifications
                    </button>
                    <?php if ($unread_count > 0): ?>
                        <div class="notif-badge" id="notif-badge"><?php echo $unread_count; ?></div>
                    <?php endif; ?>
                    <div class="notif-dropdown" id="notif-dropdown">
                        <div class="notif-empty">Loading...</div>
                    </div>
                </div>
            </div>
        </div>

        <input type="text" class="search-bar" id="forum-search" placeholder="🔍 Search threads and posts..." onkeyup="forumSearch(this.value)">
        <div class="search-results" id="search-results" style="display: none;"></div>

        <div class="glass-panel">
            <h2 style="color: #fff; margin-top: 0; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Categories</h2>
            
            <?php if (count($categories) > 0): ?>
                <div class="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <a href="category.php?id=<?php echo $cat['id']; ?>" class="category-card">
                            <div class="category-icon"><?php echo htmlspecialchars($cat['icon']); ?></div>
                            <div class="category-info">
                                <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                                <p><?php echo htmlspecialchars($cat['description']); ?></p>
                            </div>
                            <div class="category-stats">
                                <span>📝 <?php echo $cat['thread_count']; ?> threads</span>
                                <span>💬 <?php echo $cat['post_count']; ?> posts</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">No categories yet. Be the first to request one</div>
            <?php endif; ?>
        </div>

    </div>

    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
</body>
</html>
