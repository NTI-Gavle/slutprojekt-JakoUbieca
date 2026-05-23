document.addEventListener("DOMContentLoaded", function () {
    console.log("JS LOADED");

    fetch("php/get_quizzes.php")
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById("quiz-list");

            if (!list) {
                console.error("quiz-list is missing!");
                return;
            }

            list.innerHTML = ""; 

            data.forEach(quiz => {
                const div = document.createElement("div");
                div.className = "quiz-item"; 
                div.innerHTML = `
                    <h3>${quiz.title}</h3>
                    <a href="quiz.php?id=${quiz.id}&mode=solo">
                        <button class="solo-btn">Solo</button>
                    </a>
                `;
                list.appendChild(div);
            });
        })
        .catch(err => console.error("JS error:", err));
});