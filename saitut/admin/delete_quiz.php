<?php
session_start();
include "../php/db.php";
include "../php/logger.php";

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$user_id = $_SESSION['user_id'];
$target_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($target_id === 0) {
    die("Invalid ID");
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($is_admin);
$stmt->fetch();
$stmt->close();

if ($is_admin != 1) {
    die("Access Denied");
}

$del = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
$del->bind_param("i", $target_id);

$qstmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
$qstmt->bind_param("i", $target_id);
$qstmt->execute();
$qres = $qstmt->get_result();
$qrow = $qres->fetch_assoc();
$quiz_title = $qrow ? $qrow['title'] : 'Unknown Quiz';
$qstmt->close();

if ($del->execute()) {
    addSystemLog($conn, $user_id, "Deleted quiz: " . $quiz_title);
    echo "success";
} else {
    echo "error";
}
$del->close();
?>
