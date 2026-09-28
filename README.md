# 🩺 DPAMS — Doctor & Patient Appointment Management System

A web-based Hospital Appointment Management System built with **PHP 8 · CodeIgniter 4 · MySQL (XAMPP)** following the MVC architecture and the SDLC Waterfall Model.

## Technology

| Component | Technology |
|---|---|
| Language | PHP 8.2 |
| Framework | CodeIgniter 4.6 |
| Database | MySQL / MariaDB (XAMPP) |
| Styling | Custom CSS (`public/assets/css/style.css`) |
| Server | Apache (XAMPP) |

## Features

- **Local authentication** — register (patients), login, logout; passwords hashed with bcrypt; session-based auth; CSRF protection on all forms.
- **4 roles with role-based access control** — Manager/Representative, Staff Nurse, Doctor, Patient (dedicated dashboard per role).
- **Doctor Management** — profile with description, skills, services, contact info, status; search & filter.
- **Patient Management** — full CRUD by staff; patients maintain their own profile.
- **Appointment Management** — booking with **slot-conflict validation** (same doctor + date + time is rejected), status flow (pending → confirmed → completed / cancelled).
- **Bill Management** — bill amount + payment status stored on each appointment (per the 4-table constraint).
- **Test & Report Management** — prescribe tests on appointments, collect reports with details and/or file upload (PDF/JPG/PNG ≤ 4 MB, validated), protected downloads.
- **Staff management** — manager creates user accounts with any role (a doctor account automatically gets a linked doctor profile), resets passwords, activates/deactivates.

## Database (4 primary tables + users)

```
users        user_id PK, name, email, password_hash, role, status
doctors      doctor_id PK, user_id, doctor_name, description, skills, services, contact_info, status
patients     patient_id PK, user_id, patient_name, contact_info, address, date_of_birth, gender
appointments appointment_id PK, doctor_id FK, patient_id FK, appointment_date, appointment_time,
             status, bill_amount, bill_status, test_name, test_status
reports      report_id PK, appointment_id FK, patient_id FK, test_name, report_details, report_file, report_date
```

Schema file: `database/dpams_schema.sql` · Demo data: `database/seed_demo.sql`

## Installation (XAMPP)

1. **Install XAMPP** (PHP ≥ 8.1 with `intl`, `mysqli`, `mbstring` enabled) and start **Apache** + **MySQL**.
2. **Create the database:**
   ```
   C:\xampp\mysql\bin\mysql.exe -u root < database\dpams_schema.sql
   C:\xampp\mysql\bin\mysql.exe -u root < database\seed_demo.sql
   ```
3. **Serve the `public/` folder.** Two options:
   - `C:\xampp\php\php.exe spark serve --port 8890` (development), or
   - Apache vhost with `DocumentRoot` pointing at `D:/Doctor_and_patient_ams/public`.
4. Open **http://dpams.local** (vhost) or the spark URL.
5. Database credentials live in `.env` (`database.default.*`).

## Demo accounts (password: `password123`)

| Email | Role |
|---|---|
| manager@dpams.test | Manager / Representative |
| nurse@dpams.test | Staff Nurse |
| doctor@dpams.test | Doctor |
| patient@dpams.test | Patient |

## Project structure

```
app/
  Config/        Routes (explicit, auto-routing off), Filters (auth, role), .env
  Controllers/   Auth, Home, Dashboard, Doctors, Patients, Appointments, Reports, Staff
  Filters/       AuthFilter (login required), RoleFilter (role:manager,nurse,...)
  Models/        UserModel, DoctorModel, PatientModel, AppointmentModel, ReportModel
  Views/         layouts/, auth/, home/, dashboard/, doctors/, patients/, appointments/, reports/, staff/
  Helpers/       dpams_helper (roles, badges, formatting)
  Database/      (migrations/seeds namespace)
database/        dpams_schema.sql, seed_demo.sql
public/          index.php, assets/css/style.css
writable/        uploads/reports/ (protected report files), logs/, session/
tests/           smoke_test.sh, workflow_test.sh (bash + curl integration tests)
```

## Testing

With the app running at `http://dpams.local`:

```bash
bash tests/smoke_test.sh      # 52 checks: pages, role access, redirects
bash tests/workflow_test.sh   # 16 checks: real CRUD actions via HTTP
```

## Security notes

- Passwords: `password_hash()` / `password_verify()` (bcrypt).
- All queries go through CodeIgniter's Query Builder (prepared statements).
- CSRF token verified on every POST; forms embed `csrf_field()`.
- Role filter on every restricted route + in-controller ownership checks (doctors see only their appointments, patients only their own records).
- Report files stored outside `public/` and streamed through an authorized controller only.
- Upload validation: MIME type whitelist (PDF/JPG/PNG) and 4 MB size cap.
