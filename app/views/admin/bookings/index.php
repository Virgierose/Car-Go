<?php
$pageTitle  = 'Bookings';
$activePage = 'admin-bookings';
?>

<div class="tab-bar">
    <?php
    $statuses  = ['All', 'Pending', 'Confirmed', 'Active', 'Completed', 'Cancelled'];
    $activeTab = $_GET['status'] ?? 'All';
    foreach ($statuses as $s):
        $url = $s === 'All'
            ? BASE_URL . '?page=admin-bookings'
            : BASE_URL . '?page=admin-bookings&status=' . urlencode($s);
    ?>
    <a href="<?= $url ?>" class="tab <?= $activeTab === $s ? 'tab-active' : '' ?>"><?= $s ?></a>
    <?php endforeach; ?>
</div>

<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>" class="filter-form">
        <input type="hidden" name="page" value="admin-bookings">
        <?php if ($activeTab !== 'All'): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($activeTab) ?>">
        <?php endif; ?>
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input" placeholder="Search client, car, driver..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <input type="date" name="date_from" class="filter-input date-input" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <input type="date" name="date_to" class="filter-input date-input" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="<?= BASE_URL ?>?page=admin-bookings" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Booking Records</span>
        <span class="record-count"><?= count($bookings ?? []) ?> Records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Client</th><th>Car</th><th>Driver</th>
                <th>Pickup</th><th>Return</th><th>Days</th><th>Total</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><span class="id-badge">#<?= $b['booking_id'] ?></span></td>
                <td><?= htmlspecialchars($b['clnt_fname'] . ' ' . $b['clnt_lname']) ?></td>
                <td><?= htmlspecialchars($b['model_name'] ?? $b['plate_number']) ?></td>
                <td><?= htmlspecialchars($b['drvr_fname'] . ' ' . $b['drvr_lname']) ?></td>
                <td><?= $b['pickup_date'] ?></td>
                <td><?= $b['return_date'] ?></td>
                <td><?= $b['num_days'] ?></td>
                <td>₱<?= number_format($b['total_price'], 2) ?></td>
                <td><span class="badge badge-<?= strtolower($b['bkng_status']) ?>"><?= $b['bkng_status'] ?></span></td>
                <td>
                    <div class="action-btns">
                        <div class="status-dropdown-wrap">
                            <button class="btn-action btn-edit" title="Update Status" onclick="toggleDropdown(<?= $b['booking_id'] ?>)"><i class="fas fa-pen"></i></button>
                            <div class="status-dropdown" id="dd-<?= $b['booking_id'] ?>">
                                <?php foreach (['Pending','Confirmed','Active','Completed','Cancelled'] as $st): ?>
                                <form method="POST" action="<?= BASE_URL ?>?page=admin-bookings-status">
                                    <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                    <input type="hidden" name="status" value="<?= $st ?>">
                                    <button type="submit" class="dd-item <?= $b['bkng_status'] === $st ? 'dd-active' : '' ?>"><?= $st ?></button>
                                </form>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="10" class="empty-row"><i class="fas fa-calendar-times" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>No bookings found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.tab-bar { display:flex; gap:4px; margin-bottom:16px; border-bottom:1px solid var(--border); }
.tab { padding:10px 18px; color:var(--silver); font-size:.8rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px; transition:all .2s; }
.tab:hover { color:var(--white); }
.tab-active { color:var(--red); border-bottom-color:var(--red); }
.filter-bar { background:var(--black-card); border:1px solid var(--border); border-radius:8px; padding:14px 20px; margin-bottom:18px; }
.filter-form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.filter-group { position:relative; display:flex; align-items:center; }
.filter-icon { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem; min-width:220px; font-family:'Barlow',sans-serif; }
.filter-input.date-input { padding-left:14px; min-width:160px; }
.filter-input:focus { outline:none; border-color:var(--red); }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge { color:var(--red); font-weight:600; }
.action-btns { display:flex; gap:6px; align-items:center; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; text-decoration:none; }
.btn-edit { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-edit:hover { background:rgba(255,255,255,.15); color:var(--white); }
.status-dropdown-wrap { position:relative; }
.status-dropdown { display:none; position:absolute; right:0; top:36px; background:var(--black-card); border:1px solid var(--border); border-radius:8px; min-width:130px; z-index:100; box-shadow:0 8px 24px rgba(0,0,0,.4); overflow:hidden; }
.status-dropdown.open { display:block; }
.dd-item { display:block; width:100%; text-align:left; background:none; border:none; color:var(--silver); padding:10px 16px; font-size:.82rem; cursor:pointer; transition:all .15s; font-family:'Barlow',sans-serif; }
.dd-item:hover { background:rgba(255,255,255,.07); color:var(--white); }
.dd-active { color:var(--red); font-weight:600; }
.empty-row { text-align:center; color:var(--silver); padding:48px !important; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid var(--border); padding:9px 14px; border-radius:6px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .2s; font-family:'Barlow',sans-serif; }
.btn-ghost:hover { color:var(--white); border-color:var(--silver); }
</style>
<script>
function toggleDropdown(id) {
    document.querySelectorAll('.status-dropdown').forEach(d => {
        if (d.id !== 'dd-' + id) d.classList.remove('open');
    });
    document.getElementById('dd-' + id).classList.toggle('open');
}
document.addEventListener('click', e => {
    if (!e.target.closest('.status-dropdown-wrap'))
        document.querySelectorAll('.status-dropdown').forEach(d => d.classList.remove('open'));
});
</script>