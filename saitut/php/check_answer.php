<?php
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['question_id']) || !isset($data['selected'])) {
    echo json_encode(["error" => "Missing data."]);
    exit;
}

$question_id = intval($data['question_id']);
$selected = $data['selected']; 

if (!is_array($selected)) {
    $selected = [$selected];
}

$q_query = $conn->prepare("SELECT correct_answer, points_value FROM questions WHERE id = ?");
$q_query->bind_param("i", $question_id);
$q_query->execute();
$q_res = $q_query->get_result()->fetch_assoc();

if (!$q_res) {
    echo json_encode(["error" => "Question not found."]);
    exit;
}

$points_value = isset($q_res['points_value']) ? intval($q_res['points_value']) : 10;
$correct_text_answer = $q_res['correct_answer'];
$ans_query = $conn->prepare("SELECT answer FROM answers WHERE question_id = ? AND is_correct = 1");
$ans_query->bind_param("i", $question_id);
$ans_query->execute();
$ans_res = $ans_query->get_result();

$correct_answers = [];
while ($row = $ans_res->fetch_assoc()) {
    $correct_answers[] = $row['answer'];
}

if (empty($correct_answers) && !empty($correct_text_answer)) {
    $correct_answers[] = $correct_text_answer;
}

include_once "sanitize.php";
sanitize_array($correct_answers);

$is_correct = false;

sort($selected);
$sorted_correct = $correct_answers;
sort($sorted_correct);

if ($selected === $sorted_correct) {
    $is_correct = true;
}

echo json_encode([
    "correct" => $is_correct,
    "correct_answers" => $correct_answers,
    "points" => $is_correct ? $points_value : 0
]);
