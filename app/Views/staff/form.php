<section class="page-head">
    <h1><?= $user ? 'Edit Staff' : 'Add Staff' ?></h1>
    <a class="btn btn-outline" href="/staff">← Back</a>
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
          action="<?= $user ? '/staff/update/' . (int) $user->user_id : '/staff/store' ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required
                       value="<?= old('name', $user->name ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?= old('email', $user->email ?? '') ?>">
            </div>
        </div>

        <?php if (! $user): ?>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <?php foreach (dpams_roles() as $r): ?>
                        <option value="<?= $r ?>" <?= old('role', $user->role ?? '') === $r ? 'selected' : '' ?>>
                            <?= dpams_role_label($r) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?= old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= old('status', $user->status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <?php if ($user && $user->role === 'doctor'): ?>
            <p class="muted small">Note: this user has a linked doctor profile. The doctor profile itself is managed in the Doctors module.</p>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary"><?= $user ? 'Update Account' : 'Create Account' ?></button>
    </form>
</div>
