<?php
$pageTitle  = 'Clients';
$activePage = 'admin-clients';
?>

<div class="section-header">
    <h2 class="section-title"><i class="fas fa-users"></i> Registered Clients</h2>
</div>

<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Registered</th>
                <th>MFA</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clients)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--silver)">No clients found.</td></tr>
            <?php else: ?>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?= $c['client_id'] ?></td>
                    <td><?= htmlspecialchars($c['clnt_fname'] . ' ' . $c['clnt_lname']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['clnt_phone_number']) ?></td>
                    <td><?= htmlspecialchars($c['adress']) ?></td>
                    <td><?= date('M d, Y', strtotime($c['client_dateAdded'])) ?></td>
                    <td>
                        <?php if (!empty($c['mfa_secret'])): ?>
                            <span class="badge badge-success"><i class="fas fa-shield-halved"></i> Enabled</span>
                        <?php else: ?>
                            <span class="badge badge-muted">None</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>