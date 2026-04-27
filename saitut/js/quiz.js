let questions = [];
let currentQuestionIndex = 0;
let totalPoints = 0;
let correctCount = 0;
let wrongCount = 0;
let quizFinished = false;


const urlParams = new URLSearchParams(window.location.search);
const isMultiplayer = urlParams.get('mode') === 'multi';
const sessionId = urlParams.get('session_id');


let timeLeft = 30;
let timerInterval = null;

const questionText = document.getElementById("question-text");
const answersList = document.getElementById("answers-list");
const scoreDisplay = document.getElementById("score");
const quizContainer = document.getElementById("quiz-container");
const mediaContainer = document.getElementById("quiz-media");


function shuffleArray(array) {           // Blandar svaren för att säkerställa att det rätta svaret inte alltid ligger på samma plats.
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}


const timerDisplay = document.createElement("div");
timerDisplay.id = "timer-display";
questionText.before(timerDisplay);


fetch(`php/get_questions.php?quiz_id=${quizId}&t=${Date.now()}`)
    .then(res => res.json())
    .then(data => {
        questions = data;
        if (questions.length > 0) displayQuestion();
        else questionText.innerText = "There are no questions available for this quiz.";
    });


function startTimer(seconds) {
    clearInterval(timerInterval);
    
    timeLeft = (seconds && parseInt(seconds) > 0) ? parseInt(seconds) : 30;
    
    timerDisplay.innerText = `⏱️ ${timeLeft}s`;
    timerDisplay.classList.remove("warning");

    timerInterval = setInterval(() => {
        timeLeft--;
        timerDisplay.innerText = `⏱️ ${timeLeft}s`;

        if (timeLeft <= 5) {
            timerDisplay.classList.add("warning");
        }

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            wrongCount++;
            Effects.showWrong(); 
            nextQuestion();
        }
    }, 1000);
}

function displayQuestion() {
    if (quizFinished) return;

    const q = questions[currentQuestionIndex];
    if (!q) return;

    if (!Array.isArray(q.answers) || q.answers.length === 0) {
        console.error("Missing answers for question:", q);
        questionText.innerText = "Error loading the question.";
        answersList.innerHTML = "";
        return;
    }

    shuffleArray(q.answers);

    console.log("Time for question " + (currentQuestionIndex + 1) + ":", q.timer);

    startTimer(q.timer);
    
    questionText.innerText = q.question;
    answersList.innerHTML = "";
    
    // MEDIA LOGIC
    if (mediaContainer) {
        mediaContainer.innerHTML = "";                                  // Denna sektion bearbetar multimedieinnehållet i frågan dynamiskt: först kontrollerar den om URLadressen kommer från YouTube 
        if (q.media_url && q.media_url.trim() !== "" && q.media_url !== "null") {                  // för att integrera en videospelare, och om så inte är fallet försöker den ladda en standardvideo eller växlar automatiskt till en bild vid fel.
            const url = q.media_url.trim();
            const ytMatch = url.match(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);

            if (ytMatch && ytMatch[1]) {
                const videoId = ytMatch[1];
                mediaContainer.innerHTML = `
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/${videoId}?rel=0" frameborder="0" allowfullscreen></iframe>
                    </div>`;
            } else {
                mediaContainer.innerHTML = `
                    <div class="video-container">
                        <video id="generic-video" controls playsinline style="max-width:100%; border-radius:12px;">
                            <source src="${url}">
                            <img src="${url}" style="max-width:100%; border-radius:12px;" alt="Media content">
                        </video>
                    </div>`;
                const videoElem = document.getElementById('generic-video');
                if(videoElem) {
                    videoElem.onerror = function() {
                        mediaContainer.innerHTML = `<img src="${url}" style="max-width:100%; border-radius:12px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">`;
                    };
                }
            }
        }
    }

    if (q.multiple_correct == 1) {
        q.answers.forEach(a => {
            const answerText = typeof a === "object" ? a.text : a;
            const label = document.createElement("label");
            label.className = "answer-btn multi-select-label"; 

            label.innerHTML = `
                <input type="checkbox" value="${answerText}" style="display:none;">
                <span>${answerText}</span>
            `;

            label.onclick = function(e) {
                e.preventDefault(); 
                const checkbox = this.querySelector('input');
                checkbox.checked = !checkbox.checked; 
                this.classList.toggle("selected", checkbox.checked);
            };
            answersList.appendChild(label);                             // Denna paragraf genererar svarssnittet och skiljer mellan två lägen: 
        });                                                            // 1) Multival med kryssrutor och bekräftelseknapp, om frågan tillåter mer än ett rätt svar.
                                                                       // 2) Standardknappar med omedelbar kontroll vid klick för frågor med ett enda möjligt svar.
        const confirmBtn = document.createElement("button");
        confirmBtn.innerText = "Confirm";
        confirmBtn.className = "confirm-btn";
        confirmBtn.onclick = () => checkMultiAnswer(q);
        answersList.appendChild(confirmBtn);

    } else {
        q.answers.forEach(a => {
            const answerText = typeof a === "object" ? a.text : a;
            const btn = document.createElement("button");
            btn.innerText = answerText;
            btn.className = "answer-btn";
            btn.onclick = () => checkSingleAnswer(answerText, q);
            answersList.appendChild(btn);
        });
    }
}

