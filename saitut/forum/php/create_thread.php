<?php
session_start();
include "../../php/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$category_id = intval($data['category_id'] ?? 0);
$title = trim($data['title'] ?? '');
$body = trim($data['body'] ?? '');

if ($category_id === 0 || $title === '' || $body === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (mb_strlen($title) > 255) {
    echo json_encode(['success' => false, 'message' => 'Title is too long']);
    exit;
}

$cat_check = $conn->prepare("SELECT id FROM forum_categories WHERE id = ?");
$cat_check->bind_param("i", $category_id);
$cat_check->execute();
if ($cat_check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Category not found']);
    exit;
}
$cat_check->close();

$image_url = isset($data['image_url']) ? trim($data['image_url']) : null;

if ($image_url) {
    $stmt = $conn->prepare("INSERT INTO forum_threads (category_id, user_id, title, body, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $category_id, $user_id, $title, $body, $image_url);
} else {
    $stmt = $conn->prepare("INSERT INTO forum_threads (category_id, user_id, title, body) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $category_id, $user_id, $title, $body);
}

if ($stmt->execute()) {
    $thread_id = $stmt->insert_id;
    
    // Notify subscribers
    $notif_msg = "New thread: " . substr($title, 0, 50);
    $notif_link = "forum/thread.php?id=" . $thread_id;
    
    $sub_sql = "SELECT user_id FROM forum_subscriptions WHERE category_id = ? AND user_id != ?";
    $sub_stmt = $conn->prepare($sub_sql);
    $sub_stmt->bind_param("ii", $category_id, $user_id);
    $sub_stmt->execute();
    $sub_res = $sub_stmt->get_result();
    
    $ins_notif = $conn->prepare("INSERT INTO forum_notifications (user_id, type, from_user_id, message, link) VALUES (?, 'new_thread', ?, ?, ?)");
    while ($row = $sub_res->fetch_assoc()) {
        $sub_user_id = $row['user_id'];
        $ins_notif->bind_param("iiss", $sub_user_id, $user_id, $notif_msg, $notif_link);
        $ins_notif->execute();
    }
    $sub_stmt->close();
    if (isset($ins_notif)) $ins_notif->close();

    echo json_encode(['success' => true, 'thread_id' => $thread_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create thread']);
}
$stmt->close();
?>
