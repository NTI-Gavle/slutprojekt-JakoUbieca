let joinSessionId = null;
let joinPollInterval = null;

document.getElementById('btn-join-lobby').addEventListener('click', function() {   // Validerar den kod som användaren har angett och skickar en begäran till servern om att lägga till användaren i väntelistan i lobbyn.
    const code = document.getElementById('input-lobby-code').value.trim().toUpperCase();
    const statusText = document.getElementById('join-status');

    if (code.length < 6) {
        statusText.innerText = "❌ Thecode must be at least 6 characters long.";
        statusText.style.color = "#ff4d4d";
        return;
    }

    
    fetch(`php/manage_lobby.php?action=join_lobby&code=${code}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                joinSessionId = data.session_id;
                statusText.innerText = "⏳ The request has been sent! Wait for approval from the host...";
                statusText.style.color = "#ffcc00";
                
              
                document.getElementById('btn-join-lobby').style.display = 'none';

              
                joinPollInterval = setInterval(checkIfApproved, 2000);
            } else {
                statusText.innerText = "❌ " + data.message;
                statusText.style.color = "#ff4d4d";
            }
        });
});

function checkIfApproved() {
    if (!joinSessionId) return;

    fetch(`php/manage_lobby.php?action=get_players&session_id=${joinSessionId}`)
        .then(res => res.json())                    
        .then(players => {

            checkMyStatus();
        });
}

function checkMyStatus() {
   
    fetch(`php/manage_lobby.php?action=check_my_status&session_id=${joinSessionId}`)
        .then(res => res.json())
        .then(data => {
            if (data.approved) {
                document.getElementById('join-status').innerText = "✅ You are approved! Wait for the host to start the game...";
                document.getElementById('join-status').style.color = "#28a745";
                
                
                if (data.game_started) {
                    clearInterval(joinPollInterval);
                    window.location.href = `../quiz.php?id=${data.quiz_id}&mode=multi&session_id=${joinSessionId}`;
                }
            }
        });
}