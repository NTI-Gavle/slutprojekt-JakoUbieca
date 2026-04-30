<?php
include "db.php";

if (!isset($_GET['quiz_id'])) {
    echo json_encode(["error" => "Quiz ID not provided."]);
    exit;
}

$quiz_id = intval($_GET['quiz_id']);


$query = $conn->prepare("
    SELECT id, question, correct_answer, points_value, media_url, timer, multiple_correct
    FROM questions
    WHERE quiz_id = ?
    ORDER BY RAND()
");
$query->bind_param("i", $quiz_id);
$query->execute();
$result = $query->get_result();

$questions = [];

while ($q = $result->fetch_assoc()) {
    $q_id = $q['id'];
    $q['points_value'] = $q['points_value'] ?? 10;
    
    
    $q['timer'] = isset($q['timer']) ? (int)$q['timer'] : 30;
    
    $q['answers'] = [];

    $ans_query = $conn->prepare("
        SELECT answer, is_correct
        FROM answers
        WHERE question_id = ?
    ");
    $ans_query->bind_param("i", $q_id);
    $ans_query->execute();
    $ans_result = $ans_query->get_result();

    $correctCounter = 0;

    while ($a = $ans_result->fetch_assoc()) {
        $q['answers'][] = [
            "text" => $a['answer'],
            "is_correct" => (int)$a['is_correct']
        ];

        if ((int)$a['is_correct'] === 1) {
            $correctCounter++;
        }
    }

   
    if ($correctCounter > 1) {
        $q['multiple_correct'] = 1;
       
        if (strpos($q['question'], "(Choose") === false) {
            $q['question'] .= " (Choose $correctCounter correct answers)";
        }
    } else {
        $q['multiple_correct'] = (int)$q['multiple_correct'];
    }

    $questions[] = $q;
}

header("Content-Type: application/json");

header("Cache-Control: no-cache, must-revalidate"); 
echo json_encode($questions);