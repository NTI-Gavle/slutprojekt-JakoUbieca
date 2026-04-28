<?php
session_start();
include "../php/lang_config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Maker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/maker.css">
    <style>
        
        .answer-row input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 45px; 
            height: 45px; 
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid var(--glass-border);
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            flex-shrink: 0;
        }

        .answer-row input[type="checkbox"]:checked {
            background: #76c900;
            border-color: #76c900;
            box-shadow: 0 0 10px rgba(118, 201, 0, 0.5);
        }

        .answer-row input[type="checkbox"]:checked::after {
            content: '✔';
            color: black;
            font-size: 22px;
            font-weight: bold;
        }

       
        .answer-text-input {
            height: 45px; 
            margin-bottom: 0 !important;
            flex: 1;
            font-size: 1rem;
        }

        
        .btn-remove-small {
            width: 30px;
            height: 30px;
            background: rgba(255, 94, 94, 0.2);
            border: 1px solid rgba(255, 94, 94, 0.4);
            color: #ff5e5e;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: 0.3s;
            flex-shrink: 0;
            padding: 0;
            line-height: 1;
        }

        .btn-remove-small:hover {
            background: #ff5e5e;
            color: black;
        }

        
        .question-card {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
                                                                        

<body>
    <?php include "../php/lang_ui.php"; ?>
    <div class="maker-container">
        <h1>🎨 <?php echo htmlspecialchars($lang['quiz_maker_title']); ?></h1>
        
        <form id="quizForm" action="save.php" method="POST">
            <div class="question-card" style="border-top: 4px solid var(--accent);">       
                <h3 style="margin-top:0; color: var(--accent);"><?php echo htmlspecialchars($lang['settings']); ?></h3>            
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 3;">
                        <span class="label-text"><?php echo htmlspecialchars($lang['quiz_title_label']); ?></span>
                        <input type="text" name="quiz_title" placeholder="<?php echo htmlspecialchars($lang['quiz_title_placeholder']); ?>" required>
                    </div>
                    <div style="flex: 1;">
                        <span class="label-text"><?php echo htmlspecialchars($lang['pin_label']); ?></span>
                        <input type="number" name="quiz_pin" placeholder="1234" required>
                    </div>
                </div>
            </div>

            <div id="questions-container"></div>

            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button type="button" class="btn-add" onclick="addQuestion()"><?php echo htmlspecialchars($lang['add_question']); ?></button>
                <a href="../dashboard.php" style="text-decoration:none; color:black; padding:10px 20px; border:1px solid rgba(255,255,255,0.3); border-radius:50px; font-size: 0.9rem;"><?php echo htmlspecialchars($lang['cancel']); ?></a>
            </div>

            <button type="submit" class="btn-submit"><?php echo htmlspecialchars($lang['save_quiz']); ?></button>
        </form>
    </div>

    <script>
        let questionCount = 0;

        function addQuestion() {     // Skapar dynamiskt en ny HTML block för val av typ, timer och multival.     
            questionCount++;
            const container = document.getElementById('questions-container');
            
            const qHtml = `
                <div class="question-card" id="q-card-${questionCount}">
                    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin:0; color: var(--accent);"><?php echo htmlspecialchars($lang['question_number']); ?>${questionCount}</h3>
                        <button type="button" class="btn-remove-q" onclick="removeQuestion(${questionCount})"><?php echo htmlspecialchars($lang['remove_question']); ?></button>
                    </div>
                    
                    <span class="label-text"><?php echo htmlspecialchars($lang['question_text_label']); ?></span>
                    <input type="text" name="questions[${questionCount}][text]" placeholder="<?php echo htmlspecialchars($lang['write_question_placeholder']); ?>" required>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div>
                            <span class="label-text"><?php echo htmlspecialchars($lang['question_type_label']); ?></span>
                            <select name="questions[${questionCount}][type]" onchange="toggleMedia(${questionCount}, this.value)">
                                <option value="text"><?php echo htmlspecialchars($lang['text_type']); ?></option>
                                <option value="media"><?php echo htmlspecialchars($lang['media_type']); ?></option>
                            </select>
                        </div>
                        <div>
                            <span class="label-text"><?php echo htmlspecialchars($lang['time_for_answer']); ?></span>
                            <input type="number" name="questions[${questionCount}][timer]" value="30" min="5" max="300" placeholder="<?php echo htmlspecialchars($lang['example_30']); ?>">
                        </div>
                        <div style="display: flex; align-items: center; padding-top: 15px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor:pointer;">
                                <input type="checkbox" name="questions[${questionCount}][is_multi]" style="width:20px; height:20px;"> 
                                <span class="label-text" style="margin:0;"><?php echo htmlspecialchars($lang['multiple_choice']); ?></span>
                            </label>
                        </div>
                    </div>

                    <div id="media-box-${questionCount}" style="display:none; margin-top: 15px;">
                        <span class="label-text"><?php echo htmlspecialchars($lang['link_to_media']); ?></span>
                        <input type="text" name="questions[${questionCount}][media_url]" placeholder="<?php echo htmlspecialchars($lang['paste_link_placeholder']); ?>">
                    </div>
                    
                    <div id="ans-cont-${questionCount}">
                        <span class="label-text" style="margin-top: 25px; display:block;"><?php echo htmlspecialchars($lang['answer_options_label']); ?></span>
                    </div>
                    
                    <button type="button" class="btn-add" onclick="addAnswer(${questionCount})" 
                            style="margin-top: 15px; font-size: 0.8rem; padding: 8px 15px;"><?php echo htmlspecialchars($lang['add_new_answer']); ?></button>
                </div>`;
            
            container.insertAdjacentHTML('beforeend', qHtml);
            addAnswer(questionCount); 
            addAnswer(questionCount);
        }

        function addAnswer(qId) {
            const cont = document.getElementById(`ans-cont-${qId}`);
            const count = cont.getElementsByClassName('answer-row').length;
            
            const ansHtml = `
                <div class="answer-row" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <input type="checkbox" name="questions[${qId}][answers][${count}][correct]" title="Mark as correct">
                    <input type="text" name="questions[${qId}][answers][${count}][text]" 
                           class="answer-text-input" placeholder="<?php echo htmlspecialchars($lang['write_answer_placeholder']); ?>" required>
                    <button type="button" onclick="this.parentElement.remove()" class="btn-remove-small" title="Remove answer">&times;</button>
                </div>`;
                
            cont.insertAdjacentHTML('beforeend', ansHtml);
        }

        function removeQuestion(id) {
            const el = document.getElementById(`q-card-${id}`);
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        }

        function toggleMedia(qId, val) {
            document.getElementById(`media-box-${qId}`).style.display = (val === 'media') ? 'block' : 'none';
        }

        window.onload = addQuestion;
    </script>
</body>
</html>