<?php
session_start();
include "../../php/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
                                                                         //last 20 unread only shows
$stmt = $conn->prepare("SELECT fn.*, u.username AS from_username         
    FROM forum_notifications fn 
    LEFT JOIN users u ON fn.from_user_id = u.id
    WHERE fn.user_id = ? AND fn.is_read = 0 
    ORDER BY fn.created_at DESC 
    LIMIT 20");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$conn->query("UPDATE forum_notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");

echo json_encode($notifications);
?>
