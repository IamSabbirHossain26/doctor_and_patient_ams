<div class="auth-card">
    <h1 class="auth-title">🩺 DPAMS</h1>
    <p class="auth-subtitle">Create your patient account</p>

    <h2>Register</h2>

    <?php if (isset($errors) && $errors !== []): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= dpams_e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/register">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="contact_info">Contact Info</label>
            <input type="text" id="contact_info" name="contact_info" value="<?= old('contact_info') ?>" placeholder="Phone / email">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?= old('address') ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>

    <p class="auth-alt">
        Already registered? <a href="/login">Login here</a>
    </p>
</div>
