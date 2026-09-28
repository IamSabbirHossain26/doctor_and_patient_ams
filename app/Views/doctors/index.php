<section class="page-head">
    <h1>Doctors</h1>
    <?php if (session()->get('role') === 'manager'): ?>
        <a class="btn btn-primary" href="/doctors/create">+ Add Doctor</a>
    <?php endif; ?>
</section>

<form class="filter-bar" method="get" action="/doctors">
    <input type="text" name="q" value="<?= dpams_e($q) ?>" placeholder="Search name, skills, services…">
    <select name="status">
        <option value="">All status</option>
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>
    <button type="submit" class="btn btn-outline">Filter</button>
</form>

<?php if (empty($doctors)): ?>
    <p class="muted">No doctors found.</p>
<?php else: ?>
    <div class="card-grid">
        <?php foreach ($doctors as $doctor): ?>
            <div class="card doctor-card">
                <div class="card-head">
                    <h3><a href="/doctors/view/<?= (int) $doctor->doctor_id ?>"><?= dpams_e($doctor->doctor_name) ?></a></h3>
                    <span class="badge <?= dpams_badge($doctor->status) ?>"><?= dpams_e($doctor->status) ?></span>
                </div>

                <?php if (! empty($doctor->skills)): ?>
                    <p class="card-sub"><?= dpams_e($doctor->skills) ?></p>
                <?php endif; ?>

                <?php if (! empty($doctor->contact_info)): ?>
                    <p class="muted small">📞 <?= dpams_e($doctor->contact_info) ?></p>
                <?php endif; ?>

                <div class="card-actions">
                    <a class="btn btn-sm btn-outline" href="/doctors/view/<?= (int) $doctor->doctor_id ?>">View</a>
                    <?php if (session()->get('role') === 'manager'): ?>
                        <a class="btn btn-sm btn-outline" href="/doctors/edit/<?= (int) $doctor->doctor_id ?>">Edit</a>
                        <form method="post" action="/doctors/delete/<?= (int) $doctor->doctor_id ?>"
                              onsubmit="return confirm('Delete this doctor?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
