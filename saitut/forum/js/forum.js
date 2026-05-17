function vote(postId, voteValue) {                         // voting
    fetch('php/vote.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ post_id: postId, vote: voteValue })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('score-' + postId).innerText = data.new_score;

                const postEl = document.getElementById('post-' + postId);
                if (postEl) {
                    const btns = postEl.querySelectorAll('.vote-btn');
                    btns.forEach(btn => btn.classList.remove('active-up', 'active-down'));
                    if (data.current_vote === 1) btns[0].classList.add('active-up');
                    if (data.current_vote === -1) btns[1].classList.add('active-down');
                }
            }
        });
}

function submitReply() {
    const body = document.getElementById('reply-body').value.trim();
    if (!body) {
        alert('Please write something before posting.');
        return;
    }

    fetch('php/create_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ thread_id: threadId, body: body })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
}

let searchTimeout = null;
function forumSearch(query) {
    const resultsDiv = document.getElementById('search-results');

    clearTimeout(searchTimeout);

    if (query.length < 2) {
        resultsDiv.style.display = 'none';
        resultsDiv.innerHTML = '';
        return;
    }                                                                                  //searching 

    searchTimeout = setTimeout(() => {
        fetch('php/search.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                resultsDiv.innerHTML = '';
                if (data.length === 0) {
                    resultsDiv.innerHTML = '<div class="empty-state" style="padding: 15px;">No results found.</div>';
                    resultsDiv.style.display = 'block';
                    return;
                }
                data.forEach(item => {
                    resultsDiv.innerHTML += `
                        <a href="thread.php?id=${item.thread_id}" class="thread-card" style="margin-bottom: 8px;">
                            <div class="thread-info">
                                <div class="thread-title">${escapeHtml(item.title)}</div>
                                <div class="thread-meta">
                                    <span>by ${escapeHtml(item.username)}</span>
                                    <span>in ${escapeHtml(item.category_name)}</span>
                                </div>
                            </div>
                        </a>`;
                });
                resultsDiv.style.display = 'block';
            });
    }, 350);
}

function toggleNotifications() {
    const dropdown = document.getElementById('notif-dropdown');
    const isOpen = dropdown.classList.contains('open');

    if (isOpen) {
        dropdown.classList.remove('open');
        return;
    }

    fetch('php/get_notifications.php')
        .then(res => res.json())
        .then(data => {
            dropdown.innerHTML = '';
            if (data.length === 0) {
                dropdown.innerHTML = '<div class="notif-empty">No new notifications</div>';
            } else {
                data.forEach(n => {
                    let icon = '💬';
                    if (n.type === 'vote') icon = '⬆️';
                    if (n.type === 'mention') icon = '@';
                    if (n.type === 'category_approved') icon = '✅';
                    if (n.type === 'category_rejected') icon = '❌';

                    const link = n.thread_id ? `thread.php?id=${n.thread_id}` : '#';
                    dropdown.innerHTML += `<a href="${link}" class="notif-item">${icon} ${escapeHtml(n.message || n.type)}</a>`;
                });
            }
            dropdown.classList.add('open');
            const badge = document.getElementById('notif-badge');
            if (badge) badge.style.display = 'none';
        });
}

document.addEventListener('click', function (e) {
    const dropdown = document.getElementById('notif-dropdown');
    const wrapper = document.querySelector('.notif-wrapper');
    if (dropdown && wrapper && !wrapper.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});

function openReportModal(postId) {
    document.getElementById('report-post-id').value = postId;
    document.getElementById('report-reason').value = '';
    document.getElementById('reportModal').classList.add('open');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.remove('open');
}

function submitReport() {
    const postId = document.getElementById('report-post-id').value;
    const reason = document.getElementById('report-reason').value.trim();
    if (!reason) {
        alert('Please provide a reason.');
        return;
    }

    fetch('php/report_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ post_id: postId, reason: reason })
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            closeReportModal();
        });
}

function modAction(action, targetId, targetType) {

    if (action.includes('delete')) {
        if (!confirm('Are you sure you want to delete this?')) return;
    }

    fetch('php/mod_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: action, target_id: targetId, target_type: targetType })
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
}

function editPost(postId) {
    const bodyEl = document.getElementById('post-body-' + postId);
    const currentText = bodyEl.innerText;
    const newText = prompt('Edit post:', currentText);
    if (newText === null || newText.trim() === '') return;

    fetch('php/mod_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'edit_post', target_id: postId, new_body: newText.trim() })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message);
        });
}

function showAdminTab(tabName, btn) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));      //admin tabs with hide and show function for the sections  
    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    btn.classList.add('active');
}

function adminAction(action, id, extraId) {
    const noteEl = document.getElementById('note-' + id);
    const note = noteEl ? noteEl.value : '';

    const body = { action: action, id: id, admin_note: note };          //admin decisons with admin response and post id 
    if (extraId) body.post_id = extraId;

    fetch('php/admin_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                const el = document.getElementById('request-' + id) || document.getElementById('report-' + id);
                if (el) el.style.display = 'none';
            }
        });
}

function editCategory(catId) {
    const name = document.getElementById('cat-name-' + catId).value;
    const icon = document.getElementById('cat-icon-' + catId).value;
    const order = document.getElementById('cat-order-' + catId).value;

    fetch('php/admin_actions.php', {
        method: 'POST',                                                          //edit, categ, sort_order
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'edit_category', id: catId, name: name, icon: icon, sort_order: order })
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
        });
}

function deleteCategory(catId) {
    if (!confirm('Delete this category and ALL its threads? This cannot be undone!')) return;

    fetch('php/admin_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_category', id: catId })
    })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                document.getElementById('cat-' + catId).style.display = 'none';
            }
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
