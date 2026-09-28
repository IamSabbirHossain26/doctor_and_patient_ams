<section class="page-head">
    <h1>Staff Nurse Dashboard</h1>
    <span class="tag">Staff Nurse</span>
</section>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['today'] ?></span>
        <span class="stat-label">Today's Appointments</span>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['patients'] ?></span>
        <span class="stat-label">Patients</span>
        <a class="stat-link" href="/patients">Manage →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['reports'] ?></span>
        <span class="stat-label">Reports</span>
        <a class="stat-link" href="/reports">Manage →</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= (int) $counts['appointments']->total ?></span>
        <span class="stat-label">Total Appointments</span>
        <a class="stat-link" href="/appointments">View →</a>
    </div>
</div>

<h3 class="section-title mt">Today's Schedule (<?= dpams_fmt_date(date('Y-m-d')) ?>)</h3>
<?php if (empty($today)): ?>
    <p class="muted">No appointments scheduled for today.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Time</th><th>Doctor</th><th>Patient</th><th>Status</th><th>Test</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($today as $a): ?>
                    <tr>
                        <td><?= dpams_fmt_time($a->appointment_time) ?></td>
                        <td><?= dpams_e($a->doctor_name) ?></td>
                        <td><?= dpams_e($a->patient_name) ?></td>
                        <td><span class="badge <?= dpams_badge($a->status) ?>"><?= dpams_e($a->status) ?></span></td>
                        <td><?= dpams_e($a->test_name ?: '-') ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline" href="/appointments/view/<?= (int) $a->appointment_id ?>">Open</a>
                            <a class="btn btn-sm btn-outline" href="/patients/view/<?= (int) $a->patient_id ?>">Patient</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="mt quick-links">
    <a class="btn btn-outline" href="/patients/create">+ Add Patient</a>
    <a class="btn btn-outline" href="/appointments/book">+ Book Appointment</a>
    <a class="btn btn-outline" href="/reports/upload">+ Upload Report</a>
</div>
