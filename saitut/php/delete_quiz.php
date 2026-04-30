<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../profile.php");
    exit;
}

$quiz_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];


$stmt_answers = $conn->prepare("DELETE FROM answers WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)");
$stmt_answers->bind_param("i", $quiz_id);
$stmt_answers->execute();


$stmt_questions = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();


$stmt_quiz = $conn->prepare("DELETE FROM quizzes WHERE id = ? AND user_id = ?");
$stmt_quiz->bind_param("ii", $quiz_id, $user_id);

if ($stmt_quiz->execute()) {
    header("Location: ../profile.php?msg=deleted");
} else {
    echo "Error: " . $conn->error;
}
exit;