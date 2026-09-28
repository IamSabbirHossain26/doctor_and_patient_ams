<section class="page-head">
    <h1>My Dashboard</h1>
    <a class="btn btn-primary" href="/appointments/book">+ Book Appointment</a>
</section>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['total'] ?></span>
        <span class="stat-label">My Appointments</span>
        <a class="stat-link" href="/appointments">View →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['confirmed'] ?></span>
        <span class="stat-label">Confirmed</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['unpaid'] ?></span>
        <span class="stat-label">Unpaid Bills</span>
        <a class="stat-link" href="/appointments?bill_status=unpaid">View →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= count($reports) ?>+</span>
        <span class="stat-label">Reports</span>
        <a class="stat-link" href="/reports">View →</a>
    </div>
</div>

<h3 class="section-title mt">Upcoming / Active Appointments</h3>
<?php if (empty($upcoming)): ?>
    <p class="muted">No upcoming appointments. <a href="/appointments/book">Book one now →</a></p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th><th>Bill</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming as $a): ?>
                    <tr>
                        <td><?= dpams_fmt_date($a->appointment_date) ?></td>
                        <td><?= dpams_fmt_time($a->appointment_time) ?></td>
                        <td><?= dpams_e($a->doctor_name) ?></td>
                        <td><span class="badge <?= dpams_badge($a->status) ?>"><?= dpams_e($a->status) ?></span></td>
                        <td>
                            <?= dpams_money($a->bill_amount) ?>
                            <span class="badge <?= dpams_badge($a->bill_status) ?>"><?= dpams_e($a->bill_status) ?></span>
                        </td>
                        <td><a class="btn btn-sm btn-outline" href="/appointments/view/<?= (int) $a->appointment_id ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h3 class="section-title mt">My Test Reports</h3>
<?php if (empty($reports)): ?>
    <p class="muted">No reports yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Test</th><th>Date</th><th>Doctor</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $r): ?>
                    <tr>
                        <td><?= dpams_e($r->test_name) ?></td>
                        <td><?= dpams_fmt_date($r->report_date) ?></td>
                        <td><?= dpams_e($r->doctor_name) ?></td>
                        <td><a class="btn btn-sm btn-outline" href="/reports/view/<?= (int) $r->report_id ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="mt quick-links">
    <a class="btn btn-outline" href="/my-profile">Edit My Profile</a>
    <a class="btn btn-outline" href="/doctors">Browse Doctors</a>
</div>
