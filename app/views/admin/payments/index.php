<?php
$pageTitle  = 'Payments';
$activePage = 'admin-payments';
?>

<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
        <div class="stat-value">₱<?= number_format($totalRevenue ?? 0) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?= $paidCount ?? 0 ?></div>
        <div class="stat-label">Paid</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?= $pendingCount ?? 0 ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value"><?= $refundedCount ?? 0 ?></div>
        <div class="stat-label">Refunded</div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>" class="filter-form">
        <input type="hidden" name="page" value="admin-payments">
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input" placeholder="Search client, booking ID..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Paid"     <?= ($_GET['status'] ?? '') === 'Paid'     ? 'selected' : '' ?>>Paid</option>
                <option value="Pending"  <?= ($_GET['status'] ?? '') === 'Pending'  ? 'selected' : '' ?>>Pending</option>
                <option value="Refunded" <?= ($_GET['status'] ?? '') === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="method" class="filter-select">
                <option value="">All Methods</option>
                <option value="Cash"          <?= ($_GET['method'] ?? '') === 'Cash'          ? 'selected' : '' ?>>Cash</option>
                <option value="GCash"         <?= ($_GET['method'] ?? '') === 'GCash'         ? 'selected' : '' ?>>GCash</option>
                <option value="Bank Transfer" <?= ($_GET['method'] ?? '') === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                <option value="Card"          <?= ($_GET['method'] ?? '') === 'Card'          ? 'selected' : '' ?>>Card</option>
            </select>
        </div>
        <input type="date" name="date_from" class="filter-input date-input" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
        <input type="date" name="date_to"   class="filter-input date-input" value="<?= htmlspecialchars($_GET['date_to']   ?? '') ?>">
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="<?= BASE_URL ?>?page=admin-payments" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Payment Transactions</span>
        <span class="record-count"><?= count($payments ?? []) ?> Records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th><th>Booking #</th><th>Client</th><th>Amount</th>
                <th>Method</th><th>Date</th><th>Reference No.</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><span class="id-badge">#<?= $p['payment_id'] ?></span></td>
                <td><span class="id-badge">#<?= $p['booking_id'] ?></span></td>
                <td><?= htmlspecialchars($p['clnt_fname'] . ' ' . $p['clnt_lname']) ?></td>
                <td class="amount-cell">₱<?= number_format($p['amount'], 2) ?></td>
                <td>
                    <?php
                    $icons = ['Cash'=>'fa-money-bill','GCash'=>'fa-mobile-alt','Card'=>'fa-credit-card','Bank Transfer'=>'fa-university'];
                    $icon  = $icons[$p['payment_method'] ?? 'Cash'] ?? 'fa-money-bill';
                    ?>
                    <span class="method-badge"><i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($p['payment_method'] ?? 'Cash') ?></span>
                </td>
                <td><?= $p['payment_date'] ?? '—' ?></td>
                <td><code class="ref-code"><?= htmlspecialchars($p['reference_no'] ?? '—') ?></code></td>
                <td><span class="badge badge-<?= strtolower($p['payment_status'] ?? 'pending') ?>"><?= $p['payment_status'] ?? 'Pending' ?></span></td>
                <td>
                    <div class="action-btns">
                        <?php if (($p['payment_status'] ?? '') === 'Pending'): ?>
                        <form method="POST" action="<?= BASE_URL ?>?page=admin-payments-confirm" style="display:inline">
                            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                            <button type="submit" class="btn-action btn-confirm" title="Mark Paid"><i class="fas fa-check"></i></button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" class="empty-row"><i class="fas fa-receipt" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>No payment records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.filter-bar { background:var(--black-card); border:1px solid var(--border); border-radius:8px; padding:14px 20px; margin-bottom:18px; }
.filter-form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.filter-group { position:relative; display:flex; align-items:center; }
.filter-icon { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem; min-width:200px; font-family:'Barlow',sans-serif; }
.filter-input.date-input { padding-left:14px; min-width:150px; }
.filter-input:focus { outline:none; border-color:var(--red); }
.filter-select { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px; font-size:.85rem; font-family:'Barlow',sans-serif; cursor:pointer; }
.filter-select:focus { outline:none; border-color:var(--red); }
.filter-select option { background:var(--black-card); }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge { color:var(--red); font-weight:600; }
.amount-cell { font-weight:700; color:var(--white); }
.method-badge { display:inline-flex; align-items:center; gap:6px; font-size:.8rem; padding:4px 10px; border-radius:6px; background:rgba(255,255,255,.07); color:var(--silver); }
.ref-code { background:rgba(255,255,255,.07); padding:3px 8px; border-radius:4px; font-family:monospace; font-size:.82rem; color:var(--silver); }
.action-btns { display:flex; gap:6px; align-items:center; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; text-decoration:none; }
.btn-confirm { background:rgba(39,174,96,.15); color:#27ae60; }
.btn-confirm:hover { background:#27ae60; color:var(--white); }
.empty-row { text-align:center; color:var(--silver); padding:48px !important; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid var(--border); padding:9px 14px; border-radius:6px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .2s; font-family:'Barlow',sans-serif; }
.btn-ghost:hover { color:var(--white); border-color:var(--silver); }
</style>