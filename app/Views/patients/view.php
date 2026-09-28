<section class="page-head">
    <h1><?= dpams_e($patient->patient_name) ?></h1>
    <a class="btn btn-outline" href="/patients">← Back</a>
</section>

<div class="card">
    <div class="detail-grid">
        <div><span class="detail-label">Patient ID</span><span>#<?= (int) $patient->patient_id ?></span></div>
        <div><span class="detail-label">Contact</span><span><?= dpams_e($patient->contact_info) ?: '-' ?></span></div>
        <div><span class="detail-label">Address</span><span><?= dpams_e($patient->address) ?: '-' ?></span></div>
        <div><span class="detail-label">Date of Birth</span><span><?= dpams_fmt_date($patient->date_of_birth) ?></span></div>
        <div><span class="detail-label">Gender</span><span><?= dpams_e(ucfirst($patient->gender)) ?></span></div>
        <div><span class="detail-label">Registered</span><span><?= dpams_fmt_date($patient->created_at) ?></span></div>
    </div>
</div>

<h3 class="section-title mt">Appointments</h3>
<?php if (empty($appointments)): ?>
    <p class="muted">No appointments for this patient.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th><th>Doctor</th><th>Date</th><th>Time</th>
                    <th>Status</th><th>Bill</th><th>Test</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $a): ?>
                    <tr>
                        <td>#<?= (int) $a->appointment_id ?></td>
                        <td><?= dpams_e($a->doctor_name) ?></td>
                        <td><?= dpams_fmt_date($a->appointment_date) ?></td>
                        <td><?= dpams_fmt_time($a->appointment_time) ?></td>
                        <td><span class="badge <?= dpams_badge($a->status) ?>"><?= dpams_e($a->status) ?></span></td>
                        <td><?= dpams_money($a->bill_amount) ?> <span class="badge <?= dpams_badge($a->bill_status) ?>"><?= dpams_e($a->bill_status) ?></span></td>
                        <td>
                            <?= dpams_e($a->test_name ?: '-') ?>
                            <?php if ($a->test_name): ?>
                                <span class="badge <?= dpams_badge($a->test_status === 'collected' ? 'collected' : $a->test_status) ?>"><?= dpams_e($a->test_status) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><a class="btn btn-sm btn-outline" href="/appointments/view/<?= (int) $a->appointment_id ?>">Open</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
