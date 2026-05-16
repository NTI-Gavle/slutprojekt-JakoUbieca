<?php
session_start();
include "../php/db.php";
include "../php/logger.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_admin, username FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($is_admin, $admin_username);
$stmt->fetch();
$stmt->close();

if ($is_admin != 1) {
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$target_user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$achievement_id = isset($data['achievement_id']) ? intval($data['achievement_id']) : 0;

if ($target_user_id == 0 || $achievement_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$check = $conn->prepare("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ?");  // checking userers achivm 
$check->bind_param("ii", $target_user_id, $achievement_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'The user already has this achievement!']);
    exit;
}
$check->close();

$stmt = $conn->prepare("INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
$stmt->bind_param("ii", $target_user_id, $achievement_id);

if ($stmt->execute()) {
    if (function_exists('addSystemLog')) {
        addSystemLog($conn, $admin_id, "Awarded an achievement (ID: $achievement_id) to user ID: $target_user_id");
    }
    echo json_encode(['success' => true, 'message' => 'Achievement awarded successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
