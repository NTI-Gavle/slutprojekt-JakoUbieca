let currentSessionId = null;
let lobbyPollInterval = null;


document.getElementById('btn-create-lobby').addEventListener('click', function() {           // Skickar en begäran till servern om ett nytt spel, visar åtkomstkoden och aktiverar automatisk uppdatering av spelarna.
    fetch('php/create_lobby.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentSessionId = data.session_id;
                document.getElementById('display-lobby-code').innerText = data.lobby_code;
                document.getElementById('active-lobby').style.display = 'block';
                const hostTitle = document.querySelector('#host-controls h2');
                if (hostTitle) {
                 hostTitle.innerText = "You are the Host (Session ID: " + currentSessionId + ")";
                }
                
               
                this.style.display = 'none';

                                                                          // Startar en cykel (polling) som varannan sekund kontrollerar om det finns nya spelare i lobbyn.
                lobbyPollInterval = setInterval(checkLobbyStatus, 2000);
            } else {
                alert("Error: " + data.message);
            }
        });
});


function checkLobbyStatus() {
    if (!currentSessionId) return;

    fetch(`php/manage_lobby.php?action=get_players&session_id=${currentSessionId}`)
        .then(res => res.json())
        .then(players => {
            const waitingList = document.getElementById('waiting-players-list');
            const approvedList = document.getElementById('approved-players-list');
            
            waitingList.innerHTML = "";
            approvedList.innerHTML = "";

            players.forEach(p => {
                const li = document.createElement('li');
                li.style.margin = "10px 0";
                li.innerHTML = `<span>👤 ${p.username}</span> `;

                if (p.status === 'pending') {
                    const btn = document.createElement('button');
                    btn.innerText = "Одобри";
                    btn.style.width = "auto";
                    btn.style.padding = "5px 10px";
                    btn.style.marginLeft = "10px";
                    btn.style.background = "#28a745";
                    btn.onclick = () => approvePlayer(p.user_id);
                    li.appendChild(btn);
                    waitingList.appendChild(li);
                } else {
                    approvedList.appendChild(li);
                }
            });
            

            document.getElementById('btn-start-game').disabled = (approvedList.children.length === 0);
        });


        
}


function approvePlayer(playerId) {
    fetch(`php/manage_lobby.php?action=approve_player&session_id=${currentSessionId}&player_id=${playerId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                checkLobbyStatus(); 
            }
        });
}


document.addEventListener("DOMContentLoaded", () => {            // Så snart sidan är klar laddas alla tillgängliga frågesporter från servern i rullgardinsmenyn.
    fetch('../php/get_quizzes.php') 
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('select-quiz');
            select.innerHTML = '<option value="">-- Select Quiz --</option>';
            data.forEach(quiz => {
                select.innerHTML += `<option value="${quiz.id}">${quiz.title}</option>`;
            });
        });
});


document.getElementById('btn-start-game').addEventListener('click', () => {     // Skickar en signal till alla spelare i sessionen att starta testet och omdirigerar värden till spelskärmen.
    const quizId = document.getElementById('select-quiz').value;
    
    if (!quizId) {
        alert("Please select a quiz first!");
        return;
    }

    fetch(`php/manage_lobby.php?action=start_game&session_id=${currentSessionId}&quiz_id=${quizId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                
                window.location.href = `../quiz.php?id=${quizId}&mode=multi&session_id=${currentSessionId}`;
            }
        });
});