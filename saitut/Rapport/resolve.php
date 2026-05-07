<?php
session_start();
include "../php/db.php";
include "../php/logger.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

$admin_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");                  // adminn chek
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if ($is_admin != 1) {
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

$report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
$reply_msg = trim($_POST['reply_msg'] ?? '');

if ($report_id === 0 || empty($reply_msg)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$stmt = $conn->prepare("SELECT user_id, title FROM reports WHERE id = ?");   // chek  user and title + ID
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit;
}
$report = $res->fetch_assoc();
$target_user_id = $report['user_id'];
$report_title = $report['title'];
$stmt->close();

$full_msg = "Admin Reply to your report [" . $report_title . "]: " . $reply_msg;            // msg to user
$ins = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$ins->bind_param("iis", $admin_id, $target_user_id, $full_msg);
$ins->execute();
$ins->close();

$upd = $conn->prepare("UPDATE reports SET status = 'resolved' WHERE id = ?");   // finnish report
$upd->bind_param("i", $report_id);
$upd->execute();
$upd->close();

addSystemLog($conn, $admin_id, "Resolved a report: " . $report_title);

echo json_encode(['success' => true]);
?>
