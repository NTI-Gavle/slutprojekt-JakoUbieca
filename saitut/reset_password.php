<?php
include "php/db.php";
$token = $_GET['token'] ?? '';
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("<h1 style='color:white; text-align:center; margin-top:50px;'>The link is invalid or expired.</h1>");
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <h1>New Password</h1>
        <form action="php/update_forgotten_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="password" name="new_password" placeholder="Enter new password" required class="auth-input">
            <button type="submit" class="auth-button">Save Password</button>
        </form>
    </div>
</body>
</html>