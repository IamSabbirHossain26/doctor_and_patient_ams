<section class="page-head">
    <h1><?= $doctor ? 'Edit Doctor' : 'Add Doctor' ?></h1>
    <a class="btn btn-outline" href="/doctors">← Back</a>
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
          action="<?= $doctor ? '/doctors/update/' . (int) $doctor->doctor_id : '/doctors/store' ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="doctor_name">Doctor Name *</label>
            <input type="text" id="doctor_name" name="doctor_name" required
                   value="<?= old('doctor_name', $doctor->doctor_name ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"
                      placeholder="Short professional biography"><?= old('description', $doctor->description ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="skills">Skills <span class="muted">(comma separated)</span></label>
            <input type="text" id="skills" name="skills"
                   placeholder="e.g. Cardiology, Echocardiography"
                   value="<?= old('skills', $doctor->skills ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="services">Services</label>
            <textarea id="services" name="services" rows="3"
                      placeholder="Services offered"><?= old('services', $doctor->services ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="contact_info">Contact Info</label>
                <input type="text" id="contact_info" name="contact_info"
                       value="<?= old('contact_info', $doctor->contact_info ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= old('status', $doctor->status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= old('status', $doctor->status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><?= $doctor ? 'Update Doctor' : 'Create Doctor' ?></button>
    </form>
</div>
