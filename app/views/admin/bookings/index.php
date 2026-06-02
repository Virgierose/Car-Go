<?php
$pageTitle  = 'Bookings';
$activePage = 'admin-bookings';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/booking.css">
<style>
/* Admin-specific overrides */
.tab-bar  { display:flex; gap:4px; margin-bottom:16px; border-bottom:1px solid var(--border); }
.tab      { padding:10px 18px; color:var(--silver); font-size:.8rem; font-weight:600;
            letter-spacing:.08em; text-transform:uppercase; text-decoration:none;
            border-bottom:2px solid transparent; margin-bottom:-1px; transition:all .2s; }
.tab:hover     { color:var(--white); }
.tab-active    { color:var(--red); border-bottom-color:var(--red); }
.filter-bar    { background:var(--black-card); border:1px solid var(--border);
                 border-radius:8px; padding:14px 20px; margin-bottom:18px; }
.filter-form   { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.filter-group  { position:relative; display:flex; align-items:center; }
.filter-icon   { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input  { background:var(--black); border:1px solid var(--border); border-radius:6px;
                 color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem;
                 min-width:220px; font-family:'Barlow',sans-serif; }
.filter-input.date-input { padding-left:14px; min-width:160px; }
.filter-input:focus { outline:none; border-color:var(--red); }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge     { color:var(--red); font-weight:600; }
.ref-badge    { color:var(--silver); font-size:.78rem; font-family:monospace; }
.no-driver    { color:var(--silver); font-style:italic; font-size:.8rem; }
.action-btns  { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.btn-action   { display:inline-flex; align-items:center; justify-content:center;
                width:32px; height:32px; border-radius:6px; border:none;
                cursor:pointer; font-size:.8rem; transition:all .2s; text-decoration:none; }
.btn-edit  { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-edit:hover { background:rgba(255,255,255,.15); color:var(--white); }
.status-dropdown-wrap { position:relative; }
.status-dropdown      { display:none; position:absolute; right:0; top:36px;
                        background:var(--black-card); border:1px solid var(--border);
                        border-radius:8px; min-width:140px; z-index:100;
                        box-shadow:0 8px 24px rgba(0,0,0,.4); overflow:hidden; }
.status-dropdown.open { display:block; }
.dd-item  { display:block; width:100%; text-align:left; background:none; border:none;
            color:var(--silver); padding:10px 16px; font-size:.82rem; cursor:pointer;
            transition:all .15s; font-family:'Barlow',sans-serif; }
.dd-item:hover  { background:rgba(255,255,255,.07); color:var(--white); }
.dd-active      { color:var(--red); font-weight:600; }
.empty-row      { text-align:center; color:var(--silver); padding:48px !important; }
.btn-ghost      { background:transparent; color:var(--silver); border:1px solid var(--border);
                  padding:9px 14px; border-radius:6px; font-size:.85rem; cursor:pointer;
                  text-decoration:none; transition:all .2s; font-family:'Barlow',sans-serif; }
.btn-ghost:hover { color:var(--white); border-color:var(--silver); }

/* Doc review panel inside modal */
.doc-img-wrap { display:flex; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
.doc-thumb    { flex:1; min-width:140px; }
.doc-thumb img { width:100%; border:1px solid rgba(255,255,255,.1); cursor:pointer; }
.doc-thumb a   { display:block; text-align:center; font-size:.72rem;
                 color:rgba(255,255,255,.4); margin-top:.3rem; text-decoration:underline; }
.doc-modal-bg  { display:none; position:fixed; inset:0; background:rgba(0,0,0,.9);
                 z-index:9999; align-items:center; justify-content:center; }
.doc-modal-bg.open { display:flex; }
.doc-modal-img { max-width:90vw; max-height:90vh; object-fit:contain; }

/* Approval actions */
.approve-actions { display:flex; gap:.5rem; margin-top:.8rem; flex-wrap:wrap; }
.btn-approve { background:#27ae60; color:#fff; border:none; padding:.5rem 1rem;
               font-size:.78rem; font-weight:700; cursor:pointer; letter-spacing:.06em;
               text-transform:uppercase; transition:opacity .2s; }
.btn-approve:hover { opacity:.85; }
.btn-reject  { background:#c0392b; color:#fff; border:none; padding:.5rem 1rem;
               font-size:.78rem; font-weight:700; cursor:pointer; letter-spacing:.06em;
               text-transform:uppercase; transition:opacity .2s; }
.btn-reject:hover { opacity:.85; }
.docs-row    { background:rgba(243,156,18,.04); }
</style>

<!-- Tabs -->
<div class="tab-bar">
  <?php
  $statuses  = ['All','Docs Pending','Pending','Confirmed','Completed','Cancelled'];
  $activeTab = $_GET['status'] ?? 'All';
  foreach ($statuses as $s):
      $slug = $s === 'All' ? '' : urlencode(strtolower(str_replace(' ','_',$s)));
      $url  = BASE_URL . '?page=admin-bookings' . ($slug ? '&status='.$slug : '');
  ?>
  <a href="<?= $url ?>" class="tab <?= $activeTab === $s ? 'tab-active' : '' ?>"><?= $s ?></a>
  <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div class="filter-bar">
  <form method="GET" action="<?= BASE_URL ?>" class="filter-form">
    <input type="hidden" name="page" value="admin-bookings">
    <?php if ($activeTab !== 'All'): ?>
      <input type="hidden" name="status" value="<?= htmlspecialchars($activeTab) ?>">
    <?php endif; ?>
    <div class="filter-group">
      <i class="fas fa-search filter-icon"></i>
      <input type="text" name="search" class="filter-input" placeholder="Search client, car, driver..."
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="filter-group">
      <input type="date" name="date_from" class="filter-input date-input"
             value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
    </div>
    <div class="filter-group">
      <input type="date" name="date_to" class="filter-input date-input"
             value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
    </div>
    <button type="submit" class="btn btn-outline">Filter</button>
    <a href="<?= BASE_URL ?>?page=admin-bookings" class="btn-ghost">Reset</a>
  </form>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">
    <span class="card-title">Booking Records</span>
    <span class="record-count"><?= count($bookings ?? []) ?> Records</span>
  </div>
  <table>
    <thead>
      <tr>
        <th>#</th><th>Ref</th><th>Client</th><th>Car</th><th>Pickup</th>
        <th>Return</th><th>Days</th><th>Total</th><th>Docs</th><th>Status</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($bookings)): ?>
      <?php foreach ($bookings as $b):
        $hasDocs    = !empty($b['license_img']) || !empty($b['gov_id_img']);
        $docsStatus = $b['docs_status'] ?? 'none';
        $rowClass   = ($docsStatus === 'pending') ? 'docs-row' : '';
      ?>
      <tr class="<?= $rowClass ?>">
        <td><span class="id-badge">#<?= $b['rental_id'] ?></span></td>
        <td><span class="ref-badge"><?= htmlspecialchars($b['booking_ref']) ?></span></td>
        <td><?= htmlspecialchars(($b['clnt_fname'] ?? '') . ' ' . ($b['clnt_lname'] ?? '')) ?></td>
        <td><?= htmlspecialchars($b['model_name'] ?? $b['plate_number'] ?? '—') ?></td>
        <td><?= date('M d, Y', strtotime($b['rental_start'])) ?></td>
        <td><?= date('M d, Y', strtotime($b['rental_end'])) ?></td>
        <td><?= $b['total_days'] ?></td>
        <td>₱<?= number_format($b['total_amount'], 2) ?></td>
        <td>
          <?php if ($hasDocs): ?>
            <span class="badge badge-<?= $docsStatus ?>">
              <?= ucfirst($docsStatus) ?>
            </span>
            <button class="btn-action btn-edit" style="margin-left:4px;"
                title="Review Documents"
                onclick="openDocReview(<?= htmlspecialchars(json_encode([
                  'rental_id'   => $b['rental_id'],
                  'client'      => ($b['clnt_fname'] ?? '') . ' ' . ($b['clnt_lname'] ?? ''),
                  'ref'         => $b['booking_ref'],
                  'license_img' => $b['license_img'] ?? '',
                  'gov_id_img'  => $b['gov_id_img']  ?? '',
                  'docs_status' => $docsStatus,
                ]), ENT_QUOTES) ?>)">
              🪪
            </button>
          <?php else: ?>
            <span style="color:rgba(255,255,255,.2);font-size:.72rem;">—</span>
          <?php endif; ?>
        </td>
        <td><span class="badge badge-<?= strtolower($b['rental_status']) ?>"><?= ucfirst(str_replace('_',' ',$b['rental_status'])) ?></span></td>
        <td>
          <div class="action-btns">
            <div class="status-dropdown-wrap">
              <button class="btn-action btn-edit" title="Update Status"
                      onclick="toggleDropdown(<?= $b['rental_id'] ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <div class="status-dropdown" id="dd-<?= $b['rental_id'] ?>">
                <?php foreach (['docs_pending','pending','confirmed','completed','cancelled'] as $st): ?>
                <form method="POST" action="<?= BASE_URL ?>?page=admin-bookings-status">
                  <input type="hidden" name="booking_id"   value="<?= $b['rental_id'] ?>">
                  <input type="hidden" name="status"       value="<?= $st ?>">
                  <input type="hidden" name="current_tab"  value="<?= htmlspecialchars($activeTab) ?>">
                  <button type="submit" class="dd-item <?= strtolower($b['rental_status']) === $st ? 'dd-active' : '' ?>">
                    <?= ucfirst(str_replace('_',' ',$st)) ?>
                  </button>
                </form>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="11" class="empty-row">
        <i class="fas fa-calendar-times" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>
        No bookings found.
      </td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ── Document Review Modal ──────────────────────────────── -->
