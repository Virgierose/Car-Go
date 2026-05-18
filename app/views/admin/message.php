<?php
// ── Mark as read if requested ─────────────────────────────────────────────────
if (isset($_GET['read'])) {
    $db   = Database::getInstance();
    $rid  = (int) $_GET['read'];
    $stmt = $db->prepare("UPDATE tbl_contact SET is_read = 1 WHERE contact_id = ?");
    $stmt->bind_param('i', $rid);
    $stmt->execute();
    header('Location: ' . BASE_URL . '?page=admin&section=messages');
    exit;
}

// ── Delete message if requested ───────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $db   = Database::getInstance();
    $did  = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM tbl_contact WHERE contact_id = ?");
    $stmt->bind_param('i', $did);
    $stmt->execute();
    header('Location: ' . BASE_URL . '?page=admin&section=messages');
    exit;
}

// ── Fetch all messages ────────────────────────────────────────────────────────
$db       = Database::getInstance();
$result   = $db->query("SELECT * FROM tbl_contact ORDER BY contact_dateAdded DESC");
$messages = $result->fetch_all(MYSQLI_ASSOC);
$unread   = array_filter($messages, fn($m) => !$m['is_read']);
?>

<style>
  .msg-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
  }
  .msg-title { font-size: 22px; font-weight: 700; color: var(--white); }
  .msg-badge {
    background: var(--red); color: #fff;
    font-size: 12px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; margin-left: 10px;
  }

  .msg-table-wrap {
    background: var(--black2); border: 1px solid var(--grey);
    border-radius: 12px; overflow: hidden;
  }
  .msg-table { width: 100%; border-collapse: collapse; font-size: 14px; }
  .msg-table thead tr { background: var(--black3); border-bottom: 1px solid var(--grey); }
  .msg-table th {
    padding: 13px 16px; text-align: left;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
    color: var(--muted); font-weight: 600;
  }
  .msg-table td { padding: 13px 16px; color: var(--muted); vertical-align: top; border-bottom: 1px solid var(--grey); }
  .msg-table tr:last-child td { border-bottom: none; }
  .msg-table tr.unread td { color: var(--white); background: rgba(224,27,27,.04); }
  .msg-table tr:hover td { background: rgba(255,255,255,.03); }

  .msg-name  { color: var(--white); font-weight: 600; }
  .msg-subj  { font-family: 'Barlow Condensed', sans-serif; font-size: 13px; letter-spacing: .5px; color: var(--red); }
  .msg-text  { max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .msg-date  { white-space: nowrap; font-size: 12px; }

  .badge-unread {
    display: inline-block; padding: 2px 8px; background: rgba(224,27,27,.15);
    border: 1px solid rgba(224,27,27,.35); border-radius: 20px;
    color: var(--red); font-size: 11px; font-weight: 700; margin-left: 6px;
  }

  .msg-actions { display: flex; gap: 8px; }
  .btn-sm {
    padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    border: 1px solid var(--grey); background: var(--black3); color: var(--muted);
    text-decoration: none; cursor: pointer; transition: border-color .2s, color .2s;
    font-family: 'Barlow Condensed', sans-serif; letter-spacing: .5px;
  }
  .btn-sm:hover { border-color: var(--red); color: var(--red); }
  .btn-sm.danger:hover { border-color: #e03e3e; color: #e03e3e; }

  /* Modal */
  .msg-modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,.75); align-items: center; justify-content: center;
  }
  .msg-modal-overlay.active { display: flex; }
  .msg-modal {
    background: var(--black2); border: 1px solid var(--grey);
    border-radius: 16px; padding: 32px; max-width: 560px; width: 90%;
    position: relative;
  }
  .msg-modal h3 { font-size: 18px; color: var(--white); margin-bottom: 16px; }
  .msg-modal .meta { font-size: 13px; color: var(--muted); margin-bottom: 6px; }
  .msg-modal .meta span { color: var(--white); }
  .msg-modal .body {
    margin-top: 16px; padding: 16px; background: var(--black3);
    border: 1px solid var(--grey); border-radius: 8px;
    color: var(--muted); font-size: 14px; line-height: 1.75; white-space: pre-wrap;
  }
  .modal-close {
    position: absolute; top: 16px; right: 16px;
    background: none; border: none; color: var(--muted); font-size: 20px;
    cursor: pointer; line-height: 1;
  }
  .modal-close:hover { color: var(--red); }

  .empty-state { padding: 60px; text-align: center; color: var(--muted); font-size: 15px; }
</style>

<!-- Header -->
<div class="msg-header">
  <div class="msg-title">
    Messages
    <?php if (count($unread) > 0): ?>
      <span class="msg-badge"><?= count($unread) ?> unread</span>
    <?php endif; ?>
  </div>
</div>

<!-- Table -->
<div class="msg-table-wrap">
  <?php if (empty($messages)): ?>
    <div class="empty-state">📭 No messages yet.</div>
  <?php else: ?>
    <table class="msg-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Subject</th>
          <th>Message</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($messages as $i => $m): ?>
          <tr class="<?= !$m['is_read'] ? 'unread' : '' ?>">
            <td><?= $i + 1 ?><?= !$m['is_read'] ? '<span class="badge-unread">NEW</span>' : '' ?></td>
            <td class="msg-name"><?= htmlspecialchars($m['name']) ?></td>
            <td><?= htmlspecialchars($m['email']) ?></td>
            <td class="msg-subj"><?= htmlspecialchars($m['subject'] ?: '—') ?></td>
            <td class="msg-text"><?= htmlspecialchars($m['message']) ?></td>
            <td class="msg-date"><?= date('M d, Y g:i A', strtotime($m['contact_dateAdded'])) ?></td>
            <td>
              <div class="msg-actions">
                <button class="btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($m)) ?>)'>View</button>
                <?php if (!$m['is_read']): ?>
                  <a class="btn-sm" href="?page=admin&section=messages&read=<?= $m['contact_id'] ?>">Mark Read</a>
                <?php endif; ?>
                <a class="btn-sm danger" href="?page=admin&section=messages&delete=<?= $m['contact_id'] ?>"
                   onclick="return confirm('Delete this message?')">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- View Modal -->
<div class="msg-modal-overlay" id="msgModal">
  <div class="msg-modal">
    <button class="modal-close" onclick="closeModal()">✕</button>
    <h3 id="modalSubject"></h3>
    <div class="meta">From: <span id="modalName"></span></div>
    <div class="meta">Email: <span id="modalEmail"></span></div>
    <div class="meta">Date: <span id="modalDate"></span></div>
    <div class="body" id="modalBody"></div>
  </div>
</div>

<script>
function openModal(m) {
  document.getElementById('modalSubject').textContent = m.subject || 'No Subject';
  document.getElementById('modalName').textContent    = m.name;
  document.getElementById('modalEmail').textContent   = m.email;
  document.getElementById('modalDate').textContent    = m.contact_dateAdded;
  document.getElementById('modalBody').textContent    = m.message;
  document.getElementById('msgModal').classList.add('active');

  // Auto mark as read via fetch
  if (m.is_read == 0) {
    fetch('?page=admin&section=messages&read=' + m.contact_id);
  }
}
function closeModal() {
  document.getElementById('msgModal').classList.remove('active');
}
document.getElementById('msgModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>