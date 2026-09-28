<div class="auth-card">
    <h1 class="auth-title">🩺 DPAMS</h1>
    <p class="auth-subtitle">Doctor &amp; Patient Appointment Management System</p>

    <h2>Login</h2>

    <?php if (isset($errors) && $errors !== []): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= dpams_e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/login">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="auth-alt">
        No account? <a href="/register">Register as patient</a>
    </p>

    <div class="demo-box">
        <strong>Demo accounts</strong> (password: <code>password123</code>)
        <ul>
            <li>manager@dpams.test — Manager</li>
            <li>nurse@dpams.test — Staff Nurse</li>
            <li>doctor@dpams.test — Doctor</li>
            <li>patient@dpams.test — Patient</li>
        </ul>
    </div>
</div>
