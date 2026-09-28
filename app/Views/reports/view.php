<section class="page-head">
    <h1>Report #<?= (int) $report->report_id ?> — <?= dpams_e($report->test_name) ?></h1>
    <a class="btn btn-outline" href="/reports">← Back</a>
</section>

<div class="card">
    <div class="detail-grid">
        <div><span class="detail-label">Patient</span><span><?= dpams_e($report->patient_name) ?></span></div>
        <div><span class="detail-label">Doctor</span><span><?= dpams_e($report->doctor_name) ?></span></div>
        <div>
            <span class="detail-label">Appointment</span>
            <a href="/appointments/view/<?= (int) $report->appointment_id ?>">#<?= (int) $report->appointment_id ?></a>
            (<?= dpams_fmt_date($report->appointment_date) ?>)
        </div>
        <div><span class="detail-label">Report Date</span><span><?= dpams_fmt_date($report->report_date) ?></span></div>
        <div><span class="detail-label">Test</span><span><?= dpams_e($report->test_name) ?></span></div>
    </div>

    <h3 class="mb mt">Report Details</h3>
    <p><?= nl2br(dpams_e((string) $report->report_details)) ?: '<span class="muted">No written details.</span>' ?></p>

    <?php if (! empty($report->report_file)): ?>
        <h3 class="mb mt">Attached File</h3>
        <a class="btn btn-primary btn-sm" href="/reports/download/<?= (int) $report->report_id ?>">⬇ Download Report File</a>
    <?php endif; ?>
</div>

<?php if (in_array(session()->get('role'), ['manager', 'nurse'], true)): ?>
    <div class="mt">
        <a class="btn btn-outline" href="/reports/edit/<?= (int) $report->report_id ?>">Edit Report</a>
    </div>
<?php endif; ?>
