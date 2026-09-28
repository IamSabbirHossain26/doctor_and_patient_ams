<section class="page-head">
    <h1>Manager Dashboard</h1>
    <span class="tag">Manager / Representative</span>
</section>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['doctors'] ?></span>
        <span class="stat-label">Doctors</span>
        <a class="stat-link" href="/doctors">Manage →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['patients'] ?></span>
        <span class="stat-label">Patients</span>
        <a class="stat-link" href="/patients">Manage →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['appointments']->total ?></span>
        <span class="stat-label">Appointments</span>
        <a class="stat-link" href="/appointments">View →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['appointments']->pending ?></span>
        <span class="stat-label">Pending</span>
        <a class="stat-link" href="/appointments?status=pending">Review →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['appointments']->unpaid ?></span>
        <span class="stat-label">Unpaid Bills</span>
        <a class="stat-link" href="/appointments?bill_status=unpaid">Collect →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= dpams_money($counts['appointments']->revenue) ?></span>
        <span class="stat-label">Revenue (paid)</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['reports'] ?></span>
        <span class="stat-label">Reports</span>
        <a class="stat-link" href="/reports">View →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['users'] ?></span>
        <span class="stat-label">User Accounts</span>
        <a class="stat-link" href="/staff">Manage →</a>
    </div>
</div>

<h3 class="section-title mt">Recent Appointments</h3>
<?php if (empty($recent)): ?>
    <p class="muted">No appointments yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>ID</th><th>Doctor</th><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Bill</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $a): ?>
                    <tr>
                        <td>#<?= (int) $a->appointment_id ?></td>
                        <td><?= dpams_e($a->doctor_name) ?></td>
                        <td><?= dpams_e($a->patient_name) ?></td>
                        <td><?= dpams_fmt_date($a->appointment_date) ?></td>
                        <td><?= dpams_fmt_time($a->appointment_time) ?></td>
                        <td><span class="badge <?= dpams_badge($a->status) ?>"><?= dpams_e($a->status) ?></span></td>
                        <td><?= dpams_money($a->bill_amount) ?> <span class="badge <?= dpams_badge($a->bill_status) ?>"><?= dpams_e($a->bill_status) ?></span></td>
                        <td><a class="btn btn-sm btn-outline" href="/appointments/view/<?= (int) $a->appointment_id ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
