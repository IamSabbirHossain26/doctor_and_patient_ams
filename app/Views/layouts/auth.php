<?php
/**
 * Auth layout: minimal chrome for login/register.
 *
 * Expects: $title, $content
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= dpams_e($title ?? 'DPAMS') ?> | DPAMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-body">
    <main class="auth-wrap">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= dpams_e(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= dpams_e(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</body>
</html>
