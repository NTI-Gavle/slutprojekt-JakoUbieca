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

$stmt = $conn->prepare("INSERT INTO forum_threads (category_id, user_id, title, body) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $category_id, $user_id, $title, $body);

if ($stmt->execute()) {
    $thread_id = $stmt->insert_id;
    echo json_encode(['success' => true, 'thread_id' => $thread_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create thread']);
}
$stmt->close();
?>
