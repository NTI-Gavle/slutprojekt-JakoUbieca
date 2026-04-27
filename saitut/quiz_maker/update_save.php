<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$quiz_id = intval($_POST['quiz_id']);
$quiz_title = $_POST['quiz_title'];
$quiz_pin = $_POST['quiz_pin'];
$questions = isset($_POST['questions']) ? $_POST['questions'] : [];


$check_stmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $quiz_id, $user_id);
$check_stmt->execute();
if (!$check_stmt->get_result()->fetch_assoc()) {
    die("You do not have permission for this operation.");
}
$check_stmt->close();

$conn->begin_transaction();

try {
    
    $update_q = $conn->prepare("UPDATE quizzes SET title = ?, pin = ? WHERE id = ?");
    $update_q->bind_param("ssi", $quiz_title, $quiz_pin, $quiz_id);
    $update_q->execute();
    $update_q->close();

   
    $del_ans = $conn->prepare("DELETE FROM answers WHERE question_id IN (SELECT id FROM questions WHERE quiz_id = ?)");
    $del_ans->bind_param("i", $quiz_id);
    $del_ans->execute();
    $del_ans->close();

    $del_ques = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
    $del_ques->bind_param("i", $quiz_id);
    $del_ques->execute();
    $del_ques->close();

    
    foreach ($questions as $q) {
        $q_text = $q['text'];
        $q_type = ($q['type'] === 'media') ? 'media' : 'text';
        $q_timer = (int)$q['timer'] > 0 ? (int)$q['timer'] : 30;
        
        
        $q_media = (empty($q['media_url']) || $q['media_url'] === 'null' || $q['media_url'] === 'undefined') ? NULL : $q['media_url'];
        
        $is_multi = isset($q['is_multi']) ? 1 : 0;
        $correct_placeholder = ($is_multi) ? 'Multi' : ''; 
        $points_default = 10;

        
        $ins_q = $conn->prepare("INSERT INTO questions (quiz_id, user_id, question, correct_answer, points_value, media_url, multiple_correct, question_type, timer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins_q->bind_param("iissisisi", 
            $quiz_id, 
            $user_id, 
            $q_text, 
            $correct_placeholder, 
            $points_default, 
            $q_media, 
            $is_multi, 
            $q_type, 
            $q_timer
        );
        $ins_q->execute();
        $new_question_id = $conn->insert_id;
        $ins_q->close();

        
        if (isset($q['answers'])) {
            foreach ($q['answers'] as $a) {
                $a_text = $a['text'];
                $is_correct = (isset($a['correct']) && ($a['correct'] === 'on' || $a['correct'] == 1)) ? 1 : 0;

                $ins_a = $conn->prepare("INSERT INTO answers (question_id, answer, is_correct) VALUES (?, ?, ?)");
                $ins_a->bind_param("isi", $new_question_id, $a_text, $is_correct);
                $ins_a->execute();
                $ins_a->close();
            }
        }
    }

    $conn->commit();
    header("Location: ../profile.php?update=success");

} catch (Exception $e) {
    $conn->rollback();
    echo "Error while updating quiz: " . $e->getMessage();
}
?>