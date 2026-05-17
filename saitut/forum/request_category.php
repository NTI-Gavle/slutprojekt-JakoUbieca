<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$my_requests = [];
$r_stmt = $conn->prepare("SELECT * FROM forum_category_requests WHERE user_id = ? ORDER BY created_at DESC");
$r_stmt->bind_param("i", $user_id);
$r_stmt->execute();
$r_res = $r_stmt->get_result();
while ($row = $r_res->fetch_assoc()) {
    $my_requests[] = $row;
}
$r_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Request Category - Forum</title>
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>

    <div class="forum-container">

        <div class="breadcrumb">
            <a href="index.php">Forum</a>
            <span>›</span>
            <span>Request Category</span>
        </div>
        <div class="glass-panel">
            <h2 style="color: #fff; margin-top: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">📁 Request a New Category</h2>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 25px;">Submit a request and an admin will verify it.</p>

            <form id="requestCategoryForm">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Science Quizzes" required maxlength="100">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="reply-textarea" style="min-height: 100px;" placeholder="Describe what this category would be about..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Icon (emoji)</label>
                    <input type="text" name="icon" class="form-input" placeholder="e.g. 🔬" maxlength="50" value="📁">
                </div>

                <button type="submit" class="btn-forum btn-forum-primary">📤 Submit Request</button>
            </form>
            <p id="request-msg" style="margin-top: 15px; font-weight: bold; color: #fff;"></p>
        </div>

        <?php if (count($my_requests) > 0): ?>
        <div class="glass-panel">
            <h3 style="color: #fff; margin-top: 0;">My Previous Requests</h3>
            <?php foreach ($my_requests as $req): ?>
                <div class="admin-item">
                    <div class="admin-item-header">
                        <strong><?php echo htmlspecialchars($req['icon'] . ' ' . $req['name']); ?></strong>
                        <?php
                            $badge_class = 'badge-pending';
                            if ($req['status'] === 'approved') $badge_class = 'badge-approved';
                            if ($req['status'] === 'rejected') $badge_class = 'badge-rejected';
                        ?>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo $req['status']; ?></span>
                    </div>
                    <div class="admin-item-body">
                        <?php echo htmlspecialchars($req['description']); ?>
                        <?php if ($req['admin_note']): ?>
                            <br><em style="opacity: 0.7;">Admin note: <?php echo htmlspecialchars($req['admin_note']); ?></em>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.75rem; opacity: 0.5;">
                        Submitted: <?php echo date('M j, Y', strtotime($req['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>

    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
    <script>
        document.getElementById('requestCategoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            formData.forEach((val, key) => data[key] = val);

            fetch('php/request_category.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                const msg = document.getElementById('request-msg');
                if (result.success) {
                    msg.style.color = '#2ed573';
                    msg.innerText = '✅ Request submitted! An admin will review it.';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    msg.style.color = '#ff4757';
                    msg.innerText = '❌ ' + result.message;
                }
            });
        });
    </script>
</body>
</html>
