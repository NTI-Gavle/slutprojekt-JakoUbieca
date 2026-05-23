<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($category_id === 0) {
    header("Location: index.php");
    exit;
}
$cat_stmt = $conn->prepare("SELECT name, icon FROM forum_categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_stmt->bind_result($cat_name, $cat_icon);
if (!$cat_stmt->fetch()) {
    die("Category not found.");
}
$cat_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>New Thread - Forum</title>
    <link rel="stylesheet" href="css/forum.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="sky-bg"></div>
    <div class="grass-floor"></div>            

    <div class="forum-container">
        <div class="breadcrumb">
            <a href="index.php"><?php echo htmlspecialchars($lang['forum_community']); ?></a>
            <span>›</span>
            <a href="category.php?id=<?php echo $category_id; ?>"><?php echo htmlspecialchars($cat_icon . ' ' . $cat_name); ?></a>
            <span>›</span>
            <span><?php echo htmlspecialchars($lang['forum_new_thread']); ?></span>
        </div>

        <div class="glass-panel">
            <h2 style="color: #fff; margin-top: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">✏️ <?php echo htmlspecialchars($lang['forum_new_thread']); ?></h2>
            <p style="color: rgba(255,255,255,0.7); margin-bottom: 25px;"><?php echo htmlspecialchars($lang['forum_create_thread_title']); ?> <?php echo htmlspecialchars($cat_icon . ' ' . $cat_name); ?></p>
            
            <form id="createThreadForm">
                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">          <!-- Form for creating a new thread Includes fields for name info and an optional image post. -->
                
                <div class="form-group">
                    <label><?php echo htmlspecialchars($lang['forum_thread_title_label']); ?></label>
                    <input type="text" name="title" class="form-input" placeholder="Enter a descriptive title..." required maxlength="255">
                </div>

                <div class="form-group">
                    <label><?php echo htmlspecialchars($lang['forum_thread_content_label']); ?></label>
                    <textarea name="body" class="reply-textarea" style="min-height: 200px;" placeholder="Write your post here... You can include links and mention users with @username" required></textarea>
                </div>
                
                <div class="form-group">
                    <input type="file" id="forumImageInput" accept="image/*" style="display:none;" onchange="uploadForumImage()">
                    <button type="button" class="btn-forum" id="forumImageBtn" style="background: #fdcb6e; color: #2d3436; margin-bottom: 10px;" onclick="document.getElementById('forumImageInput').click()">📎 Attach Image</button>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-forum btn-forum-primary">📤 <?php echo htmlspecialchars($lang['forum_submit_thread']); ?></button>
                    <a href="category.php?id=<?php echo $category_id; ?>" class="btn-forum"><?php echo htmlspecialchars($lang['forum_cancel']); ?></a>
                </div>
            </form>

            <p id="thread-msg" style="margin-top: 15px; font-weight: bold; color: #fff;"></p>
        </div>

    </div>

    <script src="js/forum.js?v=<?php echo time(); ?>"></script>
    <script>
        document.getElementById('createThreadForm').addEventListener('submit', function(e) {         // Intercept the form submission to send data asynchronously via fetch api. appends the uploaded image URL if any... and handles the server response.
            e.preventDefault();
            const formData = new FormData(this);
            const data = {};
            formData.forEach((val, key) => data[key] = val);
            if (pendingForumImageUrl) {
                data['image_url'] = pendingForumImageUrl;
            }

            fetch('php/create_thread.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                const msg = document.getElementById('thread-msg');
                if (result.success) {
                    msg.style.color = '#2ed573';
                    msg.innerText = '✅ Thread created!';
                    setTimeout(() => window.location.href = 'thread.php?id=' + result.thread_id, 800);
                } else {
                    msg.style.color = '#ff4757';
                    msg.innerText = '❌ ' + result.message;
                }
            });
        });
    </script>
</body>
</html>
