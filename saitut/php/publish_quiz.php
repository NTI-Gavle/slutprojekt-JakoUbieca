<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../profile.php");
    exit;
}

$quiz_id = $_GET['id'];
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("UPDATE quizzes SET is_published = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quiz_id, $user_id);
$stmt->execute();

header("Location: ../profile.php?msg=published");
exit;