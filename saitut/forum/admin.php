<?php
session_start();
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

if ($is_admin != 1) {
    die("Access denied.");
}
                                  // request for category
$pending_requests = [];
$pr_sql = "SELECT fcr.*, u.username FROM forum_category_requests fcr JOIN users u ON fcr.user_id = u.id WHERE fcr.status = 'pending' ORDER BY fcr.created_at DESC";
$pr_res = $conn->query($pr_sql);
while ($row = $pr_res->fetch_assoc()) {
    $pending_requests[] = $row;
}

$reports = [];                                                                  //reports of posts 
$rp_sql = "SELECT fr.*, u.username AS reporter, fp.body AS post_body, fp.thread_id,
    ft.title AS thread_title
    FROM forum_reports fr
    JOIN users u ON fr.reporter_id = u.id
    JOIN forum_posts fp ON fr.post_id = fp.id
    JOIN forum_threads ft ON fp.thread_id = ft.id
    WHERE fr.status = 'pending'
    ORDER BY fr.created_at DESC";
$rp_res = $conn->query($rp_sql);
while ($row = $rp_res->fetch_assoc()) {
    $reports[] = $row;
}

$categories = [];
$cat_res = $conn->query("SELECT * FROM forum_categories ORDER BY sort_order ASC");
while ($row = $cat_res->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Forum Admin Panel</title>
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>

    <div class="forum-container">

        <div class="breadcrumb">
            <a href="index.php">Forum</a>
            <span>›</span>
            <span>⚙️ Admin Panel</span>
        </div>

        <div class="forum-header">
            <h1>⚙️ Forum Admin</h1>
            <a href="index.php" class="btn-forum">← Back to Forum</a>
        </div>

        <div class="admin-tabs">
            <button class="admin-tab active" onclick="showAdminTab('requests', this)">📁 Category Requests (<?php echo count($pending_requests); ?>)</button>
            <button class="admin-tab" onclick="showAdminTab('reports', this)">🚩 Reports (<?php echo count($reports); ?>)</button>
            <button class="admin-tab" onclick="showAdminTab('categories', this)">🗂️ Manage Categories</button>
            <button class="admin-tab" onclick="showAdminTab('create-cat', this)">➕ Create Category</button>
        </div>

        <div class="admin-section active" id="tab-requests">
            <div class="glass-panel">
                <h3 style="color: #fff; margin-top: 0;">Pending Category Requests</h3>
                <?php if (count($pending_requests) > 0): ?>
                    <?php foreach ($pending_requests as $req): ?>
                        <div class="admin-item" id="request-<?php echo $req['id']; ?>">
                            <div class="admin-item-header">
                                <strong><?php echo htmlspecialchars($req['icon'] . ' ' . $req['name']); ?></strong>
                                <span style="font-size: 0.8rem; opacity: 0.6;">by <?php echo htmlspecialchars($req['username']); ?> • <?php echo date('M j, Y', strtotime($req['created_at'])); ?></span>
                            </div>
                            <div class="admin-item-body"><?php echo htmlspecialchars($req['description']); ?></div>
                            <div class="admin-item-actions">
                                <input type="text" class="admin-note-input" id="note-<?php echo $req['id']; ?>" placeholder="Admin note (optional)">
                                <button class="btn-forum btn-forum-primary btn-forum-small" onclick="adminAction('approve_category', <?php echo $req['id']; ?>)">✅ Approve</button>
                                <button class="btn-forum btn-forum-danger btn-forum-small" onclick="adminAction('reject_category', <?php echo $req['id']; ?>)">❌ Reject</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No pending requests. 🎉</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-section" id="tab-reports">
            <div class="glass-panel">
                <h3 style="color: #fff; margin-top: 0;">Pending Reports</h3>
                <?php if (count($reports) > 0): ?>
                    <?php foreach ($reports as $rp): ?>
                        <div class="admin-item" id="report-<?php echo $rp['id']; ?>">
                            <div class="admin-item-header">
                                <strong>Report #<?php echo $rp['id']; ?></strong>
                                <span style="font-size: 0.8rem; opacity: 0.6;">by <?php echo htmlspecialchars($rp['reporter']); ?> • <?php echo date('M j, Y', strtotime($rp['created_at'])); ?></span>
                            </div>
                            <div class="admin-item-body">
                                <strong>Reason:</strong> <?php echo htmlspecialchars($rp['reason']); ?><br>
                                <strong>Post content:</strong> <?php echo htmlspecialchars(mb_substr($rp['post_body'], 0, 200)); ?>...
                                <br><a href="thread.php?id=<?php echo $rp['thread_id']; ?>#post-<?php echo $rp['post_id']; ?>" style="color: #74b9ff;">→ View in thread: <?php echo htmlspecialchars($rp['thread_title']); ?></a>
                            </div>
                            <div class="admin-item-actions">
                                <button class="btn-forum btn-forum-primary btn-forum-small" onclick="adminAction('resolve_report', <?php echo $rp['id']; ?>)">✅ Resolve</button>
                                <button class="btn-forum btn-forum-danger btn-forum-small" onclick="adminAction('delete_reported_post', <?php echo $rp['id']; ?>, <?php echo $rp['post_id']; ?>)">🗑️ Delete Post</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">No pending reports. 🎉</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="admin-section" id="tab-categories">
            <div class="glass-panel">
                <h3 style="color: #fff; margin-top: 0;">Manage Categories</h3>
                <?php foreach ($categories as $cat): ?>
                    <div class="admin-item" id="cat-<?php echo $cat['id']; ?>">
                        <div class="admin-item-header">
                            <strong><?php echo htmlspecialchars($cat['icon'] . ' ' . $cat['name']); ?></strong>
                            <span class="badge badge-approved">Order: <?php echo $cat['sort_order']; ?></span>
                        </div>
                        <div class="admin-item-body"><?php echo htmlspecialchars($cat['description']); ?></div>
                        <div class="admin-item-actions">
                            <input type="text" class="admin-note-input" id="cat-name-<?php echo $cat['id']; ?>" value="<?php echo htmlspecialchars($cat['name']); ?>" placeholder="Name">
                            <input type="text" class="admin-note-input" style="max-width: 60px;" id="cat-icon-<?php echo $cat['id']; ?>" value="<?php echo htmlspecialchars($cat['icon']); ?>" placeholder="Icon">
                            <input type="number" class="admin-note-input" style="max-width: 70px;" id="cat-order-<?php echo $cat['id']; ?>" value="<?php echo $cat['sort_order']; ?>" placeholder="Order">
                            <button class="btn-forum btn-forum-primary btn-forum-small" onclick="editCategory(<?php echo $cat['id']; ?>)">💾 Save</button>
                            <button class="btn-forum btn-forum-danger btn-forum-small" onclick="deleteCategory(<?php echo $cat['id']; ?>)">🗑️ Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
                                                                                         
        <div class="admin-section" id="tab-create-cat">
            <div class="glass-panel">
                <h3 style="color: #fff; margin-top: 0;">➕ Create Category Directly</h3>
                <form id="directCreateCatForm">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-input" required maxlength="100" placeholder="Category name">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="reply-textarea" style="min-height: 80px;" required placeholder="Category description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon (emoji)</label>
                        <input type="text" name="icon" class="form-input" value="📁" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-input" value="0">
                    </div>
                    <button type="submit" class="btn-forum btn-forum-primary">📤 Create</button>
                </form>
                <p id="create-cat-msg" style="margin-top: 15px; font-weight: bold; color: #fff;"></p>
            </div>
        </div>

    </div>

    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
    <script>
        document.getElementById('directCreateCatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = { action: 'create_category' };
            formData.forEach((val, key) => data[key] = val);

            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                const msg = document.getElementById('create-cat-msg');
                if (result.success) {
                    msg.style.color = '#2ed573';
                    msg.innerText = '✅ Category created!';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    msg.style.color = '#ff4757';
                    msg.innerText = '❌ ' + result.message;
                }
            });
        });
    </script>
</body>
</html>
