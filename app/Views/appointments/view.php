<section class="page-head">
    <h1>Appointment #<?= (int) $appointment->appointment_id ?></h1>
    <a class="btn btn-outline" href="/appointments">← Back</a>
</section>

<div class="detail-cards">
    <div class="card">
        <h3 class="mb">Appointment</h3>
        <div class="detail-grid">
            <div><span class="detail-label">Doctor</span><span><?= dpams_e($appointment->doctor_name) ?></span></div>
            <div><span class="detail-label">Patient</span><span><?= dpams_e($appointment->patient_name) ?></span></div>
            <div><span class="detail-label">Date</span><span><?= dpams_fmt_date($appointment->appointment_date) ?></span></div>
            <div><span class="detail-label">Time</span><span><?= dpams_fmt_time($appointment->appointment_time) ?></span></div>
            <div>
                <span class="detail-label">Status</span>
                <span class="badge <?= dpams_badge($appointment->status) ?>"><?= dpams_e($appointment->status) ?></span>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="mb">Bill &amp; Test</h3>
        <div class="detail-grid">
            <div>
                <span class="detail-label">Bill Amount</span>
                <span><?= dpams_money($appointment->bill_amount) ?></span>
            </div>
            <div>
                <span class="detail-label">Bill Status</span>
                <span class="badge <?= dpams_badge($appointment->bill_status) ?>"><?= dpams_e($appointment->bill_status) ?></span>
            </div>
            <div>
                <span class="detail-label">Test</span>
                <span><?= dpams_e($appointment->test_name ?: 'None') ?></span>
            </div>
            <div>
                <span class="detail-label">Test Status</span>
                <span class="badge <?= dpams_badge($appointment->test_status === 'collected' ? 'collected' : $appointment->test_status) ?>"><?= dpams_e($appointment->test_status) ?></span>
            </div>
        </div>

        <?php if ($report): ?>
            <div class="mt">
                <span class="detail-label">Report</span>
                <a class="btn btn-sm btn-outline" href="/reports/view/<?= (int) $report->report_id ?>">View Report</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($canManage): ?>
    <div class="detail-cards mt">
        <div class="card">
            <h3 class="mb">Update Status</h3>
            <form method="post" action="/appointments/status/<?= (int) $appointment->appointment_id ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <select name="status">
                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $appointment->status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save Status</button>
            </form>
        </div>

        <div class="card">
            <h3 class="mb">Update Bill</h3>
            <form method="post" action="/appointments/bill/<?= (int) $appointment->appointment_id ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Amount (Tk)</label>
                    <input type="number" step="0.01" min="0" max="99999999" name="bill_amount"
                           value="<?= dpams_e($appointment->bill_amount) ?>">
                </div>
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="bill_status">
                        <option value="unpaid" <?= $appointment->bill_status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                        <option value="paid" <?= $appointment->bill_status === 'paid' ? 'selected' : '' ?>>Paid</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save Bill</button>
            </form>
        </div>

        <div class="card">
            <h3 class="mb">Prescribe / Update Test</h3>
            <form method="post" action="/appointments/test/<?= (int) $appointment->appointment_id ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Test Name</label>
                    <input type="text" name="test_name" placeholder="e.g. CBC, X-Ray Chest"
                           value="<?= dpams_e($appointment->test_name) ?>">
                </div>
                <div class="form-group">
                    <label>Test Status</label>
                    <select name="test_status">
                        <?php foreach (['none', 'pending', 'collected'] as $s): ?>
                            <option value="<?= $s ?>" <?= $appointment->test_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save Test</button>
            </form>
        </div>
    </div>
<?php endif; ?>
