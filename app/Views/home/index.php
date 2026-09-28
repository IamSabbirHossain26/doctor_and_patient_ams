<section class="hero">
    <h1>Doctor &amp; Patient Appointment Management System</h1>
    <p>Book appointments with trusted doctors, view bills and collect your test reports — all in one place.</p>
    <div class="hero-actions">
        <?php if (session()->get('isLoggedIn')): ?>
            <a class="btn btn-primary" href="/dashboard">Go to Dashboard</a>
            <a class="btn btn-outline" href="/appointments/book">Book Appointment</a>
        <?php else: ?>
            <a class="btn btn-primary" href="/register">Get Started</a>
            <a class="btn btn-outline" href="/login">Login</a>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <h2 class="section-title">Our Doctors</h2>

    <?php if (empty($doctors)): ?>
        <p class="muted">No doctors available yet.</p>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($doctors as $doctor): ?>
                <div class="card doctor-card">
                    <div class="card-head">
                        <h3><?= dpams_e($doctor->doctor_name) ?></h3>
                        <span class="badge <?= dpams_badge($doctor->status) ?>"><?= dpams_e($doctor->status) ?></span>
                    </div>
                    <?php if (! empty($doctor->skills)): ?>
                        <p class="card-sub"><?= dpams_e($doctor->skills) ?></p>
                    <?php endif; ?>
                    <p class="clamp"><?= dpams_e(mb_strimwidth((string) $doctor->description, 0, 120, '…')) ?></p>
                    <a class="btn btn-sm btn-outline" href="/doctors/view/<?= (int) $doctor->doctor_id ?>">View Profile</a>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="mt"><a href="/doctors">See all doctors →</a></p>
    <?php endif; ?>
</section>

<section class="section features">
    <div class="feature">
        <h3>📅 Easy Booking</h3>
        <p>Choose a doctor, pick a date &amp; time — the system prevents double-booking.</p>
    </div>
    <div class="feature">
        <h3>💰 Bill Tracking</h3>
        <p>Every appointment carries its bill amount and payment status.</p>
    </div>
    <div class="feature">
        <h3>🧪 Test Reports</h3>
        <p>Prescribed tests and reports are attached to your appointment record.</p>
    </div>
</section>
