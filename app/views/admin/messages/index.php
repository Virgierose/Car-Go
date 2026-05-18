<?php
$pageTitle = 'Messages';
$activePage = 'messages';
ob_start();
?>

<div class="messages-layout">
    <!-- MESSAGE LIST -->
    <div class="message-list-panel">
        <div class="panel-header">
            <span class="panel-title">Inbox</span>
            <div class="inbox-tabs">
                <button class="inbox-tab inbox-tab-active" onclick="filterMsg('all', this)">All</button>
                <button class="inbox-tab" onclick="filterMsg('unread', this)">Unread</button>
            </div>
        </div>
        <div class="message-list" id="messageList">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $m): ?>
            <a href="#" onclick="loadMessage(<?= $m['message_id'] ?>, event)"
               class="message-item <?= !$m['is_read'] ? 'unread' : '' ?> <?= (isset($_GET['id']) && $_GET['id'] == $m['message_id']) ? 'active' : '' ?>"
               data-id="<?= $m['message_id'] ?>"
               data-read="<?= $m['is_read'] ? '1' : '0' ?>">
                <div class="msg-avatar"><?= strtoupper(substr($m['sender_name'] ?? 'U', 0, 1)) ?></div>
                <div class="msg-info">
                    <div class="msg-top">
                        <span class="msg-sender"><?= htmlspecialchars($m['sender_name'] ?? 'Unknown') ?></span>
                        <span class="msg-time"><?= date('M d', strtotime($m['created_at'])) ?></span>
                    </div>
                    <div class="msg-subject"><?= htmlspecialchars($m['subject'] ?? '(No subject)') ?></div>
                    <div class="msg-preview"><?= htmlspecialchars(substr($m['message'] ?? '', 0, 60)) ?>...</div>
                </div>
                <?php if (!$m['is_read']): ?><span class="unread-dot"></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-inbox">
                <i class="fas fa-inbox"></i>
                <p>No messages yet</p>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- MESSAGE VIEWER -->
    <div class="message-view-panel">
    <?php if (!empty($currentMessage)): ?>
        <div class="msg-view-header">
            <div>
                <div class="msg-view-subject"><?= htmlspecialchars($currentMessage['subject'] ?? '(No subject)') ?></div>
                <div class="msg-view-meta">
                    From: <strong><?= htmlspecialchars($currentMessage['sender_name'] ?? '') ?></strong>
                    &lt;<?= htmlspecialchars($currentMessage['email'] ?? '') ?>&gt;
                    &nbsp;·&nbsp; <?= $currentMessage['created_at'] ?>
                </div>
            </div>
            <div class="msg-view-actions">
                <button type="button" class="btn-action btn-reply" onclick="toggleReplyForm()" title="Reply"><i class="fas fa-reply"></i></button>
                <button type="button" class="btn-action btn-delete" onclick="deleteMessage(<?= $currentMessage['message_id'] ?>)" title="Delete"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="msg-view-body">
            <?= nl2br(htmlspecialchars($currentMessage['message'] ?? '')) ?>
        </div>

        <!-- REPLY FORM -->
        <div id="replyForm" class="msg-reply-form" style="display:none;">
            <form method="POST" action="<?= BASE_URL ?>?page=admin-message-reply" onsubmit="submitReply(event, <?= $currentMessage['message_id'] ?>)">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?= $currentMessage['message_id'] ?>">
                <div class="form-group">
                    <label class="form-label">Your Reply</label>
                    <textarea name="reply_message" class="reply-textarea" placeholder="Type your reply here..." required></textarea>
                </div>
                <div class="reply-actions">
                    <button type="submit" class="btn-reply-send"><i class="fas fa-paper-plane"></i> Send Reply</button>
                    <button type="button" class="btn-reply-cancel" onclick="toggleReplyForm()"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="msg-empty-state">
            <i class="fas fa-envelope-open-text"></i>
            <p>Select a message to read</p>
        </div>
    <?php endif; ?>
    </div>

