document.addEventListener("DOMContentLoaded", () => {
    const chatCircle = document.querySelector('.chat-circle');
    const chatPanel = document.querySelector('.chat-panel');
    const closeChat = document.querySelector('.close-chat');
    const chatBody = document.querySelector('.chat-body');

 
    chatCircle.addEventListener('click', () => {
        chatPanel.style.display = 'flex'; 
        loadChatFriends(); 
    });

    closeChat.addEventListener('click', () => {
        chatPanel.style.display = 'none';
    });

    
    function loadChatFriends() {
        fetch("php/manage_friends.php?action=get_friends")
            .then(res => res.json())
            .then(friends => {
                chatBody.innerHTML = ""; 

                if (friends.length === 0) {
                    chatBody.innerHTML = "<p style='padding:15px; color:#666;'>You haven't added any friends yet.</p>";
                    return;
                }

                friends.forEach(friend => {
                    const div = document.createElement("div");
                    div.className = "chat-friend-item";
                    
                    
                    const avatar = friend.profile_pic ? friend.profile_pic : 'assets/default-avatar.png';
                    
                    div.innerHTML = `
                        <img src="${avatar}" alt="">
                        <span>${friend.username}</span>
                    `;

                    
                    div.onclick = () => openConversation(friend.user_id, friend.username);
                    
                    chatBody.appendChild(div);
                });
            });
    }
});