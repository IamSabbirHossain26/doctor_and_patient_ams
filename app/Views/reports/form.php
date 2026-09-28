<section class="page-head">
    <h1><?= $report ? 'Edit Report' : 'Upload Report' ?></h1>
    <a class="btn btn-outline" href="/reports">← Back</a>
</section>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= dpams_e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card form-card">
    <form method="post"
          action="<?= $report ? '/reports/update/' . (int) $report->report_id : '/reports/store' ?>"
          enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="appointment_id">Appointment *</label>
            <select id="appointment_id" name="appointment_id" required <?= $report ? 'disabled' : '' ?>>
                <option value="">— Select appointment —</option>
                <?php foreach ($appointments as $a): ?>
                    <option value="<?= (int) $a->appointment_id ?>"
                        <?= ($report && (int) $report->appointment_id === (int) $a->appointment_id) || (int) old('appointment_id') === (int) $a->appointment_id ? 'selected' : '' ?>>
                        #<?= (int) $a->appointment_id ?> — <?= dpams_e($a->patient_name ?? $a->patient_name ?? '') ?>
                        <?= isset($a->doctor_name) ? ' / Dr. ' . dpams_e($a->doctor_name) : '' ?>
                        (<?= dpams_fmt_date($a->appointment_date) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="test_name">Test Name *</label>
            <input type="text" id="test_name" name="test_name" required
                   placeholder="e.g. CBC, Urine R/E, X-Ray Chest"
                   value="<?= old('test_name', $report->test_name ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="report_details">Report Details</label>
            <textarea id="report_details" name="report_details" rows="5"
                      placeholder="Findings, values, remarks…"><?= old('report_details', $report->report_details ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="report_file">Report File <span class="muted">(PDF/JPG/PNG, max 4 MB<?= $report ? ' — optional replacement' : ' — optional' ?>)</span></label>
            <input type="file" id="report_file" name="report_file" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <button type="submit" class="btn btn-primary"><?= $report ? 'Update Report' : 'Save Report' ?></button>
    </form>
</div>
