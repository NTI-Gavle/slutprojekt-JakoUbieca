<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$is_admin = 0;
$adm_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$adm_stmt->bind_param("i", $user_id);
$adm_stmt->execute();
$adm_stmt->bind_result($is_admin);
$adm_stmt->fetch();
$adm_stmt->close();

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
            <a href="index.php"><?php echo htmlspecialchars($lang['forum_community']); ?></a>
            <span>›</span>
            <span><?php echo htmlspecialchars($lang['forum_request_category']); ?></span>
        </div>

        <?php if ($is_admin == 1): ?>
        <div class="glass-panel" style="border: 1px solid #ff4757; box-shadow: 0 4px 15px rgba(255, 71, 87, 0.2); position: relative; overflow: hidden;">
            <div style="position: absolute; top:0; right:0; background: #ff4757; color: white; padding: 2px 10px; font-size: 0.8rem; font-weight: bold; border-bottom-left-radius: 10px;">Admin</div>
            <h2 style="color: #ff4757; margin-top: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">⚡ Direct Category Creation</h2>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 25px;">Instantly create a new category bypassing the request system.</p>

            <form id="adminCreateCategoryForm">
                <input type="hidden" name="action" value="create_category">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Science Quizzes" required maxlength="100">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="reply-textarea" style="min-height: 80px;" placeholder="Describe what this category would be about..." required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Icon (emoji)</label>
                        <input type="text" name="icon" class="form-input" placeholder="e.g. 🔬" maxlength="50" value="📁">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-input" value="10">
                    </div>
                </div>

                <button type="submit" class="btn-forum" style="background: #ff4757; color: white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold;">⚡ Create Instantly</button>
            </form>
            <p id="admin-create-msg" style="margin-top: 15px; font-weight: bold; color: #fff;"></p>
        </div>
        <?php endif; ?>

        <div class="glass-panel">
            <h2 style="color: #fff; margin-top: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">📁 <?php echo htmlspecialchars($lang['forum_req_cat_title']); ?></h2>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 25px;">Submit a request and an admin will verify it.</p>

            <form id="requestCategoryForm">
                <div class="form-group">
                    <label><?php echo htmlspecialchars($lang['forum_req_cat_name']); ?></label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Science Quizzes" required maxlength="100">
                </div>

                <div class="form-group">
                    <label><?php echo htmlspecialchars($lang['forum_req_cat_reason']); ?></label>
                    <textarea name="description" class="reply-textarea" style="min-height: 100px;" placeholder="Describe what this category would be about..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Icon (emoji)</label>
                    <input type="text" name="icon" class="form-input" placeholder="e.g. 🔬" maxlength="50" value="📁">
                </div>

                <button type="submit" class="btn-forum btn-forum-primary">📤 <?php echo htmlspecialchars($lang['forum_req_cat_submit']); ?></button>
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

        <?php if ($is_admin == 1): ?>
        document.getElementById('adminCreateCategoryForm').addEventListener('submit', function(e) {                         // intercpt the admin creation form and sends the data as a json object to php/admin_actions.php
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            formData.forEach((val, key) => data[key] = val);

            fetch('php/admin_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                const msg = document.getElementById('admin-create-msg');
                if (result.success) {
                    msg.style.color = '#2ed573';
                    msg.innerText = '✅ Category created instantly!';
                    setTimeout(() => location.href = 'index.php', 1500);
                } else {
                    msg.style.color = '#ff4757';
                    msg.innerText = '❌ ' + result.message;
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>
