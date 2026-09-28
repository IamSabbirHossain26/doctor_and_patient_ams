<section class="page-head">
    <h1>Patients</h1>
    <?php if (in_array(session()->get('role'), ['manager', 'nurse'], true)): ?>
        <a class="btn btn-primary" href="/patients/create">+ Add Patient</a>
    <?php endif; ?>
</section>

<form class="filter-bar" method="get" action="/patients">
    <input type="text" name="q" value="<?= dpams_e($q) ?>" placeholder="Search name, contact, address…">
    <button type="submit" class="btn btn-outline">Search</button>
</form>

<?php if (empty($patients)): ?>
    <p class="muted">No patients found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td>#<?= (int) $patient->patient_id ?></td>
                        <td><a href="/patients/view/<?= (int) $patient->patient_id ?>"><?= dpams_e($patient->patient_name) ?></a></td>
                        <td><?= dpams_e($patient->contact_info) ?></td>
                        <td><?= dpams_e($patient->address) ?></td>
                        <td><?= dpams_fmt_date($patient->date_of_birth) ?></td>
                        <td><?= dpams_e(ucfirst($patient->gender)) ?></td>
                        <td class="actions">
                            <a class="btn btn-sm btn-outline" href="/patients/view/<?= (int) $patient->patient_id ?>">View</a>
                            <?php if (in_array(session()->get('role'), ['manager', 'nurse'], true)): ?>
                                <a class="btn btn-sm btn-outline" href="/patients/edit/<?= (int) $patient->patient_id ?>">Edit</a>
                                <form method="post" action="/patients/delete/<?= (int) $patient->patient_id ?>"
                                      onsubmit="return confirm('Delete this patient?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