<div class="doc-modal-bg" id="doc-modal">
  <div style="background:#111113;border:1px solid rgba(255,255,255,.1);
              padding:1.8rem;width:520px;max-width:95vw;position:relative;">
    <button onclick="closeDocModal()"
            style="position:absolute;top:1rem;right:1rem;background:none;border:none;
                   color:rgba(255,255,255,.4);font-size:1.2rem;cursor:pointer;">✕</button>

    <h3 style="font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;
               letter-spacing:.1em;text-transform:uppercase;color:#fff;margin-bottom:.3rem;">
      Document Review
    </h3>
    <p style="font-size:.75rem;color:rgba(255,255,255,.4);margin-bottom:1.2rem;" id="dm-client-info"></p>

    <div class="doc-img-wrap">
      <div class="doc-thumb">
        <div style="font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;
                    color:rgba(255,255,255,.4);margin-bottom:.4rem;">Driver's License</div>
        <img id="dm-license-img" src="" alt="License" onerror="this.style.display='none'">
        <a id="dm-license-link" href="#" target="_blank">Open full size ↗</a>
      </div>
      <div class="doc-thumb">
        <div style="font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;
                    color:rgba(255,255,255,.4);margin-bottom:.4rem;">Government ID</div>
        <img id="dm-govid-img" src="" alt="Gov ID" onerror="this.style.display='none'">
        <a id="dm-govid-link" href="#" target="_blank">Open full size ↗</a>
      </div>
    </div>

    <div id="dm-approve-area">
      <div class="approve-actions">
        <form method="POST" action="<?= BASE_URL ?>?page=admin-bookings-docs" style="display:contents;">
          <input type="hidden" name="rental_id" id="dm-rental-id">
          <input type="hidden" name="action"    value="approve">
          <input type="hidden" name="current_tab" value="<?= htmlspecialchars($activeTab) ?>">
          <button type="submit" class="btn-approve">✓ Approve Documents</button>
        </form>
        <form method="POST" action="<?= BASE_URL ?>?page=admin-bookings-docs" style="display:contents;">
          <input type="hidden" name="rental_id" id="dm-rental-id-reject">
          <input type="hidden" name="action"    value="reject">
          <input type="hidden" name="current_tab" value="<?= htmlspecialchars($activeTab) ?>">
          <div style="display:flex;gap:.4rem;align-items:center;">
            <input type="text" name="docs_note" placeholder="Reason for rejection (optional)"
                   style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
                          color:#fff;padding:.45rem .7rem;font-size:.78rem;flex:1;">
            <button type="submit" class="btn-reject">✕ Reject</button>
          </div>
        </form>
      </div>
    </div>

    <div id="dm-already-reviewed" style="display:none;margin-top:.8rem;">
      <span style="font-size:.8rem;color:rgba(255,255,255,.4);">
        Documents already reviewed — use the status dropdown to change.
      </span>
    </div>

    <!-- Full-size image lightbox -->
    <div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.95);
                              z-index:99999;align-items:center;justify-content:center;">
      <img id="lightbox-img" src="" style="max-width:95vw;max-height:95vh;object-fit:contain;">
      <button onclick="document.getElementById('lightbox').style.display='none'"
              style="position:absolute;top:1rem;right:1rem;background:none;border:none;
                     color:#fff;font-size:1.5rem;cursor:pointer;">✕</button>
    </div>
  </div>
