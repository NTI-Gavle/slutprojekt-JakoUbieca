<?php
session_start();
include "php/lang_config.php";
include "php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freaky Quiz - Chat</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body { margin: 0; padding: 0; overflow: hidden; background-color: #1A1511; background-image: linear-gradient(180deg, #1A1511 0%, #FF7B00 100%); background-size: 100% 100%; font-family: 'Montserrat', sans-serif; color: #EAE4D9; }
        
        .chat-wrapper { display: flex; justify-content: center; align-items: center; height: 100vh; width: 100vw; position: relative; z-index: 10; padding: 20px; box-sizing: border-box; gap: 20px; perspective: 1000px; max-width: 1600px; margin: 0 auto; }
        
        .chat-sidebar { 
            width: 420px; height: 100%;
            background: rgba(26, 21, 17, 0.6); 
            backdrop-filter: blur(15px) saturate(180%);
            -webkit-backdrop-filter: blur(15px) saturate(180%);
            border: 1px solid rgba(255, 123, 0, 0.3);
            border-radius: 2rem;
            display: flex; flex-direction: column; z-index: 2; position: relative; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .chat-main { 
            flex: 1; display: flex; height: 100%; flex-direction: column; 
            background: rgba(26, 21, 17, 0.6);
            backdrop-filter: blur(15px) saturate(180%);
            -webkit-backdrop-filter: blur(15px) saturate(180%);
            border: 1px solid rgba(255, 123, 0, 0.3);
            border-radius: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.1s ease-out, opacity 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
            overflow: hidden; 
            opacity: 0;
            transform: translateX(50px) scale(0.98);
            pointer-events: none;
            position: relative;
            z-index: 1;
        }
        
        .chat-main.active {
            opacity: 1;
            transform: translateX(0) scale(1);
            pointer-events: auto;
        }

        .room-settings-panel {
            width: 300px; height: 100%;
            background: rgba(255, 123, 0, 0.1);
            backdrop-filter: blur(25px) saturate(200%);
            -webkit-backdrop-filter: blur(25px) saturate(200%);
            border: 1px solid rgba(255, 123, 0, 0.5);
            border-radius: 2rem;
            display: none; flex-direction: column; z-index: 2; position: relative;
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.2), 0 20px 50px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            opacity: 0;
            transform: translateX(-50px) scale(0.98);
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            padding: 20px;
        }
        .room-settings-panel.active {
            display: flex;
            opacity: 1;
            transform: translateX(0) scale(1);
        }

        #bg-grid-effect {
            position: absolute;
            z-index: 0;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(20, 1fr);
            grid-template-rows: repeat(20, 1fr);
            overflow: hidden;
            pointer-events: auto;
        }
        .bg-grid-tile {
            position: relative;
            z-index: 1;
        }
        .bg-grid-tile::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            height: 0.3rem;
            width: 0.3rem;
            border-radius: 50%;
            background: #FF7B00;
            color: #FF7B00;
            transition: 500ms linear all;
            box-shadow: var(--dynamic-shadow);
            opacity: 0.3;
        }
        .bg-grid-tile:hover::before {
            height: 3rem;
            width: 3rem;
            opacity: 0.8;
            transition: 70ms linear all;
            z-index: 10;
        }

        .rapport-btn {
            bottom: auto !important;
            top: 20px !important;
            right: 20px !important;
            z-index: 9999 !important;
        }
        
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-header h2 { margin: 0; color: #fff; font-size: 1.8rem; text-shadow: 1px 1px 5px rgba(0,0,0,0.8); }
        .btn-back { text-decoration: none; color: #fff; font-weight: bold; background: rgba(255, 123, 0, 0.8); padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; transition: 0.2s; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
        .btn-back:hover { background: #FF7B00; }
        
        .search-area { padding: 15px; }
        .search-box { width: 100%; padding: 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: #fff; outline: none; box-sizing: border-box; }
        .search-box::placeholder { color: rgba(255,255,255,0.5); }
        
        .friends-list { flex: 1; overflow-y: auto; padding: 10px; }
        .friend-item { display: flex; align-items: center; padding: 15px; border-radius: 12px; cursor: pointer; margin-bottom: 8px; transition: 0.2s; background: rgba(255,255,255,0.05); }
        .friend-item:hover { background: rgba(255, 123, 0, 0.2); transform: translateY(-2px); border: 1px solid rgba(255, 123, 0, 0.3); }
        .avatar-wrapper { position: relative; margin-right: 18px; }
        .friend-avatar { width: 50px; height: 50px; background: linear-gradient(135deg, #FF9B44, #FF7B00); color: #111; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 1.2rem; }
        .status-dot { position: absolute; bottom: 0; right: 0; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #1A1511; }
        .status-online { background-color: #2ed573; }
        .status-away { background-color: #ffa502; }
        .status-dnd { background-color: #ff4757; }
        .status-offline { background-color: #747d8c; }
        .friend-name { font-weight: 600; color: #fff; text-shadow: 1px 1px 3px rgba(0,0,0,0.8); font-size: 1.1rem; }
        
        .chat-header { padding: 25px 30px; background: rgba(255, 255, 255, 0.05); border-bottom: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); display: flex; align-items: center; }
        .chat-header h2 { margin: 0; font-size: 1.6rem; text-shadow: 1px 1px 5px rgba(0,0,0,0.8); }
        .chat-header a { color: #fff; text-decoration: none; transition: 0.2s; text-shadow: 1px 1px 3px rgba(0,0,0,0.8); }
        .chat-header a:hover { color: #FF7B00; text-decoration: underline; }
        
        .messages-container { flex: 1; overflow-y: auto; padding: 30px; display: flex; flex-direction: column; gap: 15px; }
        .msg { max-width: 75%; padding: 15px 22px; border-radius: 20px; word-wrap: break-word; line-height: 1.5; position: relative; font-weight: 600; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.5); }
        .msg-me { background: #050505; color: #fff; align-self: flex-end; border-bottom-right-radius: 5px; border: 1px solid rgba(255, 123, 0, 0.5); }
        .msg-them { background: #111111; color: #fff; align-self: flex-start; border-bottom-left-radius: 5px; border: 1px solid rgba(255,255,255,0.2); }
        
        .msg-actions { display: none; margin-top: 5px; gap: 10px; justify-content: flex-end; }
        .msg-me:hover .msg-actions { display: flex; }
        .btn-msg-action { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; font-size: 0.8rem; padding: 0; }
        .btn-msg-action:hover { color: #fff; text-decoration: underline; }
        .edited-tag { font-size: 0.7rem; opacity: 0.7; margin-left: 5px; font-style: italic; }
        
        .chat-input-area { padding: 25px 30px; background: rgba(255, 255, 255, 0.05); display: flex; gap: 15px; align-items: center; border-top: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px); }
        .chat-input { flex: 1; padding: 18px 25px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.4); background: rgba(255,255,255,0.85); color: #111; outline: none; font-size: 1.1rem; font-weight: 600; box-shadow: inset 0 2px 5px rgba(0,0,0,0.2); }
        .chat-input::placeholder { color: rgba(0,0,0,0.5); }
        .chat-input:focus { border-color: #FF7B00; background: #fff; }
        .btn-send { background: linear-gradient(135deg, #FF9B44, #FF7B00); color: #111; text-shadow: none; border: none; padding: 0 35px; height: 56px; border-radius: 30px; cursor: pointer; font-weight: bold; font-size: 1.1rem; transition: 0.2s; }
        .btn-send:hover { box-shadow: 0 4px 15px rgba(255, 123, 0, 0.4); transform: translateY(-2px); }
        .btn-upload { background: rgba(255,255,255,0.85); color: #111; border: 1px solid rgba(255,255,255,0.4); width: 56px; height: 56px; border-radius: 50%; cursor: pointer; font-size: 1.4rem; display: flex; justify-content: center; align-items: center; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.2); flex-shrink: 0; }
        .btn-upload:hover { background: #fff; border-color: #FF7B00; color: #FF7B00; }
        
        #searchResults { margin-top: 10px; }
        .user-result-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 8px; margin-bottom: 5px; border: 1px solid rgba(255,255,255,0.1); }
        .btn-add { background: #FF7B00; color: #fff; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; transition: 0.2s; }
        .btn-add:hover { background: #e25822; }
        
        .chat-img-preview { max-width: 200px; max-height: 200px; border-radius: 10px; margin-top: 10px; display: block; cursor: pointer; }
        
        .context-menu {
            position: absolute;
            background: rgba(26, 21, 17, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 123, 0, 0.3);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            min-width: 150px;
            z-index: 1000;
            overflow: hidden;
        }
        .context-menu-item {
            padding: 12px 15px;
            color: #EAE4D9;
            cursor: pointer;
            transition: 0.2s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .context-menu-item:hover {
            background: rgba(255, 123, 0, 0.2);
            color: #FF7B00;
        }

        @media (max-width: 768px) {
            .chat-wrapper { flex-direction: column; padding: 10px; gap: 10px; overflow: hidden; height: calc(100vh - 20px); }
            .chat-sidebar { width: 100%; border-radius: 1rem; flex: 1; }
            .chat-main { width: 100%; border-radius: 1rem; position: absolute; top: 0; left: 0; height: 100%; z-index: 20; transform: translateX(100%); opacity: 1; display: none; }
            .chat-main.active { transform: translateX(0); display: flex; }
            .room-settings-panel { width: 100%; position: absolute; z-index: 30; }
            #mobile-back-btn { display: inline-block !important; margin-right: 15px; background: none; border: none; color: #FF7B00; font-size: 1.5rem; cursor: pointer; }
            .chat-input-area { flex-wrap: wrap; padding: 15px; }
            .chat-input { flex: 1 1 100%; min-width: 100%; margin-bottom: 10px; }
            .btn-send { flex: 1; }
            .btn-upload { width: 45px; height: 45px; font-size: 1.1rem; }
        }
    </style>
</head>
<body>

<div id="bg-grid-effect"></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const gridContainer = document.getElementById('bg-grid-effect');
        

        let gap = 48; 
        let coef = -4.8; 
        let shadows = [];
        for (let i = 1; i <= 4; i++) {
            shadows.push(`${i*gap}px 0 0 ${i*coef}px`);
            shadows.push(`${-i*gap}px 0 0 ${i*coef}px`);
            shadows.push(`0 ${-i*gap}px 0 ${i*coef}px`);
            shadows.push(`0 ${i*gap}px 0 ${i*coef}px`);
            for (let j = 1; j <= 4; j++) {
                let shrink = i*j*1.5*coef;
                shadows.push(`${i*gap}px ${j*gap}px 0 ${shrink}px`);
                shadows.push(`${i*gap}px ${-j*gap}px 0 ${shrink}px`);
                shadows.push(`${-i*gap}px ${j*gap}px 0 ${shrink}px`);
                shadows.push(`${-i*gap}px ${-j*gap}px 0 ${shrink}px`);
            }
        }
        
        document.documentElement.style.setProperty('--dynamic-shadow', shadows.join(', '));
        
        for(let i=0; i<400; i++) {
            const tile = document.createElement('div');
            tile.className = 'bg-grid-tile';
            gridContainer.appendChild(tile);
        }
    });
</script>

<div id="contextMenu" class="context-menu" style="display: none;">
    <div class="context-menu-item" id="ctx-profile">👤 View Profile</div>
    <div class="context-menu-item" id="ctx-add-group">👥 Add to Room</div>
    <div class="context-menu-item" id="ctx-remove" style="color: #ff4757;">❌ Remove Friend</div>
</div>

<div class="chat-wrapper">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h2>💬 <?php echo htmlspecialchars($lang['chat_title']); ?></h2>
            <div style="display: flex; gap: 10px; align-items: center;">
                <select id="userStatus" onchange="updateStatus(this.value)" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 5px; outline: none; cursor: pointer;">
                    <option value="online">🟢 <?php echo htmlspecialchars($lang['status'] ?? 'Online'); ?></option>
                    <option value="away">🟡 <?php echo htmlspecialchars($lang['status'] ?? 'Away'); ?></option>
                    <option value="dnd">🔴 <?php echo htmlspecialchars($lang['status'] ?? 'DND'); ?></option>
                    <option value="offline">⚫ <?php echo htmlspecialchars($lang['status'] ?? 'Offline'); ?></option>
                </select>
                <a href="hub.php" class="btn-back">← <?php echo htmlspecialchars($lang['hub_nav']); ?></a>
            </div>
        </div>
        
        <div class="chat-tabs" style="display: flex; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <button class="chat-tab active" onclick="switchTab('friends')" style="flex: 1; padding: 15px; background: none; border: none; color: #fff; border-bottom: 2px solid #FF7B00; cursor: pointer; font-weight: bold;"><?php echo htmlspecialchars($lang['chat_friends']); ?></button>
            <button class="chat-tab" onclick="switchTab('rooms')" style="flex: 1; padding: 15px; background: none; border: none; color: rgba(255,255,255,0.5); cursor: pointer; font-weight: bold;"><?php echo htmlspecialchars($lang['chat_rooms']); ?></button>
        </div>
        
        <div class="search-area">
            <input type="text" id="userSearch" class="search-box" placeholder="<?php echo htmlspecialchars($lang['chat_search']); ?>">
            <div id="searchResults"></div>
        </div>
        
        <div class="friends-list" id="friends-list">
            <p style="text-align:center; color: #636e72;"><?php echo htmlspecialchars($lang['loading']); ?></p>
        </div>
        <div class="rooms-list" id="rooms-list" style="display: none; flex: 1; overflow-y: auto; padding: 10px;">
            <button onclick="createRoom()" style="width: 100%; padding: 10px; background: linear-gradient(135deg, #FF9B44, #FF7B00); color: #fff; border: none; border-radius: 10px; margin-bottom: 15px; cursor: pointer; font-weight: bold; transition: 0.2s; box-shadow: 0 4px 15px rgba(255, 123, 0, 0.2);"><?php echo htmlspecialchars($lang['chat_new_room']); ?></button>
            <div id="rooms-container">
                <p style="text-align:center; color: #636e72;"><?php echo htmlspecialchars($lang['loading']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="chat-main">
        <div class="chat-header" id="chat-header" style="display: none; align-items: center;">
            <button id="mobile-back-btn" style="display: none;" onclick="closeMobileChat()">←</button>
            <h2><a href="#" id="chat-title-link">Select a friend</a></h2>
        </div>
        <div class="messages-container" id="messages-container">
            <div style="height:100%; display:flex; justify-content:center; align-items:center; color:#EAE4D9; font-size:1.2rem; opacity: 0.7;">
                <?php echo htmlspecialchars($lang['choose_friend_chat']); ?>
            </div>
        </div>
        <div class="chat-input-area" id="chat-input-area" style="display: none; position: relative;">
            <div id="multimedia-panel" style="display: none; position: absolute; bottom: 100%; left: 0; width: 300px; height: 250px; background: var(--bg-color); border: 1px solid var(--glass-border); border-radius: 10px; padding: 10px; z-index: 100; overflow-y: auto;">
                <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                    <button onclick="toggleMultimedia('emoji')" style="flex: 1; padding: 5px; background: #FF7B00; border: none; color: #fff; cursor: pointer;"><?php echo htmlspecialchars($lang['chat_emojis']); ?></button>
                    <button onclick="toggleMultimedia('gif')" style="flex: 1; padding: 5px; background: rgba(255,255,255,0.1); border: none; color: #fff; cursor: pointer;"><?php echo htmlspecialchars($lang['chat_gifs']); ?></button>
                </div>
                <div id="emoji-grid" style="display: flex; justify-content: center;">
                    <emoji-picker class="dark"></emoji-picker>
                </div>
                <div id="gif-search" style="display: none;">
                    <input type="text" placeholder="<?php echo htmlspecialchars($lang['chat_search_giphy']); ?>" style="width: 100%; padding: 5px; margin-bottom: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff;" onkeyup="searchGifs(this.value)">
                    <div id="gif-results" style="display: flex; flex-wrap: wrap; gap: 5px;"></div>
                </div>
            </div>
            
            <input type="file" id="imageInput" accept="image/*" style="display:none;" onchange="uploadImage()">
            <button class="btn-upload" onclick="document.getElementById('imageInput').click()" title="Attach Image">📎</button>
            <button class="btn-upload" onclick="toggleMultimediaPanel()" title="Emojis & GIFs">😊</button>
            
            <input type="text" id="chatInput" class="chat-input" placeholder="<?php echo htmlspecialchars($lang['chat_type_message']); ?>" onkeyup="typingIndicator()" onkeypress="if(event.key==='Enter') sendMessage()">
            <button class="btn-send" onclick="sendMessage()"><?php echo htmlspecialchars($lang['chat_send']); ?></button>
        </div>
    </div>
    
    <div class="room-settings-panel" id="roomSettingsPanel">
        <h3 style="margin-top: 0; color: #FF7B00; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 10px;"><?php echo htmlspecialchars($lang['chat_room_settings']); ?></h3>
        <div style="margin-bottom: 15px;">
            <label style="font-size: 0.8rem; color: rgba(255,255,255,0.7);"><?php echo htmlspecialchars($lang['chat_rename_room']); ?></label>
            <div style="display: flex; gap: 5px; margin-top: 5px;">
                <input type="text" id="roomNameInput" style="flex: 1; padding: 8px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: #fff; outline: none;">
                <button onclick="saveRoomName()" style="background: #FF7B00; color: #fff; border: none; border-radius: 10px; padding: 0 15px; cursor: pointer;"><?php echo htmlspecialchars($lang['chat_save']); ?></button>
            </div>
        </div>
        <div style="flex: 1; overflow-y: auto;">
            <label style="font-size: 0.8rem; color: rgba(255,255,255,0.7); display: block; margin-bottom: 10px;"><?php echo htmlspecialchars($lang['chat_members']); ?></label>
            <div id="roomMembersList"></div>
        </div>
    </div>
</div>

<script>
    let currentChatId = null;
    let currentChatType = 'user'; 
    let chatInterval = null;
    let editingMsgId = null;
    let pendingImageUrl = null;
    let typingTimeout = null;

    function switchTab(tab) {
        document.querySelectorAll('.chat-tab').forEach(t => {
            t.style.color = 'rgba(255,255,255,0.5)';
            t.style.borderBottom = 'none';
        });
        const activeTab = document.querySelector(`.chat-tab[onclick="switchTab('${tab}')"]`);
        activeTab.style.color = '#fff';
        activeTab.style.borderBottom = '2px solid #FF7B00';
        
        document.getElementById('friends-list').style.display = tab === 'friends' ? 'block' : 'none';
        document.getElementById('rooms-list').style.display = tab === 'rooms' ? 'block' : 'none';
        
        if (tab === 'rooms') loadRooms();
    }

    function toggleMultimediaPanel() {
        const panel = document.getElementById('multimedia-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    function toggleMultimedia(type) {
        document.getElementById('emoji-grid').style.display = type === 'emoji' ? 'grid' : 'none';
        document.getElementById('gif-search').style.display = type === 'gif' ? 'block' : 'none';
    }

    function insertEmoji(emoji) {
        const input = document.getElementById('chatInput');
        input.value += emoji;
        input.focus();
    }

    function searchGifs(query) {
        if (query.length < 2) return;
        fetch(`https://api.giphy.com/v1/gifs/search?api_key=dc6zaTOxFJmzC&q=${encodeURIComponent(query)}&limit=10`)
            .then(res => res.json())
            .then(data => {
                const results = document.getElementById('gif-results');
                results.innerHTML = '';
                data.data.forEach(gif => {
                    const img = document.createElement('img');
                    img.src = gif.images.fixed_height_small.url;
                    img.style.height = '60px';
                    img.style.cursor = 'pointer';
                    img.onclick = () => {
                        pendingImageUrl = gif.images.original.url;
                        document.getElementById('chatInput').value = '[GIF Attached] ' + document.getElementById('chatInput').value;
                        toggleMultimediaPanel();
                    };
                    results.appendChild(img);
                });
            });
    }

    function typingIndicator() {
        if (!currentChatId) return;
        clearTimeout(typingTimeout);
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'typing', target_id: currentChatId, type: currentChatType })
        });
        typingTimeout = setTimeout(() => {}, 2000);
    }

    function updateStatus(status) {
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'update_status', status: status })
        });
    }

    function loadRooms() {
        fetch('php/manage_chat.php?action=get_rooms')
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('rooms-container');
                if (data.length === 0) { list.innerHTML = `<p style="text-align:center; color: rgba(255,255,255,0.6); margin-top:20px;"><?php echo addslashes($lang['chat_no_rooms']); ?></p>`; return; }
                list.innerHTML = '';
                data.forEach(r => {
                    const div = document.createElement('div');
                    div.className = "friend-item";
                    div.onclick = () => openChat(r.id, r.name, 'room');
                    div.innerHTML = `<div class="friend-avatar" style="background: #e17055;">#</div>
                                     <span class="friend-name">${document.createTextNode(r.name).textContent}</span>`;
                    list.appendChild(div);
                });
            });
    }

    function createRoom() {
        const name = prompt("<?php echo addslashes($lang['chat_create_room_prompt']); ?>");
        if(!name) return;
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'create_room', name: name })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                loadRooms();
                openChat(data.id, data.name, 'room');
            } else {
                alert(data.error || "Failed to create room.");
            }
        });
    }

    function loadFriends() {
        fetch('php/manage_friends.php?action=get_friends')
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('friends-list');
                if (data.length === 0) { list.innerHTML = '<p style="text-align:center; color: rgba(255,255,255,0.6); margin-top:20px;">No friends yet. Add some above!</p>'; return; }
                list.innerHTML = '';
                data.forEach(f => {
                    const div = document.createElement('div');
                    div.className = "friend-item";
                    div.onclick = () => openChat(f.user_id, f.username);
                    div.oncontextmenu = (e) => {
                        e.preventDefault();
                        showContextMenu(e.pageX, e.pageY, f.user_id, f.username);
                    };

                    div.innerHTML = `<div class="avatar-wrapper">
                                        <div class="friend-avatar">${f.username[0].toUpperCase()}</div>
                                        <div class="status-dot status-${f.status || 'offline'}"></div>
                                     </div>
                                     <span class="friend-name">${document.createTextNode(f.username).textContent}</span>`;
                    list.appendChild(div);
                });
            });
    }


    let selectedUserId = null;                          // right click options for frends
    
    function resetContextMenu() {
        const menu = document.getElementById('contextMenu');
        menu.innerHTML = `
            <div class="context-menu-item" id="ctx-profile">👤 View Profile</div>
            <div class="context-menu-item" id="ctx-add-group">👥 Add to Room</div>
            <div class="context-menu-item" id="ctx-remove" style="color: #ff4757;">❌ Remove Friend</div>
        `;
    }

    function showContextMenu(x, y, userId, username) {
        selectedUserId = userId;
        resetContextMenu();
        
        const menu = document.getElementById('contextMenu');
        menu.style.display = 'flex';
        menu.style.flexDirection = 'column';
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
        
        document.getElementById('ctx-profile').onclick = () => {
            window.location.href = 'user_profile.php?id=' + userId;
        };
        
        document.getElementById('ctx-add-group').onclick = (e) => {
            e.stopPropagation();
            menu.innerHTML = '<div style="padding: 10px; color: rgba(255,255,255,0.7); font-size:0.8rem;">Loading rooms...</div>';
            fetch('php/manage_chat.php?action=get_rooms')
                .then(res => res.json())
                .then(rooms => {
                    menu.innerHTML = '<div style="padding: 10px; color: rgba(255,255,255,0.5); font-size:0.8rem; border-bottom: 1px solid rgba(255,255,255,0.1);">Select a room to invite ' + username + ':</div>';
                    if (rooms.length === 0) {
                        menu.innerHTML += '<div style="padding: 10px; color: #fff; font-size: 0.9rem;">No rooms available.</div>';
                    }
                    rooms.forEach(r => {
                        const div = document.createElement('div');
                        div.className = 'context-menu-item';
                        div.textContent = '# ' + r.name;
                        div.onclick = () => {
                            fetch('php/manage_chat.php', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({ action: 'add_member', room_id: r.id, target_user_id: userId })
                            }).then(res => res.json()).then(data => {
                                if(data.success) alert("Added " + username + " to room " + r.name);
                                else alert(data.error || "Failed to add.");
                                menu.style.display = 'none';
                            });
                        };
                        menu.appendChild(div);
                    });
                });
        };
        
        document.getElementById('ctx-remove').onclick = () => {
            if(confirm("Are you sure you want to remove " + username + "?")) {
                alert("Feature: Remove friend (Need backend implementation)");
            }
            menu.style.display = 'none';
        };
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.context-menu')) {
            document.getElementById('contextMenu').style.display = 'none';
        }
    });

    function closeMobileChat() {
        document.querySelector('.chat-main').classList.remove('active');
        document.getElementById('chat-header').style.display = 'none';
        document.getElementById('chat-input-area').style.display = 'none';
        currentChatId = null;
        if(chatInterval) clearInterval(chatInterval);
    }

    function openChat(targetId, targetName, type = 'user') {                   
        currentChatId = targetId;
        currentChatType = type;
        document.getElementById('chat-header').style.display = 'flex';
        document.getElementById('chat-input-area').style.display = 'flex';

        document.querySelector('.chat-main').classList.add('active');

        document.getElementById('chat-title-link').textContent = targetName;
        document.getElementById('chat-title-link').href = type === 'user' ? 'user_profile.php?id=' + targetId : '#';
        
        document.getElementById('roomSettingsPanel').classList.remove('active');
        
        document.getElementById('chat-title-link').onclick = (e) => {
            if(type === 'room') {
                e.preventDefault();
                toggleRoomSettings();
            }
        };
        
        editingMsgId = null;
        pendingImageUrl = null;
        document.getElementById('chatInput').value = '';
        
        loadMessages();
        clearInterval(chatInterval);
        chatInterval = setInterval(loadMessages, 3000);
    }
    
    function uploadImage() {
        const fileInput = document.getElementById('imageInput');
        if (!fileInput.files.length) return;
        
        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'chat');
        
        fetch('php/upload_image.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                pendingImageUrl = data.url;
                document.getElementById('chatInput').value = '[Attached Image] ' + document.getElementById('chatInput').value;
                document.getElementById('chatInput').focus();
            } else {
                alert(data.message || 'Image upload failed');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Upload error');
        });
        fileInput.value = ''; 
    }

    function sendMessage() {
        const input = document.getElementById('chatInput');
        const msg = input.value.trim();
        if ((!msg && !pendingImageUrl) || !currentChatId) return;
        
        if (editingMsgId) {
            fetch('php/manage_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'edit', msg_id: editingMsgId, message: msg })
            }).then(() => { 
                input.value = ''; 
                editingMsgId = null;
                pendingImageUrl = null;
                loadMessages(); 
            });
        } else {
            fetch('php/manage_chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'send', target_id: currentChatId, type: currentChatType, message: msg, image_url: pendingImageUrl })
            }).then(res => res.json()).then(data => {
                if(!data.success) {
                    alert("Failed to send: " + (data.error || "Unknown error"));
                    console.error(data);
                } else {
                    input.value = ''; 
                    pendingImageUrl = null;
                    loadMessages(); 
                }
            });
        }
    }

    function deleteMessage(msgId) {
        if(!confirm("Delete this message?")) return;
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'delete', msg_id: msgId })
        }).then(() => loadMessages());
    }

    function startEdit(msgId, currentText) {
        editingMsgId = msgId;
        const input = document.getElementById('chatInput');
        input.value = currentText;
        input.focus();
    }

    function loadMessages() {
        if (!currentChatId) return;
        fetch(`php/manage_chat.php?action=get&target_id=${currentChatId}&type=${currentChatType}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('messages-container');
                const wasScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;

                const typingHTML = data.is_typing ? `<div class="msg msg-them" style="opacity: 0.7; font-style: italic;">Typing...</div>` : '';
                
                let html = '';
                data.messages.forEach(m => {
                    const isMe = m.sender_id == <?php echo $user_id; ?>;
                    
                    let contentHTML = `<span class="msg-text">${m.message}</span>`;
                    
                    if (m.link_preview_json) {
                        try {
                            const preview = JSON.parse(m.link_preview_json);
                            contentHTML += `
                                <a href="${preview.url}" target="_blank" style="display:block; margin-top:10px; background:rgba(0,0,0,0.2); border-radius:10px; overflow:hidden; text-decoration:none; color:inherit; border:1px solid rgba(255,255,255,0.1);">
                                    ${preview.image ? `<img src="${preview.image}" style="width:100%; height:120px; object-fit:cover;">` : ''}
                                    <div style="padding:10px;">
                                        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:5px;">${preview.title}</div>
                                        <div style="font-size:0.8rem; opacity:0.7;">${preview.description}</div>
                                    </div>
                                </a>
                            `;
                        } catch(e) {}
                    }
                    
                    if (m.image_url) {
                        contentHTML += `<br><img src="${m.image_url}" class="chat-img-preview" alt="Attachment" onclick="window.open(this.src)">`;
                    }
                    if (m.is_edited == 1) {
                        contentHTML += `<span class="edited-tag">(edited)</span>`;
                    }
                    
                    let indicators = '';
                    if (isMe && currentChatType === 'user') {
                        if (m.is_read == 1) indicators = ' <span style="font-size:0.7rem; color:#a29bfe;">✓✓</span>';
                        else if (m.is_delivered == 1) indicators = ' <span style="font-size:0.7rem; opacity:0.7;">✓✓</span>';
                        else indicators = ' <span style="font-size:0.7rem; opacity:0.5;">✓</span>';
                    }
                    
                    let actionsHTML = '';
                    if (isMe) {
                        actionsHTML = `
                            <div class="msg-actions">
                                <button class="btn-msg-action" onclick="startEdit(${m.id}, '${m.message.replace(/'/g, "\\'")}')">Edit</button>
                                <button class="btn-msg-action" onclick="deleteMessage(${m.id})">Delete</button>
                            </div>
                        `;
                    }
                    
                    html += `
                        <div class="msg ${isMe ? 'msg-me' : 'msg-them'}">
                            ${contentHTML}${indicators}
                            ${actionsHTML}
                        </div>
                    `;
                });
                
                container.innerHTML = html + typingHTML;
                
                if(wasScrolledToBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            });
    }

    document.getElementById('userSearch').addEventListener('input', function() {
        const query = this.value;
        if(query.length < 2) { document.getElementById('searchResults').innerHTML = ''; return; }       // search bar with inf
        fetch(`php/search_users.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(users => {
                const results = document.getElementById('searchResults');
                results.innerHTML = '';
                users.forEach(u => {
                    const div = document.createElement('div');
                    div.className = 'user-result-item';
                    div.innerHTML = `
                        <a href="user_profile.php?id=${u.id}" style="text-decoration:none; color:inherit; font-weight:bold; flex:1;">${document.createTextNode(u.username).textContent}</a>
                        <button class="btn-add" onclick="sendFriendRequest(${u.id})">Add</button>
                    `;
                    results.appendChild(div);
                });
            });
    });

    function sendFriendRequest(targetId) {
        fetch('php/manage_friends.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'send_request', target_id: targetId })
        }).then(res => res.json()).then(data => alert(data.message));
    }

    function toggleRoomSettings() {
        const panel = document.getElementById('roomSettingsPanel');
        if (panel.classList.contains('active')) {
            panel.classList.remove('active');
        } else {
            panel.classList.add('active');
            loadRoomSettings();
        }
    }

    function loadRoomSettings() {
        if(currentChatType !== 'room' || !currentChatId) return;
        fetch('php/manage_chat.php?action=get_room_details&room_id=' + currentChatId)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('roomNameInput').value = data.room.name;
                    const list = document.getElementById('roomMembersList');
                    list.innerHTML = '';
                    data.members.forEach(m => {
                        const div = document.createElement('div');
                        div.style.display = 'flex';                          // room settings logic rename delete osv
                        div.style.justifyContent = 'space-between';
                        div.style.background = 'rgba(255,255,255,0.1)';
                        div.style.padding = '8px';
                        div.style.borderRadius = '8px';
                        div.style.marginBottom = '5px';
                        div.style.alignItems = 'center';
                        
                        const nameSpan = document.createElement('span');
                        nameSpan.textContent = m.username;
                        div.appendChild(nameSpan);
                        
                        if (data.room.created_by == <?php echo $user_id; ?> && m.id != <?php echo $user_id; ?>) {
                            const btn = document.createElement('button');
                            btn.textContent = 'Remove';
                            btn.style.background = '#ff4757';
                            btn.style.color = '#fff';
                            btn.style.border = 'none';
                            btn.style.borderRadius = '5px';
                            btn.style.padding = '3px 8px';
                            btn.style.cursor = 'pointer';
                            btn.onclick = () => removeRoomMember(m.id);
                            div.appendChild(btn);
                        }
                        
                        list.appendChild(div);
                    });
                    
                    if (data.room.created_by == <?php echo $user_id; ?>) {
                        const deleteBtn = document.createElement('button');
                        deleteBtn.textContent = 'Delete Room';
                        deleteBtn.style.width = '100%';
                        deleteBtn.style.background = '#ff4757';
                        deleteBtn.style.color = '#fff';
                        deleteBtn.style.border = 'none';
                        deleteBtn.style.borderRadius = '10px';
                        deleteBtn.style.padding = '10px';
                        deleteBtn.style.marginTop = '20px';
                        deleteBtn.style.cursor = 'pointer';
                        deleteBtn.style.fontWeight = 'bold';
                        deleteBtn.onclick = deleteRoom;
                        list.appendChild(deleteBtn);
                    }
                }
            });
    }

    function deleteRoom() {
        if(!confirm("Are you sure you want to permanently delete this room?")) return;
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'delete_room', room_id: currentChatId })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                document.getElementById('roomSettingsPanel').classList.remove('active');
                document.querySelector('.chat-main').classList.remove('active');
                document.getElementById('chat-header').style.display = 'none';
                document.getElementById('chat-input-area').style.display = 'none';
                document.getElementById('messages-container').innerHTML = '<p class="empty-chat-msg">Select a friend or room to start chatting.</p>';
                loadRooms();
                alert("Room deleted.");
            } else {
                alert(data.error || "Failed to delete room.");
            }
        });
    }

    function saveRoomName() {
        const name = document.getElementById('roomNameInput').value;
        if(!name) return;
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'rename_room', room_id: currentChatId, name: name })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                document.getElementById('chat-title-link').textContent = name;
                loadRooms();
                alert("Room renamed!");
            } else {
                alert(data.error || "Failed to rename room.");
            }
        });
    }

    function removeRoomMember(targetUserId) {
        if(!confirm("Remove this member?")) return;
        fetch('php/manage_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'remove_member', room_id: currentChatId, target_user_id: targetUserId })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                loadRoomSettings();
            } else {
                alert(data.error || "Failed to remove member.");
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadFriends();
        document.querySelector('emoji-picker').addEventListener('emoji-click', event => {
            insertEmoji(event.detail.unicode);
        });

        const chatPanel = document.querySelector('.chat-main');    // from the old site the panel 
        chatPanel.addEventListener('mousemove', e => {
            if(!chatPanel.classList.contains('active')) return;
            const rect = chatPanel.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const rY = ((x / rect.width) - 0.5) * 6;
            const rX = ((y / rect.height) - 0.5) * -6;
            chatPanel.style.transform = `scale(1.01) rotateX(${rX}deg) rotateY(${rY}deg)`;
        });
        chatPanel.addEventListener('mouseleave', () => {
            if(chatPanel.classList.contains('active')) {
                chatPanel.style.transform = `scale(1) rotateX(0) rotateY(0)`;
            }
        });
    });
</script>
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>

<?php include_once __DIR__ . "/Rapport/ui.php"; ?>
</body>
</html>
