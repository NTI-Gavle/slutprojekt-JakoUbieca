<?php
session_start();
include "../php/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}
$user_id = $_SESSION['user_id'];
$report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
$message = trim($_POST['message'] ?? '');

if ($report_id === 0 || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$stmt = $conn->prepare("SELECT r.user_id, r.status, u.is_admin FROM reports r JOIN users u ON u.id = ? WHERE r.id = ?");
$stmt->bind_param("ii", $user_id, $report_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit;
}
$row = $res->fetch_assoc();
if ($row['user_id'] != $user_id && $row['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Access Denied.']);
    exit;
}
if ($row['status'] == 'resolved') {
    echo json_encode(['success' => false, 'message' => 'Report is closed.']);
    exit;
}

$ins = $conn->prepare("INSERT INTO report_messages (report_id, user_id, message) VALUES (?, ?, ?)");
$ins->bind_param("iis", $report_id, $user_id, $message);
$ins->execute();
echo json_encode(['success' => true]);
?>
