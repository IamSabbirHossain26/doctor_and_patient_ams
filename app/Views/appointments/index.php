<section class="page-head">
    <h1>Appointments</h1>
    <a class="btn btn-primary" href="/appointments/book">+ Book Appointment</a>
</section>

<form class="filter-bar" method="get" action="/appointments">
    <input type="date" name="date" value="<?= dpams_e($filters['date']) ?>">
    <select name="status">
        <option value="">All statuses</option>
        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="bill_status">
        <option value="">All bills</option>
        <option value="unpaid" <?= $filters['bill_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
        <option value="paid" <?= $filters['bill_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
    </select>
    <button type="submit" class="btn btn-outline">Filter</button>
    <a class="btn btn-ghost" href="/appointments">Reset</a>
</form>

<?php if (empty($items)): ?>
    <p class="muted">No appointments found.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Doctor</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Bill</th>
                    <th>Test</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $a): ?>
                    <tr>
                        <td>#<?= (int) $a->appointment_id ?></td>
                        <td><?= dpams_e($a->doctor_name) ?></td>
                        <td><?= dpams_e($a->patient_name) ?></td>
                        <td><?= dpams_fmt_date($a->appointment_date) ?></td>
                        <td><?= dpams_fmt_time($a->appointment_time) ?></td>
                        <td><span class="badge <?= dpams_badge($a->status) ?>"><?= dpams_e($a->status) ?></span></td>
                        <td>
                            <?= dpams_money($a->bill_amount) ?><br>
                            <span class="badge <?= dpams_badge($a->bill_status) ?>"><?= dpams_e($a->bill_status) ?></span>
                        </td>
                        <td>
                            <?= dpams_e($a->test_name ?: '-') ?>
                            <?php if ($a->test_name): ?>
                                <br><span class="badge <?= dpams_badge($a->test_status === 'collected' ? 'collected' : $a->test_status) ?>"><?= dpams_e($a->test_status) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a class="btn btn-sm btn-outline" href="/appointments/view/<?= (int) $a->appointment_id ?>">Open</a>
                            <?php if ($canManage): ?>
                                <form method="post" action="/appointments/delete/<?= (int) $a->appointment_id ?>"
                                      onsubmit="return confirm('Delete this appointment?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Del</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