</div>

<script>
function toggleDropdown(id) {
  document.querySelectorAll('.status-dropdown').forEach(function(d){
    if (d.id !== 'dd-' + id) d.classList.remove('open');
  });
  document.getElementById('dd-' + id).classList.toggle('open');
}
document.addEventListener('click', function(e){
  if (!e.target.closest('.status-dropdown-wrap'))
    document.querySelectorAll('.status-dropdown').forEach(function(d){ d.classList.remove('open'); });
});

function openDocReview(data) {
  var base = '<?= ASSET_URL ?>';
  document.getElementById('dm-client-info').textContent =
    data.client + ' — ' + data.ref;
  document.getElementById('dm-rental-id').value        = data.rental_id;
  document.getElementById('dm-rental-id-reject').value = data.rental_id;

  var licImg  = document.getElementById('dm-license-img');
  var govImg  = document.getElementById('dm-govid-img');
  var licLink = document.getElementById('dm-license-link');
  var govLink = document.getElementById('dm-govid-link');

  licImg.src  = data.license_img ? base + data.license_img : '';
  govImg.src  = data.gov_id_img  ? base + data.gov_id_img  : '';
  licLink.href = data.license_img ? base + data.license_img : '#';
  govLink.href = data.gov_id_img  ? base + data.gov_id_img  : '#';

  // Click image to open lightbox
  [licImg, govImg].forEach(function(img) {
    img.onclick = function() {
      if (!this.src) return;
      document.getElementById('lightbox-img').src = this.src;
      document.getElementById('lightbox').style.display = 'flex';
    };
    img.style.cursor = 'zoom-in';
  });

  var isPending = data.docs_status === 'pending';
  document.getElementById('dm-approve-area').style.display    = isPending ? 'block' : 'none';
  document.getElementById('dm-already-reviewed').style.display = isPending ? 'none'  : 'block';

  document.getElementById('doc-modal').classList.add('open');
}
function closeDocModal() {
  document.getElementById('doc-modal').classList.remove('open');
}
document.getElementById('doc-modal').addEventListener('click', function(e){
  if (e.target === this) closeDocModal();
});
</script>