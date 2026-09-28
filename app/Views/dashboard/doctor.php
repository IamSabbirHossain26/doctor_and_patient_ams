<section class="page-head">
    <h1>Doctor Dashboard</h1>
    <?php if ($doctor): ?>
        <span class="tag"><?= dpams_e($doctor->doctor_name) ?></span>
    <?php endif; ?>
</section>

<?php if (! $doctor): ?>
    <div class="alert alert-warning">
        No doctor profile is linked to this account yet. Please ask the manager to create
        your doctor profile (Staff → role "Doctor") or link your account.
    </div>
<?php else: ?>
    <div class="stat-grid">
        <div class="stat-card">
            <span class="stat-num"><?= (int) $counts['today'] ?></span>
            <span class="stat-label">Today's Appointments</span>
        </div>
        <div class="stat-card">
            <span class="stat-num"><?= (int) $counts['total'] ?></span>
            <span class="stat-label">My Total Appointments</span>
            <a class="stat-link" href="/appointments">View →</a>
        </div>
        <div class="stat-card">
            <span class="stat-num"><?= (int) $counts['pending'] ?></span>
            <span class="stat-label">Pending</span>
            <a class="stat-link" href="/appointments?status=pending">Review →</a>
        </div>
    </div>

    <h3 class="section-title mt">Today's Schedule (<?= dpams_fmt_date(date('Y-m-d')) ?>)</h3>
    <?php if (empty($today)): ?>
        <p class="muted">No appointments scheduled for today.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Time</th><th>Patient</th><th>Contact</th><th>Status</th><th>Test</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($today as $a): ?>
                        <tr>
                            <td><?= dpams_fmt_time($a->appointment_time) ?></td>
                            <td><?= dpams_e($a->patient_name) ?></td>
                            <td><?= dpams_e($a->patient_contact) ?></td>
                            <td><span class="badge <?= dpams_badge($a->status) ?>"><?= dpams_e($a->status) ?></span></td>
                            <td><?= dpams_e($a->test_name ?: '-') ?></td>
                            <td><a class="btn btn-sm btn-outline" href="/appointments/view/<?= (int) $a->appointment_id ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
