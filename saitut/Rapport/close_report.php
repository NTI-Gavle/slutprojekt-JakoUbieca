<?php
session_start();
include "../php/db.php";
include "../php/logger.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}
$user_id = $_SESSION['user_id'];
$report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;

$stmt = $conn->prepare("SELECT r.user_id, u.is_admin FROM reports r JOIN users u ON u.id = ? WHERE r.id = ?");
$stmt->bind_param("ii", $user_id, $report_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false]);
    exit;
}
$row = $res->fetch_assoc();

if ($row['user_id'] == $user_id || $row['is_admin'] == 1) {
    $upd = $conn->prepare("UPDATE reports SET status = 'resolved' WHERE id = ?");
    $upd->bind_param("i", $report_id);
    $upd->execute();
    
    $rstmt = $conn->prepare("SELECT title FROM reports WHERE id = ?");   //log
    $rstmt->bind_param("i", $report_id);
    $rstmt->execute();
    $rres = $rstmt->get_result();
    $rrow = $rres->fetch_assoc();
    $report_title = $rrow ? $rrow['title'] : 'Unknown Report';
    $rstmt->close();
    
    addSystemLog($conn, $user_id, "Closed report: " . $report_title);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
