let currentReportId = null;

function toggleRapportPopup() {
    const popup = document.getElementById('rapportPopup');
    if (popup.style.display === 'none' || popup.style.display === '') {
        popup.style.display = 'flex';
        openRapportMenu();
    } else {
        closeRapportPopup();
    }
}

function closeRapportPopup() {
    document.getElementById('rapportPopup').style.display = 'none';
}

function hideAllViews() {
    document.getElementById('rapport-menu-view').style.display = 'none';
    document.getElementById('rapport-submit-view').style.display = 'none';
    document.getElementById('rapport-list-view').style.display = 'none';
    document.getElementById('rapport-chat-view').style.display = 'none';
}

function openRapportMenu() {
    hideAllViews();
    document.getElementById('rapport-menu-view').style.display = 'block';
}

function showRapportSubmit() {
    hideAllViews();
    document.getElementById('rapport-submit-view').style.display = 'block';
    document.getElementById('rapportStatusMessage').innerText = '';
}

function showRapportList() {
    hideAllViews();
    document.getElementById('rapport-list-view').style.display = 'block';
    loadMyReports();
}

function submitRapport(e) {
    e.preventDefault();
    const title = document.getElementById('rapportTitle').value;
    const desc = document.getElementById('rapportDesc').value;
    const btn = document.querySelector('.btn-rapport-send');
    const msgBox = document.getElementById('rapportStatusMessage');

    btn.disabled = true;
    btn.innerText = 'Sending...';

    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', desc);

    fetch('/saitut/Rapport/submit.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                msgBox.style.color = '#76c900';
                msgBox.innerText = 'Problem reported successfully!';
                document.getElementById('rapportForm').reset();
                setTimeout(() => { showRapportList(); }, 1500);
            } else {
                msgBox.style.color = '#ff4757';
                msgBox.innerText = 'Error: ' + data.message;
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Send';
        });
}

function loadMyReports() {
    const container = document.getElementById('rapport-list-container');
    container.innerHTML = '<p style="text-align:center;">Loading...</p>';

    fetch('/saitut/Rapport/get_my_reports.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.reports.length === 0) {
                    container.innerHTML = '<p style="text-align:center; opacity:0.7;">No reports yet.</p>';
                    return;
                }
                container.innerHTML = '';
                data.reports.forEach(r => {
                    const statusCls = r.status === 'pending' ? 'status-pending' : 'status-resolved';
                    const statusTxt = r.status === 'pending' ? 'Pending' : 'Resolved';
                    const item = document.createElement('div');
                    item.className = 'rapport-list-item';
                    item.onclick = () => openChatView(r.id);
                    item.innerHTML = `
                    <div class="rapport-list-title">${r.title}</div>
                    <div class="rapport-list-status ${statusCls}">${statusTxt}</div>
                `;
                    container.appendChild(item);
                });
            }
        });
}

function openChatView(id) {
    hideAllViews();
    document.getElementById('rapport-chat-view').style.display = 'block';
    currentReportId = id;
    loadChatMessages();
}

function loadChatMessages() {
    const msgContainer = document.getElementById('rapport-chat-messages');
    msgContainer.innerHTML = '<p style="text-align:center;">Loading chat...</p>';

    fetch('/saitut/Rapport/get_report.php?id=' + currentReportId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('chat-report-title').innerText = data.report.title;
                document.getElementById('chat-report-desc').innerText = data.report.description;

                msgContainer.innerHTML = '';
                data.messages.forEach(m => {
                    const isMe = (m.user_id == data.current_user);
                    const bubbleCls = isMe ? 'chat-bubble-me' : 'chat-bubble-other';
                    let sender = isMe ? 'You' : m.username;
                    if (m.is_admin == 1 && !isMe) sender += ' (Admin)';

                    const b = document.createElement('div');
                    b.className = `chat-bubble ${bubbleCls}`;
                    b.innerHTML = `<div class="chat-sender-name">${sender}</div><div>${m.message}</div>`;
                    msgContainer.appendChild(b);
                });

                msgContainer.scrollTop = msgContainer.scrollHeight;

                const inputArea = document.getElementById('rapport-chat-input-area');
                const closeBtn = document.querySelector('.btn-rapport-close-issue');
                if (data.report.status === 'resolved') {
                    inputArea.style.display = 'none';
                    closeBtn.style.display = 'none';
                } else {
                    inputArea.style.display = 'flex';
                    closeBtn.style.display = 'inline-block';
                }
            }
        });
}

function sendRapportMessage() {
    const inp = document.getElementById('rapport-msg-input');
    const msg = inp.value.trim();
    if (msg === "") return;

    const formData = new FormData();
    formData.append('report_id', currentReportId);
    formData.append('message', msg);

    inp.value = '';

    fetch('/saitut/Rapport/submit_message.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadChatMessages();
            } else {
                alert(data.message);
            }
        });
}

function closeRapportIssue() {
    if (!confirm("Are you sure you want to mark this report as resolved? You wont be able to send more messages.")) return;

    const formData = new FormData();
    formData.append('report_id', currentReportId);
    fetch('/saitut/Rapport/close_report.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadChatMessages();
            }
        });
}
