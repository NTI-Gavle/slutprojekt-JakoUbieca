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
}

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

$logs = [];
try {
    $res_l = $conn->query("SELECT * FROM system_logs ORDER BY id DESC LIMIT 100");
    if ($res_l) {
        while ($row = $res_l->fetch_assoc()) {
            $logs[] = $row;
        }
    }
} catch (Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php include "../Rapport/ui.php"; ?>
    <div class="sky-bg"></div>

    <div class="admin-container">
        <div class="admin-header">
            <a href="../profile.php" style="color: white; text-decoration: none; display: block; margin-bottom: 20px;">← Back to Profile</a>
            <h1>Administrator Panel</h1>
            <p>Manage the website.</p>
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
                                <a href="#" class="btn-danger" onclick="deleteUser(<?php echo $u['id']; ?>)">Delete</a>
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
                            <a href="#" class="btn-danger" onclick="deleteQuiz(<?php echo $q['id']; ?>)">Delete</a>
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
                            <a href="#" class="btn-danger" style="background: #00d2ff; border-color: #00d2ff; color: white;" onclick="openAdminChat(<?php echo $r['id']; ?>)">View / Chat</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Website Logs</h3>
        <div class="console-logs" style="background: #000000 !important; border: 2px solid #333 !important; border-radius: 10px !important; padding: 15px !important; margin-bottom: 30px !important; font-family: 'Courier New', Courier, monospace !important; color: #00ff00 !important; max-height: 250px !important; overflow-y: auto !important; box-shadow: inset 0 0 10px rgba(0,0,0,0.8) !important;">
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
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
