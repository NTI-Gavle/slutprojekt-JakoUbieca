<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "No active session"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'];
$points  = intval($data['points'] ?? 0);
$correct = intval($data['correct'] ?? 0);
$wrong   = intval($data['wrong'] ?? 0);


$stmt = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
$stmt->bind_param("ii", $points, $user_id);
$stmt->execute();
$stmt->close();


$check = $conn->prepare("SELECT 1 FROM user_stats WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $insert = $conn->prepare("
        INSERT INTO user_stats 
        (user_id, quizzes_played, correct_answers, wrong_answers, total_points)
        VALUES (?, 1, ?, ?, ?)
    ");
    $insert->bind_param("iiii", $user_id, $correct, $wrong, $points);
    $insert->execute();
    $insert->close();
} else {
    $update = $conn->prepare("
        UPDATE user_stats
        SET quizzes_played = quizzes_played + 1,
            correct_answers = correct_answers + ?,
            wrong_answers = wrong_answers + ?,
            total_points = total_points + ?
        WHERE user_id = ?
    ");
    $update->bind_param("iiii", $correct, $wrong, $points, $user_id);
    $update->execute();
    $update->close();
}
$check->close();


$get_total = $conn->prepare("SELECT total_points FROM user_stats WHERE user_id = ?");
$get_total->bind_param("i", $user_id);
$get_total->execute();
$current_total = $get_total->get_result()->fetch_assoc()['total_points'];


$stat_query = $conn->query("SELECT COUNT(*) as total FROM user_stats");
$total_players = $stat_query->fetch_assoc()['total'];

$beaten_query = $conn->prepare("SELECT COUNT(*) as beaten FROM user_stats WHERE total_points < ?");
$beaten_query->bind_param("i", $current_total);
$beaten_query->execute();
$beaten_players = $beaten_query->get_result()->fetch_assoc()['beaten'];

$percentile = 0;
if ($total_players > 1) {
    $percentile = round(($beaten_players / ($total_players - 1)) * 100);
}

echo json_encode([
    "success" => true,
    "points" => $points,
    "correct" => $correct,
    "wrong" => $wrong,
    "percentile" => $percentile
]);