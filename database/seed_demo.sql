-- =====================================================================
-- DPAMS — Demo data seed
-- Usage: C:\xampp\mysql\bin\mysql.exe -u root < database\seed_demo.sql
--
-- All demo users have password: password123
-- (bcrypt hashes generated with PHP password_hash)
-- =====================================================================

USE dpams_db;

-- ---------------------------------------------------------------------
-- Users (password for all: password123)
-- ---------------------------------------------------------------------
INSERT INTO users (name, email, password_hash, role, status) VALUES
('Hospital Manager', 'manager@dpams.test', '$2y$10$nRI5.ChoIkrMKLh9/VM.QuWBa0c7aNWofHNy7Cp3zVcpGI7DjOSAW', 'manager', 'active'),
('Nima Nurse',       'nurse@dpams.test',   '$2y$10$nRI5.ChoIkrMKLh9/VM.QuWBa0c7aNWofHNy7Cp3zVcpGI7DjOSAW', 'nurse',   'active'),
('Daniel Doctor',    'doctor@dpams.test',  '$2y$10$nRI5.ChoIkrMKLh9/VM.QuWBa0c7aNWofHNy7Cp3zVcpGI7DjOSAW', 'doctor',  'active'),
('Paula Patient',    'patient@dpams.test', '$2y$10$nRI5.ChoIkrMKLh9/VM.QuWBa0c7aNWofHNy7Cp3zVcpGI7DjOSAW', 'patient', 'active')
ON DUPLICATE KEY UPDATE role = VALUES(role);

-- ---------------------------------------------------------------------
-- Patients
-- ---------------------------------------------------------------------
INSERT INTO patients (user_id, patient_name, contact_info, address, date_of_birth, gender) VALUES
(4, 'Paula Patient', '01711-000004', 'House 4, Road 4, Dhanmondi, Dhaka', '1998-04-12', 'female'),
(NULL, 'Karim Uddin', '01711-000005', 'Banani, Dhaka', '1985-09-30', 'male')
ON DUPLICATE KEY UPDATE patient_name = VALUES(patient_name);

-- ---------------------------------------------------------------------
-- Doctors (user_id 3 = Daniel Doctor account)
-- ---------------------------------------------------------------------
INSERT INTO doctors (user_id, doctor_name, description, skills, services, contact_info, status) VALUES
(3, 'Dr. Daniel Doctor',
 'Senior consultant with 12 years of experience in internal medicine and cardiology. Known for patient-friendly diagnosis and careful follow-up.',
 'Cardiology, Internal Medicine, Echocardiography',
 'Heart checkup, ECG, Echocardiography, Blood pressure management, Diabetes consultation',
 'doctor@dpams.test / 01711-000003', 'active'),
(NULL, 'Dr. Ayesha Rahman',
 'Pediatric specialist focused on child nutrition, vaccination and growth monitoring. 8 years of practice at a tertiary hospital.',
 'Pediatrics, Child Nutrition',
 'Child checkup, Vaccination, Newborn care, Growth assessment',
 '01711-000002', 'active'),
(NULL, 'Dr. Mahmud Hasan',
 'Orthopedic surgeon specializing in sports injuries, fracture management and joint replacement.',
 'Orthopedics, Sports Medicine, Joint Replacement',
 'Fracture care, Arthroscopy, Knee replacement, Back pain treatment',
 '01711-000001', 'active');

-- ---------------------------------------------------------------------
-- Appointments (dates relative to today)
-- ---------------------------------------------------------------------
INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, status, bill_amount, bill_status, test_name, test_status) VALUES
(1, 1, CURDATE(),                         '10:00:00', 'confirmed', 1200.00, 'unpaid',  'CBC',          'pending'),
(1, 2, CURDATE() + INTERVAL 1 DAY,        '11:30:00', 'pending',   900.00,  'unpaid',  NULL,           'none'),
(2, 2, CURDATE() + INTERVAL 2 DAY,        '09:00:00', 'pending',   800.00,  'unpaid',  'X-Ray Chest',  'pending'),
(3, 1, CURDATE() - INTERVAL 7 DAY,        '15:00:00', 'completed', 1500.00, 'paid',    'MRI Knee',     'collected'),
(2, 1, CURDATE() - INTERVAL 14 DAY,       '12:00:00', 'completed', 700.00,  'paid',    'Urine R/E',    'collected');

-- ---------------------------------------------------------------------
-- Reports (linked to completed appointments)
-- ---------------------------------------------------------------------
INSERT INTO reports (appointment_id, patient_id, test_name, report_details, report_file, report_date) VALUES
(4, 1, 'MRI Knee',
 'Moderate cartilage thinning at medial compartment. No acute ligament tear. Suggest physiotherapy for 6 weeks and follow-up review.',
 NULL, NOW() - INTERVAL 6 DAY),
(5, 1, 'Urine R/E',
 'Color: yellow. Protein: nil. Sugar: nil. Pus cells: 2-4/HPF. No bacteria seen. Within normal limits.',
 NULL, NOW() - INTERVAL 13 DAY);

-- ---------------------------------------------------------------------
-- Link demo patient user to patient row
-- (already handled above via user_id = 4)
-- ---------------------------------------------------------------------
