<?php
session_start();
include "../php/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if ($is_admin != 1) {
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$achievements = [];

$res = $conn->query("SELECT id, name, icon, description FROM achievements ORDER BY id ASC");   // fetch from db
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $achievements[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $achievements]);
?>