</div>

<style>
.messages-layout { display:grid; grid-template-columns:340px 1fr; gap:16px; height:calc(100vh - 240px); min-height:500px; }
.message-list-panel { background:var(--black-card); border:1px solid var(--border); border-radius:12px; display:flex; flex-direction:column; overflow:hidden; }
.panel-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
.panel-title { color:var(--white); font-weight:700; font-size:.9rem; letter-spacing:.06em; text-transform:uppercase; }
.inbox-tabs { display:flex; gap:4px; }
.inbox-tab { background:transparent; border:none; color:var(--silver); font-size:.78rem; padding:4px 10px; border-radius:6px; cursor:pointer; transition:all .2s; }
.inbox-tab:hover { color:var(--white); background:rgba(255,255,255,.07); }
.inbox-tab-active { background:rgba(231,76,60,.15); color:var(--red); }
.message-list { overflow-y:auto; flex:1; }
.message-item { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border); text-decoration:none; transition:background .15s; position:relative; cursor:pointer; }
.message-item:hover { background:rgba(255,255,255,.04); }
.message-item.active { background:rgba(231,76,60,.08); border-left:3px solid var(--red); }
.message-item.unread .msg-sender { color:var(--white); }
.msg-avatar { width:38px; height:38px; border-radius:50%; background:var(--red); color:var(--white); display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; }
.message-item:not(.unread) .msg-avatar { background:rgba(255,255,255,.1); color:var(--silver); }
.msg-info { flex:1; min-width:0; }
.msg-top { display:flex; justify-content:space-between; margin-bottom:3px; }
.msg-sender { color:var(--silver); font-size:.85rem; font-weight:600; }
.msg-time { color:var(--silver); font-size:.75rem; opacity:.7; }
.msg-subject { color:var(--white); font-size:.82rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:2px; }
.message-item:not(.unread) .msg-subject { color:var(--silver); font-weight:400; }
.msg-preview { color:var(--silver); font-size:.76rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; opacity:.7; }
.unread-dot { width:8px; height:8px; border-radius:50%; background:var(--red); flex-shrink:0; margin-top:6px; }
.message-view-panel { background:var(--black-card); border:1px solid var(--border); border-radius:12px; display:flex; flex-direction:column; overflow:hidden; }
.msg-view-header { padding:20px 24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:flex-start; }
.msg-view-subject { color:var(--white); font-size:1.1rem; font-weight:700; margin-bottom:6px; }
.msg-view-meta { color:var(--silver); font-size:.82rem; }
.msg-view-body { padding:24px; color:var(--white); font-size:.9rem; line-height:1.7; flex:1; overflow-y:auto; }
.msg-view-contact { padding:12px 24px; border-top:1px solid var(--border); color:var(--silver); font-size:.85rem; }
.msg-empty-state { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:var(--silver); gap:12px; }
.msg-empty-state i { font-size:3rem; opacity:.2; }
.msg-empty-state p { font-size:.9rem; opacity:.5; }
.empty-inbox { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:48px; color:var(--silver); gap:12px; }
.empty-inbox i { font-size:2.5rem; opacity:.2; }
.empty-inbox p { font-size:.9rem; opacity:.5; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; }
.btn-delete { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-delete:hover { background:#c0392b; color:var(--white); }
.msg-view-actions { display:flex; gap:8px; }
.btn-reply { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-reply:hover { background:rgba(76,175,80,.3); color:#4ade80; }
.msg-reply-form { padding:20px 24px; border-top:1px solid var(--border); background:rgba(0,0,0,.2); }
.form-group { display:flex; flex-direction:column; gap:8px; margin-bottom:12px; }
.form-label { font-size:.75rem; letter-spacing:1px; text-transform:uppercase; color:var(--silver); font-weight:600; }
.reply-textarea { background:rgba(0,0,0,.4); border:1px solid var(--border); border-radius:6px; padding:10px 12px; color:var(--white); font-family:'Barlow', sans-serif; font-size:.85rem; resize:vertical; height:100px; outline:none; transition:border-color .2s; }
.reply-textarea:focus { border-color:var(--red); }
.reply-textarea::placeholder { color:var(--silver); opacity:.5; }
.reply-actions { display:flex; gap:8px; }
.btn-reply-send { display:inline-flex; align-items:center; gap:6px; background:var(--red); color:var(--white); padding:8px 16px; border:none; border-radius:6px; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .2s; }
.btn-reply-send:hover { background:#a00816; }
.btn-reply-cancel { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,.07); color:var(--silver); padding:8px 16px; border:none; border-radius:6px; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .2s; }
.btn-reply-cancel:hover { background:rgba(255,255,255,.12); color:var(--white); }
</style>
<script>
function loadMessage(msgId, event) {
    event.preventDefault();
    
    // Mark the item as active
    document.querySelectorAll('.message-item').forEach(item => item.classList.remove('active'));
    document.querySelector('[data-id="' + msgId + '"]').classList.add('active');
    
    // Fetch message via AJAX
    fetch('<?= BASE_URL ?>?page=admin-message-read&id=' + msgId)
        .then(res => res.json())
        .then(msg => {
            const viewPanel = document.querySelector('.message-view-panel');
            const messageHTML = `
                <div class="msg-view-header">
                    <div>
                        <div class="msg-view-subject">${escapeHtml(msg.subject || '(No subject)')}</div>
                        <div class="msg-view-meta">
                            From: <strong>${escapeHtml(msg.sender_name || '')}</strong>
                            &lt;${escapeHtml(msg.email || '')}&gt;
                            &nbsp;·&nbsp; ${msg.created_at}
                        </div>
                    </div>
                    <div class="msg-view-actions">
                        <button type="button" class="btn-action btn-reply" onclick="toggleReplyForm()" title="Reply"><i class="fas fa-reply"></i></button>
                        <button type="button" class="btn-action btn-delete" onclick="deleteMessage(${msg.message_id})" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="msg-view-body">${msg.message.replace(/\n/g, '<br>')}</div>
                <div id="replyForm" class="msg-reply-form" style="display:none;">
                    <form method="POST" action="<?= BASE_URL ?>?page=admin-message-reply" onsubmit="submitReply(event, ${msg.message_id})">
                        <div class="form-group">
                            <label class="form-label">Your Reply</label>
                            <textarea name="reply_message" class="reply-textarea" placeholder="Type your reply here..." required></textarea>
                        </div>
                        <div class="reply-actions">
                            <button type="submit" class="btn-reply-send"><i class="fas fa-paper-plane"></i> Send Reply</button>
                            <button type="button" class="btn-reply-cancel" onclick="toggleReplyForm()"><i class="fas fa-times"></i> Cancel</button>
                        </div>
                    </form>
                </div>
            `;
            viewPanel.innerHTML = messageHTML;
            document.getElementById('replyForm').style.display = 'none';
        })
        .catch(err => {
            console.error('Error loading message:', err);
            alert('Failed to load message');
        });
}

function toggleReplyForm() {
    const form = document.getElementById('replyForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function deleteMessage(msgId) {
    if (!confirm('Delete this message?')) return;
    
    fetch('<?= BASE_URL ?>?page=admin-message-delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + msgId
    })
    .then(() => {
        location.reload();
    })
    .catch(err => {
        console.error('Error deleting message:', err);
        alert('Failed to delete message');
    });
}

function submitReply(event, msgId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('<?= BASE_URL ?>?page=admin-message-reply', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        alert('Reply sent successfully!');
        toggleReplyForm();
    })
    .catch(err => {
        console.error('Error sending reply:', err);
        alert('Failed to send reply');
    });
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin_layout.php';
?>