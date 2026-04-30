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
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
