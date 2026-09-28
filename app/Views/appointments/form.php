<section class="page-head">
    <h1>Book Appointment</h1>
    <a class="btn btn-outline" href="/appointments">← Back</a>
</section>

<?php if (! empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= dpams_e($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= dpams_e(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card form-card">
    <form method="post" action="/appointments/store">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="doctor_id">Doctor *</label>
                <select id="doctor_id" name="doctor_id" required>
                    <option value="">— Select doctor —</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= (int) $doc->doctor_id ?>"
                            <?= (int) old('doctor_id') === (int) $doc->doctor_id || $selectedDoctor === (int) $doc->doctor_id ? 'selected' : '' ?>>
                            <?= dpams_e($doc->doctor_name) ?><?= $doc->skills ? ' — ' . dpams_e($doc->skills) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (in_array(session()->get('role'), ['manager', 'nurse'], true)): ?>
                <div class="form-group">
                    <label for="patient_id">Patient *</label>
                    <select id="patient_id" name="patient_id" required>
                        <option value="">— Select patient —</option>
                        <?php foreach ($patients as $pat): ?>
                            <option value="<?= (int) $pat->patient_id ?>">
                                <?= dpams_e($pat->patient_name) ?><?= $pat->contact_info ? ' (' . dpams_e($pat->contact_info) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="patient_id" value="0">
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="appointment_date">Date *</label>
                <input type="date" id="appointment_date" name="appointment_date"
                       min="<?= date('Y-m-d') ?>" required
                       value="<?= old('appointment_date') ?>">
            </div>
            <div class="form-group">
                <label for="appointment_time">Time *</label>
                <input type="time" id="appointment_time" name="appointment_time" required
                       value="<?= old('appointment_time') ?>">
            </div>
        </div>

        <p class="muted small">
            Slots are validated per doctor — the same date &amp; time cannot be booked twice
            (cancelled appointments are excluded).
        </p>

        <button type="submit" class="btn btn-primary">Book Appointment</button>
    </form>
</div>
