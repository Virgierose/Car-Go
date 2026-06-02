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

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/message.css">

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
          <th class="col-email">Email</th>
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
            <td class="col-email"><?= htmlspecialchars($m['email']) ?></td>
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