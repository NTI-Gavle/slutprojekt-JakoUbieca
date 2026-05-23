<?php
session_start();
include "../../php/db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$category_id = isset($data['category_id']) ? intval($data['category_id']) : 0;

if ($category_id === 0) {
    echo json_encode(["success" => false, "message" => "Invalid category"]);
    exit;
}

// Check if already subscribed
$check_stmt = $conn->prepare("SELECT id FROM forum_subscriptions WHERE category_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $category_id, $user_id);
$check_stmt->execute();
$check_res = $check_stmt->get_result();

if ($check_res->num_rows > 0) {
    // Unsubscribe
    $del_stmt = $conn->prepare("DELETE FROM forum_subscriptions WHERE category_id = ? AND user_id = ?");
    $del_stmt->bind_param("ii", $category_id, $user_id);
    if ($del_stmt->execute()) {
        echo json_encode(["success" => true, "status" => "unsubscribed"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    $del_stmt->close();
} else {
    // Subscribe
    $ins_stmt = $conn->prepare("INSERT INTO forum_subscriptions (category_id, user_id) VALUES (?, ?)");
    $ins_stmt->bind_param("ii", $category_id, $user_id);
    if ($ins_stmt->execute()) {
        echo json_encode(["success" => true, "status" => "subscribed"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    $ins_stmt->close();
}

$check_stmt->close();
?>
