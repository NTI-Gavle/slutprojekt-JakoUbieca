<?php
session_start();
include "../php/db.php"; 

if (!isset($_SESSION['user_id'])) {
    die("No access.");
}

$user_id = $_SESSION['user_id'];
$title = $_POST['quiz_title'];
$pin = $_POST['quiz_pin'];


$conn->begin_transaction();

try {
 
    $stmt = $conn->prepare("INSERT INTO quizzes (user_id, title, pin, is_published) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("isi", $user_id, $title, $pin);
    $stmt->execute();
    $quiz_id = $stmt->insert_id;

 
    if (isset($_POST['questions'])) {
        foreach ($_POST['questions'] as $qData) {
            $q_text = $qData['text'];
            $q_type = $qData['type'];
            $media_url = ($q_type === 'media') ? $qData['media_url'] : null;
            $is_multi = isset($qData['is_multi']) ? 1 : 0;
          
            $q_timer = isset($qData['timer']) ? intval($qData['timer']) : 30;
            
          
            $first_correct = "";
            if (isset($qData['answers'])) {
                foreach ($qData['answers'] as $aData) {
                    if (isset($aData['correct'])) {
                        $first_correct = $aData['text'];
                        break;
                    }
                }
            }
            
           
            $final_correct_val = ($is_multi) ? "Multi" : $first_correct;

          
            $stmt_q = $conn->prepare("INSERT INTO questions (quiz_id, user_id, question, question_type, media_url, multiple_correct, correct_answer, timer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_q->bind_param("iisssisi", $quiz_id, $user_id, $q_text, $q_type, $media_url, $is_multi, $final_correct_val, $q_timer);
            $stmt_q->execute();
            $question_id = $stmt_q->insert_id;

          
            if (isset($qData['answers'])) {
                foreach ($qData['answers'] as $aData) {
                    $ans_text = $aData['text'];
                    $is_correct = isset($aData['correct']) ? 1 : 0;
                    
                    $stmt_a = $conn->prepare("INSERT INTO answers (question_id, answer, is_correct) VALUES (?, ?, ?)");
                    $stmt_a->bind_param("isi", $question_id, $ans_text, $is_correct);
                    $stmt_a->execute();
                }
            }
        }
    }

   
    $conn->commit();
    header("Location: ../profile.php?status=created");
    exit;

} catch (Exception $e) {
    
    $conn->rollback();
    die("Error while saving: " . $e->getMessage());
}
?>