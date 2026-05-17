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
$post_id = intval($data['post_id'] ?? 0);
$reason = trim($data['reason'] ?? '');

if ($post_id === 0 || $reason === '') {
    echo json_encode(['success' => false, 'message' => 'Post ID and reason are required']);
    exit;
}

$check = $conn->prepare("SELECT id FROM forum_posts WHERE id = ?");
$check->bind_param("i", $post_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}
$check->close();
$dup = $conn->prepare("SELECT id FROM forum_reports WHERE reporter_id = ? AND post_id = ? AND status = 'pending'");
$dup->bind_param("ii", $user_id, $post_id);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You already reported this post']);
    exit;
}
$dup->close();
$stmt = $conn->prepare("INSERT INTO forum_reports (reporter_id, post_id, reason) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $post_id, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Report submitted. An admin will review it.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit report']);
}
$stmt->close();
?>
