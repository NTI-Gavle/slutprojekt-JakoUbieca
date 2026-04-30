<?php
session_start();
include "../php/lang_config.php";
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit;
}

$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT title, pin FROM quizzes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $quiz_id, $user_id);
$stmt->execute();
$quiz_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quiz_data) {
    die("The requested quiz does not exist or you do not have permission to edit it.");
}


$questions = [];
$q_stmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY id ASC");
$q_stmt->bind_param("i", $quiz_id);
$q_stmt->execute();
$q_result = $q_stmt->get_result();

while ($q_row = $q_result->fetch_assoc()) {
    $q_id = $q_row['id'];
    
   
    $ans_stmt = $conn->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY id ASC");
    $ans_stmt->bind_param("i", $q_id);
    $ans_stmt->execute();
    $q_row['answers'] = $ans_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $ans_stmt->close();
    
    $questions[] = $q_row;
}
$q_stmt->close();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✏️ Edit Quiz - <?php echo htmlspecialchars($quiz_data['title']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/maker.css">
    <style>
        .answer-row input[type="checkbox"] {
            appearance: none; -webkit-appearance: none;
            width: 45px; height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid var(--glass-border);
            border-radius: 12px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s; flex-shrink: 0;
        }
        .answer-row input[type="checkbox"]:checked {
            background: #76c900; border-color: #76c900;
        }
        .answer-row input[type="checkbox"]:checked::after {
            content: '✔'; color: white; font-size: 22px; font-weight: bold;
        }
        .answer-text-input {
            height: 45px; margin-bottom: 0 !important; flex: 1; font-size: 1rem;
        }
        .btn-remove-small {
            width: 30px; height: 30px; background: rgba(255, 94, 94, 0.2);
            border: 1px solid rgba(255, 94, 94, 0.4); color: #ff5e5e;
            border-radius: 8px; cursor: pointer; display: flex;
            align-items: center; justify-content: center; font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <?php include "../php/lang_ui.php"; ?>
    <div class="maker-container">
        <h1>✏️ <?php echo htmlspecialchars($lang['edit_quiz_title']); ?></h1>
        
        <form id="quizForm" action="update_save.php" method="POST">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">

            <div class="question-card" style="border-top: 4px solid var(--accent);">
                <h3 style="margin-top:0; color: var(--accent);">📌 <?php echo htmlspecialchars($lang['settings']); ?></h3>
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 3;">
                        <span class="label-text"><?php echo htmlspecialchars($lang['quiz_title_label']); ?></span>
                        <input type="text" name="quiz_title" value="<?php echo htmlspecialchars($quiz_data['title']); ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <span class="label-text"><?php echo htmlspecialchars($lang['pin_label']); ?></span>
                        <input type="number" name="quiz_pin" value="<?php echo $quiz_data['pin']; ?>" required>
                    </div>
                </div>
            </div>

            <div id="questions-container"></div>

            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button type="button" class="btn-add" onclick="addQuestion()"><?php echo htmlspecialchars($lang['add_question']); ?></button>
                <a href="../profile.php" style="text-decoration:none; color:white; padding:10px 20px; border:1px solid rgba(255,255,255,0.3); border-radius:50px; font-size: 0.9rem;"><?php echo htmlspecialchars($lang['cancel']); ?></a>
            </div>

            <button type="submit" class="btn-submit"><?php echo htmlspecialchars($lang['save_changes']); ?></button>
        </form>
    </div>

    <script>
        let questionCount = 0;

    
        function addQuestion(data = null) {
            questionCount++;
            const container = document.getElementById('questions-container');
            
            const qText = data ? data.question_text : '';
            const qType = data ? data.type : 'text';
            const qTimer = data ? data.timer : 30;
            const qMedia = data ? data.media_url : '';
            const isMulti = (data && data.is_multi == 1) ? 'checked' : '';
            const displayMedia = (qType === 'media') ? 'block' : 'none';

            const qHtml = `
                <div class="question-card" id="q-card-${questionCount}">
                    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin:0; color: var(--accent);"><?php echo htmlspecialchars($lang['question_number']); ?>${questionCount}</h3>
                        <button type="button" class="btn-remove-q" onclick="removeQuestion(${questionCount})"><?php echo htmlspecialchars($lang['remove_question']); ?></button>
                    </div>
                    
                    <span class="label-text"><?php echo htmlspecialchars($lang['question_text_label']); ?></span>
                    <input type="text" name="questions[${questionCount}][text]" value="${qText}" placeholder="<?php echo htmlspecialchars($lang['write_question_placeholder']); ?>" required>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div>
                            <span class="label-text"><?php echo htmlspecialchars($lang['question_type_label']); ?></span>
                            <select name="questions[${questionCount}][type]" onchange="toggleMedia(${questionCount}, this.value)">
                                <option value="text" ${qType === 'text' ? 'selected' : ''}><?php echo htmlspecialchars($lang['text_type']); ?></option>
                                <option value="media" ${qType === 'media' ? 'selected' : ''}><?php echo htmlspecialchars($lang['media_type']); ?></option>
                            </select>
                        </div>
                        <div>
                            <span class="label-text"><?php echo htmlspecialchars($lang['time_for_answer']); ?></span>
                            <input type="number" name="questions[${questionCount}][timer]" value="${qTimer}" min="5">
                        </div>
                        <div style="display: flex; align-items: center; padding-top: 15px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor:pointer;">
                                <input type="checkbox" name="questions[${questionCount}][is_multi]" ${isMulti}> 
                                <span class="label-text" style="margin:0;"><?php echo htmlspecialchars($lang['multiple_choice']); ?></span>
                            </label>
                        </div>
                    </div>

                    <div id="media-box-${questionCount}" style="display:${displayMedia}; margin-top: 15px;">
                        <span class="label-text"><?php echo htmlspecialchars($lang['link_to_media']); ?></span>
                        <input type="text" name="questions[${questionCount}][media_url]" value="${qMedia}">
                    </div>
                    
                    <div id="ans-cont-${questionCount}">
                        <span class="label-text" style="margin-top: 25px; display:block;"><?php echo htmlspecialchars($lang['answer_options_label']); ?></span>
                    </div>
                    
                    <button type="button" class="btn-add" onclick="addAnswer(${questionCount})" 
                            style="margin-top: 15px; font-size: 0.8rem; padding: 8px 15px;"><?php echo htmlspecialchars($lang['add_new_answer']); ?></button>
                </div>`;
            
            container.insertAdjacentHTML('beforeend', qHtml);

            if (data && data.answers) {
                data.answers.forEach(ans => addAnswer(questionCount, ans));
            } else {
                addAnswer(questionCount); addAnswer(questionCount);
            }
        }

        function addAnswer(qId, ansData = null) {
            const cont = document.getElementById(`ans-cont-${qId}`);
            const count = cont.getElementsByClassName('answer-row').length;
            
            const aText = ansData ? ansData.answer_text : '';
            const isCorrect = (ansData && ansData.is_correct == 1) ? 'checked' : '';
            
            const ansHtml = `
                <div class="answer-row" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox" name="questions[${qId}][answers][${count}][correct]" ${isCorrect}>
                    <input type="text" name="questions[${qId}][answers][${count}][text]" 
                           class="answer-text-input" value="${aText}" placeholder="<?php echo htmlspecialchars($lang['write_answer_placeholder']); ?>" required>
                    <button type="button" onclick="this.parentElement.remove()" class="btn-remove-small">&times;</button>
                </div>`;
            cont.insertAdjacentHTML('beforeend', ansHtml);
        }

        function removeQuestion(id) { document.getElementById(`q-card-${id}`).remove(); }
        function toggleMedia(qId, val) { document.getElementById(`media-box-${qId}`).style.display = (val === 'media') ? 'block' : 'none'; }

       
        window.onload = () => {
            const existingData = <?php echo json_encode($questions); ?>;
            if (existingData.length > 0) {
                existingData.forEach(q => addQuestion(q));
            } else {
                addQuestion();
            }
        };
    </script>
</body>
</html>