<section class="page-head">
    <h1>Staff &amp; Users</h1>
    <a class="btn btn-primary" href="/staff/create">+ Add Staff</a>
</section>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= dpams_e(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= (int) $u->user_id ?></td>
                    <td><?= dpams_e($u->name) ?></td>
                    <td><?= dpams_e($u->email) ?></td>
                    <td><span class="tag"><?= dpams_e(dpams_role_label($u->role)) ?></span></td>
                    <td><span class="badge <?= dpams_badge($u->status) ?>"><?= dpams_e($u->status) ?></span></td>
                    <td><?= dpams_fmt_date($u->created_at) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm btn-outline" href="/staff/edit/<?= (int) $u->user_id ?>">Edit</a>

                        <?php if ((int) $u->user_id !== (int) session()->get('user_id')): ?>
                            <form method="post" action="/staff/reset-password/<?= (int) $u->user_id ?>" class="inline-form"
                                  onsubmit="return confirm('Reset this user password to 123456?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="password" value="123456">
                                <button type="submit" class="btn btn-sm btn-warning">Reset PW</button>
                            </form>

                            <form method="post" action="/staff/delete/<?= (int) $u->user_id ?>" class="inline-form"
                                  onsubmit="return confirm('Delete this user?');">
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

<p class="muted small">"Reset PW" sets the user's password to <code>123456</code>.</p>