// MULTIPLAYER SYNC
function sendPointsToLobby() {
    if (isMultiplayer && sessionId) {
        const formData = new FormData();
        formData.append('session_id', sessionId);
        formData.append('points', totalPoints);
        fetch('multiplayer/php/update_points.php', { method: 'POST', body: formData });
    }
}

// ANSWER CHECKING 
function checkSingleAnswer(selected, q) {
    if (quizFinished) return;
    clearInterval(timerInterval);

    const buttons = answersList.querySelectorAll(".answer-btn");
    const correctAnswer = q.answers.find(a => a.is_correct == 1)?.text || q.correct_answer;

    buttons.forEach(btn => {
        btn.style.pointerEvents = "none"; 
        if (btn.innerText === correctAnswer) {
            btn.style.background = "rgba(40, 167, 69, 0.7)";
            btn.style.borderColor = "#28a745";
        } else if (btn.innerText === selected) {
            btn.style.background = "rgba(220, 53, 69, 0.7)";
            btn.style.borderColor = "#dc3545";
            btn.classList.add("shake-horizontal");
        }
    });

    if (selected === correctAnswer) {
        correctCount++;
        Effects.showCorrect(); 
        let pointsEarned = (parseInt(q.points_value) || 0) + (timeLeft > 5 ? 5 : 0);
        totalPoints += pointsEarned;
        if (scoreDisplay) scoreDisplay.innerText = totalPoints;
        sendPointsToLobby();
    } else {
        wrongCount++;
        Effects.showWrong(); 
    }

    setTimeout(nextQuestion, 1000); 
}

function checkMultiAnswer(q) {
    clearInterval(timerInterval);

    const labels = answersList.querySelectorAll(".multi-select-label");
    const checked = [...answersList.querySelectorAll("input:checked")].map(i => i.value);
    const correctAnswers = q.answers
        .filter(a => typeof a === "object" && a.is_correct == 1)
        .map(a => a.text);

    const isCorrect = checked.length === correctAnswers.length && checked.every(a => correctAnswers.includes(a));

    labels.forEach(lbl => {
        lbl.style.pointerEvents = "none";
        const val = lbl.querySelector("input").value;
        if (correctAnswers.includes(val)) {
            lbl.style.background = "rgba(40, 167, 69, 0.7)";
        } else if (checked.includes(val)) {
            lbl.style.background = "rgba(220, 53, 69, 0.7)";
            lbl.classList.add("shake-horizontal");
        }
    });

    if (isCorrect) {
        correctCount++;
        Effects.showCorrect(); 
        let pointsEarned = (parseInt(q.points_value) || 0) + (timeLeft > 5 ? 5 : 0);
        totalPoints += pointsEarned;
        if (scoreDisplay) scoreDisplay.innerText = totalPoints;
        sendPointsToLobby();
    } else {
        wrongCount++;
        Effects.showWrong(); 
    }

    setTimeout(nextQuestion, 1200);
}

function nextQuestion() {
    currentQuestionIndex++;
    if (currentQuestionIndex < questions.length) {
        displayQuestion();
    } else {
        finishQuiz();
    }
}

function finishQuiz() {
    quizFinished = true;
    clearInterval(timerInterval);
    if (mediaContainer) mediaContainer.innerHTML = "";

    answersList.innerHTML = "";
    timerDisplay.innerText = "Saving the result...";

    
    let successPercent = (correctCount / questions.length) * 100;

    fetch("php/save_score.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            points: totalPoints,
            correct: correctCount,
            wrong: wrongCount
        })
    })
    .then(res => res.json())
    .then(data => {
        let statHTML = "";
        if (data.percentile !== undefined) {
            statHTML = `<p style="margin-top: 15px; color: #ffcc00; font-weight: bold;">
                🏆 You are better than ${data.percentile}% from the players!
            </p>`;
        }

        quizContainer.innerHTML = `
            <h1 style="animation: slideInUp 0.5s">🏁 Finish</h1>
            <p><b>${totalPoints}</b> total points</p>
            <p>✔ ${correctCount} correct | ❌ ${wrongCount} wrong</p>
            ${statHTML}
            <a href="dashboard.php"><button class="confirm-btn" style="width: auto; padding: 10px 40px;">Exit</button></a>
        `;

        
        Effects.showVictory(Math.round(successPercent));
    });
}

document.getElementById("exit-quiz").onclick = () => {
    if (confirm("Are you sure you want to exit?")) {
        window.location.href = "dashboard.php";
    }
};


if (isMultiplayer) {
    const liveRankings = document.getElementById('live-rankings');
    if (liveRankings) liveRankings.style.display = 'block';

    setInterval(() => {
        fetch(`multiplayer/php/manage_lobby.php?action=get_players&session_id=${sessionId}`)
            .then(res => res.json())
            .then(players => {
                if (!Array.isArray(players)) return;
                players.sort((a, b) => b.points - a.points);
                const rankingsContent = document.getElementById('rankings-content') || liveRankings;
                rankingsContent.innerHTML = "<h3>📊 Live Results:</h3>";
                players.forEach((p, index) => {
                    const color = p.user_id == userId ? "#ffcc00" : "#fff";
                    rankingsContent.innerHTML += `<p style="color: ${color}">${index + 1}. ${p.username}: ${p.points} pts.</p>`;
                });
            });
    }, 3000);
}