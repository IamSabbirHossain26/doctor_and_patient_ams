<section class="page-head">
    <h1><?= $patient ? 'Edit Patient' : 'Add Patient' ?></h1>
    <a class="btn btn-outline" href="/patients">← Back</a>
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
          action="<?= $patient ? '/patients/update/' . (int) $patient->patient_id : '/patients/store' ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="patient_name">Patient Name *</label>
            <input type="text" id="patient_name" name="patient_name" required
                   value="<?= old('patient_name', $patient->patient_name ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="contact_info">Contact Info</label>
                <input type="text" id="contact_info" name="contact_info"
                       value="<?= old('contact_info', $patient->contact_info ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth"
                       value="<?= old('date_of_birth', $patient->date_of_birth ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="male" <?= old('gender', $patient->gender ?? 'male') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= old('gender', $patient->gender ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= old('gender', $patient->gender ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address"
                       value="<?= old('address', $patient->address ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><?= $patient ? 'Update Patient' : 'Create Patient' ?></button>
    </form>
</div>
