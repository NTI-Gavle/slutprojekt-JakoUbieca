<?php
session_start();
include "../php/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}
$user_id = $_SESSION['user_id'];
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT r.*, u.is_admin FROM reports r JOIN users u ON u.id = ? WHERE r.id = ?");
$stmt->bind_param("ii", $user_id, $report_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false]);
    exit;
}
$report = $res->fetch_assoc();

if ($report['user_id'] != $user_id && $report['is_admin'] != 1) {
    echo json_encode(['success' => false]);
    exit;
}

$msgs = [];
$m_stmt = $conn->prepare("SELECT rm.*, u.username, u.is_admin FROM report_messages rm JOIN users u ON rm.user_id = u.id WHERE rm.report_id = ? ORDER BY rm.sent_at ASC");
$m_stmt->bind_param("i", $report_id);
$m_stmt->execute();
$m_res = $m_stmt->get_result();
while($r = $m_res->fetch_assoc()){
    $msgs[] = $r;
}

include_once "../php/sanitize.php";
sanitize_array($report);
sanitize_array($msgs);

echo json_encode(['success' => true, 'report' => $report, 'messages' => $msgs, 'current_user' => $user_id]);
?>
