<?php
session_start();
include "../php/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}
$user_id = $_SESSION['user_id'];
$reports = [];
$stmt = $conn->prepare("SELECT id, title, status, created_at FROM reports WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while($r = $res->fetch_assoc()){
    $reports[] = $r;
}
include_once "../php/sanitize.php";
sanitize_array($reports);

echo json_encode(['success' => true, 'reports' => $reports]);
?>
