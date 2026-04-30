<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You are not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$old_pass = $_POST['old_password'];
$new_pass = $_POST['new_password'];


$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hashed_pass);
$stmt->fetch();
$stmt->close();


if (!password_verify($old_pass, $hashed_pass)) {
    echo json_encode(["success" => false, "message" => "Old password is incorrect!"]);
    exit;
}


$new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
$update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $new_hashed_pass, $user_id);

if ($update_stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Password changed successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error updating database."]);
}
?>