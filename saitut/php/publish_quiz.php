<?php
session_start();
include "db.php";
include "logger.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../profile.php");
    exit;
}

$quiz_id = $_GET['id'];
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("UPDATE quizzes SET is_published = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quiz_id, $user_id);
$stmt->execute();

$qstmt = $conn->prepare("SELECT title FROM quizzes WHERE id = ?");
$qstmt->bind_param("i", $quiz_id);
$qstmt->execute();
$qres = $qstmt->get_result();
$qrow = $qres->fetch_assoc();
$quiz_title = $qrow ? $qrow['title'] : 'Unknown Quiz';
$qstmt->close();

addSystemLog($conn, $user_id, "Published quiz: " . $quiz_title);

header("Location: ../profile.php?msg=published");
exit;