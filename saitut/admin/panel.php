<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");     //admin check
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if ($is_admin != 1) {
    die("Access Denied! You do not have permission to view this page.");
}

$users = [];
$res = $conn->query("SELECT id, username, email, points, is_admin FROM users ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
    $users[] = $row;
}                                             //get data from data base users.id, username, email, points, quizzes.id, title, is_published, reports.id, title, description, status, created_at,

$quizzes = [];
$res_q = $conn->query("SELECT q.id, q.title, q.is_published, u.username FROM quizzes q JOIN users u ON q.user_id = u.id ORDER BY q.id DESC");
while ($row = $res_q->fetch_assoc()) {
    $quizzes[] = $row;
}                                            

$reports = [];
$res_r = $conn->query("SELECT r.id, r.title, r.description, r.status, r.created_at, u.username FROM reports r JOIN users u ON r.user_id = u.id ORDER BY r.status ASC, r.id DESC");
while ($row = $res_r->fetch_assoc()) {
    $reports[] = $row;
} 
try {
    $res_l = $conn->query("SELECT * FROM system_logs ORDER BY id DESC LIMIT 100");
    if ($res_l) {
        while ($row = $res_l->fetch_assoc()) {
            $logs[] = $row;
        }
    }
} catch (Exception $e) {
}

$forum_pending_requests = [];
$pr_sql = "SELECT fcr.*, u.username FROM forum_category_requests fcr JOIN users u ON fcr.user_id = u.id WHERE fcr.status = 'pending' ORDER BY fcr.created_at DESC";  
$pr_res = $conn->query($pr_sql);
while ($row = $pr_res->fetch_assoc()) {          
    $forum_pending_requests[] = $row;
}
                                           // Fetching data for forum management
$forum_reports = [];
$rp_sql = "SELECT fr.*, u.username AS reporter, fp.body AS post_body, fp.thread_id,
    ft.title AS thread_title
    FROM forum_reports fr
    JOIN users u ON fr.user_id = u.id
    JOIN forum_posts fp ON fr.post_id = fp.id                                
    JOIN forum_threads ft ON fp.thread_id = ft.id
    WHERE fr.status = 'pending'
    ORDER BY fr.created_at DESC";
$rp_res = $conn->query($rp_sql);
if ($rp_res) {
    while ($row = $rp_res->fetch_assoc()) {
        $forum_reports[] = $row;
    }
}

