<section class="page-head">
    <h1><?= dpams_e($doctor->doctor_name) ?></h1>
    <span class="badge <?= dpams_badge($doctor->status) ?>"><?= dpams_e($doctor->status) ?></span>
</section>

<div class="card">
    <h3 class="mb">Description</h3>
    <p><?= nl2br(dpams_e((string) $doctor->description)) ?: '<span class="muted">No description provided.</span>' ?></p>

    <h3 class="mb">Skills</h3>
    <div class="tags">
        <?php foreach (array_filter(array_map('trim', explode(',', (string) $doctor->skills))) as $skill): ?>
            <span class="tag"><?= dpams_e($skill) ?></span>
        <?php endforeach; ?>
        <?php if (trim((string) $doctor->skills) === ''): ?>
            <span class="muted">No skills listed.</span>
        <?php endif; ?>
    </div>

    <h3 class="mb mt">Services</h3>
    <p><?= nl2br(dpams_e((string) $doctor->services)) ?: '<span class="muted">No services listed.</span>' ?></p>

    <h3 class="mb mt">Contact</h3>
    <p><?= dpams_e((string) $doctor->contact_info) ?: '<span class="muted">No contact info.</span>' ?></p>
</div>

<div class="mt">
    <?php if (session()->get('role') === 'patient'): ?>
        <a class="btn btn-primary" href="/appointments/book?doctor=<?= (int) $doctor->doctor_id ?>">Book Appointment</a>
    <?php endif; ?>
    <?php if (session()->get('role') === 'manager'): ?>
        <a class="btn btn-outline" href="/doctors/edit/<?= (int) $doctor->doctor_id ?>">Edit</a>
    <?php endif; ?>
    <a class="btn btn-outline" href="/doctors">← Back to Doctors</a>
</div>
