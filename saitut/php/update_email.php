<?php
session_start();
include "db.php"; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "No access."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $new_email = $_POST['new_email'];
    $confirm_pass = $_POST['confirm_pass'];

    if (empty($new_email) || empty($confirm_pass)) {
        echo json_encode(["success" => false, "message" => "All fields are required."]);
        exit;
    }

    
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    
    if (password_verify($confirm_pass, $hashed_password)) {
        
       
        $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_email, $user_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Email updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating email."]);
        }
        $update_stmt->close();

    } else {
       
        echo json_encode(["success" => false, "message" => "Wrong password! The change has been denied."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>