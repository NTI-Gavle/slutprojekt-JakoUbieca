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

$name = trim($data['name'] ?? '');
$description = trim($data['description'] ?? '');
$icon = trim($data['icon'] ?? '📁');

if ($name === '' || $description === '') {
    echo json_encode(['success' => false, 'message' => 'Name and description are required']);
    exit;
}

if (mb_strlen($name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Name is too long (max 100 characters)']);
    exit;
}

$dup = $conn->prepare("SELECT id FROM forum_category_requests WHERE user_id = ? AND name = ? AND status = 'pending'");
$dup->bind_param("is", $user_id, $name);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending request with this name']);
    exit;
}
$dup->close();
$stmt = $conn->prepare("INSERT INTO forum_category_requests (user_id, name, description, icon) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $name, $description, $icon);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Request submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit request']);
}
$stmt->close();
?>
