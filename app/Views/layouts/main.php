<?php
/**
 * Main layout.
 *
 * Expects: $title, $content (view result), optional $hideNav
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= dpams_e($title ?? 'DPAMS') ?> | Doctor &amp; Patient Appointment Management System</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-inner">
            <a class="brand" href="/">🩺 DPAMS</a>

            <?php if (session()->get('isLoggedIn')): ?>
                <nav class="nav-links">
                    <a href="/dashboard">Dashboard</a>
                    <a href="/doctors">Doctors</a>
                    <?php $role = session()->get('role'); ?>
                    <?php if (in_array($role, ['manager', 'nurse', 'doctor'], true)): ?>
                        <a href="/patients">Patients</a>
                    <?php endif; ?>
                    <a href="/appointments">Appointments</a>
                    <a href="/reports">Reports</a>
                    <?php if ($role === 'manager'): ?>
                        <a href="/staff">Staff</a>
                    <?php endif; ?>
                </nav>
                <div class="nav-user">
                    <span class="nav-name"><?= dpams_e(session()->get('name')) ?></span>
                    <span class="badge <?= dpams_badge('active') ?>"><?= dpams_e(dpams_role_label($role)) ?></span>
                    <a class="btn btn-sm btn-outline" href="/logout">Logout</a>
                </div>
            <?php else: ?>
                <nav class="nav-links">
                    <a href="/doctors">Doctors</a>
                    <a href="/about">About</a>
                </nav>
                <div class="nav-user">
                    <a class="btn btn-sm btn-outline" href="/login">Login</a>
                    <a class="btn btn-sm btn-primary" href="/register">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container main-content">
        <!-- Flash messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= dpams_e(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= dpams_e(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="footer">
        <div class="container">
            <span>Doctor &amp; Patient Appointment Management System — PHP · CodeIgniter 4 · MySQL</span>
        </div>
    </footer>
</body>
</html>