$forum_categories = [];
$cat_res = $conn->query("SELECT * FROM forum_categories ORDER BY sort_order ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $forum_categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/global_neon.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="neon-theme">
    <?php include "../Rapport/ui.php"; ?>

    <div class="bg-particles">
        <div class="particle" style="top: 20%; left: 10%; animation-duration: 25s;"></div>
        <div class="particle" style="top: 70%; left: 80%; animation-duration: 30s;"></div>
        <div class="particle" style="top: 40%; left: 60%; animation-duration: 22s;"></div>
    </div>

    <div class="neon-container" style="--form-width: 1400px; width: 95vw; max-width: 1400px; height: 95vh; margin: auto;">
        <div class="neon-glass-box" style="padding: 30px; overflow-y: auto;">
            <div class="admin-header" style="text-align: center; margin-bottom: 30px;">
                <a href="../profile.php" style="color: var(--neon-orange); text-decoration: none; display: inline-block; margin-bottom: 20px; font-weight: bold; text-shadow: 0 0 5px rgba(255,102,0,0.5);">← Back to Profile</a>
                <h1 style="color: white; text-shadow: 0 0 10px rgba(255,255,255,0.3); margin-bottom: 10px;">Administrator Panel</h1>
                <p style="color: rgba(255,255,255,0.7);">Manage the website.</p>
            </div>

        <h3>All Users</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Points</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email'] ? $u['email'] : 'No Email'); ?></td>
                        <td><?php echo $u['points']; ?></td>
                        <td><?php echo $u['is_admin'] ? '<span style="color:#ff4757;font-weight:bold;">Admin</span>' : 'User'; ?></td>
                        <td>
                            <?php if ($u['id'] != $user_id): ?>
                                <a href="#" class="neon-btn-danger" style="text-decoration:none;" onclick="deleteUser(<?php echo $u['id']; ?>)">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>All Quizzes</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $q): ?>
                    <tr>
                        <td><?php echo $q['id']; ?></td>
                        <td><?php echo htmlspecialchars($q['title']); ?></td>
                        <td><?php echo htmlspecialchars($q['username']); ?></td>
                        <td><?php echo $q['is_published'] ? 'Published' : 'Draft'; ?></td>
                        <td>
                            <a href="#" class="neon-btn-danger" style="text-decoration:none;" onclick="deleteQuiz(<?php echo $q['id']; ?>)">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>🚨 User Reports</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['username']); ?></td>
                        <td><?php echo htmlspecialchars($r['title']); ?></td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($r['description']); ?>">
                            <?php echo htmlspecialchars($r['description']); ?>
                        </td>
                        <td>
                            <?php if ($r['status'] == 'pending'): ?>
                                <span style="color:#ffcc00; font-weight:bold;">Pending</span>
                            <?php else: ?>
                                <span style="color:#76c900; font-weight:bold;">Resolved</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="#" class="neon-btn-info" style="text-decoration:none;" onclick="openAdminChat(<?php echo $r['id']; ?>)">View / Chat</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Website Logs</h3>
        <div class="console-logs" style="background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(46, 213, 115, 0.3); border-radius: 10px; padding: 15px; margin-bottom: 30px; font-family: 'Courier New', monospace; color: #2ed573; max-height: 250px; overflow-y: auto; box-shadow: inset 0 0 15px rgba(0,0,0,0.8), 0 0 10px rgba(46, 213, 115, 0.1);">
            <?php if (empty($logs)): ?>
                <div class="log-entry" style="color: #00ff00 !important; font-size: 1rem !important; margin-bottom: 5px !important;">> No system logs found.</div>
            <?php else: ?>
                <?php foreach ($logs as $l): ?>
                    <div class="log-entry" style="color: #00ff00 !important; font-size: 1rem !important; margin-bottom: 5px !important; word-wrap: break-word !important;">
                        <span class="log-time" style="color: #888 !important;">[<?php echo date('Y-m-d H:i:s', strtotime($l['created_at'])); ?>]</span> 
                        <span class="log-user" style="color: #00d2ff !important; font-weight: bold !important;"><?php echo htmlspecialchars($l['username']); ?></span>: 
                        <span class="log-action" style="color: #00ff00 !important;"><?php echo htmlspecialchars($l['action']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <hr style="border: 1px solid rgba(255,255,255,0.2); margin: 40px 0;">
        <h2>Forum Administration</h2>

        <h3>Forum Pending Category Requests</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Category</th><th>User</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($forum_pending_requests as $req): ?>
                    <tr id="request-<?php echo $req['id']; ?>">
                        <td><?php echo htmlspecialchars($req['icon'] . ' ' . $req['name']); ?></td>
                        <td><?php echo htmlspecialchars($req['username']); ?></td>
                        <td><?php echo htmlspecialchars($req['description']); ?></td>
                        <td>
                            <input type="text" class="admin-note-input" id="note-<?php echo $req['id']; ?>" placeholder="Note" style="width: 100px; padding: 5px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;">
                            <button class="neon-btn-success" onclick="adminAction('approve_category', <?php echo $req['id']; ?>)">Approve</button>
                            <button class="neon-btn-danger" onclick="adminAction('reject_category', <?php echo $req['id']; ?>)">Reject</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Forum Pending Reports</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Report #</th><th>Reporter</th><th>Reason</th><th>Post Snippet</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($forum_reports as $rp): ?>
                    <tr id="report-<?php echo $rp['id']; ?>">
                        <td><?php echo $rp['id']; ?></td>
                        <td><?php echo htmlspecialchars($rp['reporter']); ?></td>
                        <td><?php echo htmlspecialchars($rp['reason']); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr($rp['post_body'], 0, 100)); ?>...</td>
                        <td>
                            <a href="../forum/thread.php?id=<?php echo $rp['thread_id']; ?>#post-<?php echo $rp['post_id']; ?>" class="neon-btn-info" style="text-decoration:none;">View</a>
                            <button class="neon-btn-success" onclick="adminAction('resolve_report', <?php echo $rp['id']; ?>)">Resolve</button>
                            <button class="neon-btn-danger" onclick="adminAction('delete_reported_post', <?php echo $rp['id']; ?>, <?php echo $rp['post_id']; ?>)">Delete Post</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Forum Categories (Manage)</h3>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Name</th><th>Icon</th><th>Order</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($forum_categories as $cat): ?>
                    <tr id="cat-<?php echo $cat['id']; ?>">
                        <td><?php echo $cat['id']; ?></td>
                        <td><input type="text" id="cat-name-<?php echo $cat['id']; ?>" value="<?php echo htmlspecialchars($cat['name']); ?>" style="padding: 5px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;"></td>
                        <td><input type="text" id="cat-icon-<?php echo $cat['id']; ?>" value="<?php echo htmlspecialchars($cat['icon']); ?>" style="width: 50px; padding: 5px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;"></td>
                        <td><input type="number" id="cat-order-<?php echo $cat['id']; ?>" value="<?php echo $cat['sort_order']; ?>" style="width: 60px; padding: 5px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;"></td>
                        <td>
                            <button class="neon-btn-info" onclick="editCategory(<?php echo $cat['id']; ?>)">Save</button>
                            <button class="neon-btn-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="../js/admin.js"></script>
    <script>

        function adminAction(action, targetId, extraId = null) {             // forom js logic mapped to /forum/php/admin_actions.php
            let data = { action: action, id: targetId };
            if (action === 'approve_category' || action === 'reject_category') {
                data.note = document.getElementById('note-' + targetId).value;
            }
            if (action === 'delete_reported_post') {
                data.post_id = extraId;
            }
            fetch('../forum/php/admin_actions.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).then(res => res.json()).then(data => {
                if (data.success) { location.reload(); } else { alert(data.message); }
            });
        }
        function editCategory(id) {
            const name = document.getElementById('cat-name-' + id).value;
            const icon = document.getElementById('cat-icon-' + id).value;
            const order = document.getElementById('cat-order-' + id).value;
            fetch('../forum/php/admin_actions.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'edit_category', id: id, name: name, icon: icon, sort_order: order })
            }).then(() => location.reload());
        }
        function deleteCategory(id) {
            if(!confirm('Delete this category?')) return;
            fetch('../forum/php/admin_actions.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete_category', id: id })
            }).then(() => location.reload());
        }
    </script>
        </div>
    </div>
</body>
</html>
