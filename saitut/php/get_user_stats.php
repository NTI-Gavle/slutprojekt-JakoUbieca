<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT quizzes_played, correct_answers, wrong_answers, total_points
    FROM user_stats
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $total = $row['correct_answers'] + $row['wrong_answers'];
    $accuracy = $total > 0 ? round(($row['correct_answers'] / $total) * 100) : 0;

    echo json_encode([
        "success" => true,
        "quizzes_played" => $row['quizzes_played'],
        "correct_answers" => $row['correct_answers'],
        "wrong_answers" => $row['wrong_answers'],
        "total_points" => $row['total_points'],
        "accuracy" => $accuracy
    ]);
} else {
    echo json_encode(["success" => true]);
}
