<?php
session_start();
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
    <title>Quiz Master - Multiplayer Lobby</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/multiplayer.css?v=1.0">
</head>
<body>

<div class="sky-bg"></div>

<div class="lobby-wrapper">
    <div class="header-nav">
        <h1>⚔️ Мултиплейър <span class="accent-text">Lobby</span></h1>
        <a href="../dashboard.php" class="back-link">← Back to lobby</a>
    </div>

    <div class="main-layout">
        <div class="glass-card" id="host-section">
            <h2>Host</h2>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 20px;">Create your lobby and invite friends.</p>
            
            <button id="btn-create-lobby">Create new game</button>
            
            <div id="active-lobby" style="display:none; flex-direction: column; flex-grow: 1; margin-top: 20px;">
                <div style="text-align: center; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 20px; border: 1px dashed var(--accent);">
                    <small>Code for access</small>
                    <div id="display-lobby-code" style="color: var(--accent); font-size: 40px; font-weight: bold; letter-spacing: 5px;">------</div>
                </div>
                
                <label for="select-quiz" style="margin-top: 20px; display: block;">Select Quiz:</label>
                <select id="select-quiz">
                    <option value="">loading...</option>
                </select>

                <div class="player-list-container">
                    <p style="font-size: 0.8rem; font-weight: bold; text-transform: uppercase;">Waiting / Joined Players</p>
                    <ul id="waiting-players-list" style="list-style: none; padding: 0;"></ul>
                    <ul id="approved-players-list" style="list-style: none; padding: 0;"></ul>
                </div>
                
                <button id="btn-start-game" class="confirm-btn" disabled>Start Quiz</button>
            </div>
        </div>

        <div class="glass-card" id="join-section">
            <h2>Join</h2>
            <p style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 20px;">Enter the code and wait for approval.</p>
            
            <div style="display: flex; flex-direction: column; justify-content: center; flex-grow: 1;">
                <input type="text" id="input-lobby-code" placeholder="Enter code" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 3px;">
                <button id="btn-join-lobby">Join the lobby</button>
                <div id="join-status" style="margin-top: 20px; text-align: center; font-weight: 600;"></div>
            </div>

            <div style="margin-top: auto; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 15px; font-size: 0.8rem;">
                💡 <b>Note:</b> The quiz starts at the host's discretion.
            </div>
        </div>
    </div>
</div>

<script src="js/multiplayer_host.js"></script>
<script src="js/multiplayer_join.js"></script>

</body>
</html>