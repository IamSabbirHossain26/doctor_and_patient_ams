<section class="page-head">
    <h1>Test Reports</h1>
    <?php if ($canManage): ?>
        <a class="btn btn-primary" href="/reports/upload">+ Upload Report</a>
    <?php endif; ?>
</section>

<form class="filter-bar" method="get" action="/reports">
    <input type="text" name="q" value="<?= dpams_e($q) ?>" placeholder="Search test name or patient…">
    <button type="submit" class="btn btn-outline">Search</button>
</form>

<?php if (empty($items)): ?>
    <p class="muted">No reports found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Test</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Appointment</th>
                    <th>Report Date</th>
                    <th>File</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $r): ?>
                    <tr>
                        <td>#<?= (int) $r->report_id ?></td>
                        <td><?= dpams_e($r->test_name) ?></td>
                        <td><?= dpams_e($r->patient_name) ?></td>
                        <td><?= dpams_e($r->doctor_name) ?></td>
                        <td><a href="/appointments/view/<?= (int) $r->appointment_id ?>">#<?= (int) $r->appointment_id ?></a></td>
                        <td><?= dpams_fmt_date($r->report_date) ?></td>
                        <td>
                            <?php if (! empty($r->report_file)): ?>
                                <span class="badge badge-info">attached</span>
                            <?php else: ?>
                                <span class="muted">text only</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a class="btn btn-sm btn-outline" href="/reports/view/<?= (int) $r->report_id ?>">View</a>
                            <?php if (! empty($r->report_file)): ?>
                                <a class="btn btn-sm btn-outline" href="/reports/download/<?= (int) $r->report_id ?>">Download</a>
                            <?php endif; ?>
                            <?php if ($canManage): ?>
                                <a class="btn btn-sm btn-outline" href="/reports/edit/<?= (int) $r->report_id ?>">Edit</a>
                                <form method="post" action="/reports/delete/<?= (int) $r->report_id ?>"
                                      onsubmit="return confirm('Delete this report?');">
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
