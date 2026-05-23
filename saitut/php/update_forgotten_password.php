<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

   
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

      
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $update->bind_param("si", $hashedPassword, $user_id);
        
        if ($update->execute()) {
            echo "The password has been changed! You can now log in with it.<a href='../login.php'>Enter</a>";
        } else {
            echo "Error updating the database.";
        }
    } else {
        echo "❌ Invalid or expired link. Please request a new one from the login page.";
    }
}
?>