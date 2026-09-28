-- =====================================================================
-- Doctor & Patient Appointment Management System (DPAMS)
-- Database schema for XAMPP / MariaDB
-- Usage:  C:\xampp\mysql\bin\mysql.exe -u root < database\dpams_schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS dpams_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE dpams_db;

-- ---------------------------------------------------------------------
-- Users (authentication + role management; staff have no separate table)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120)  NOT NULL,
    email         VARCHAR(180)  NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('manager','nurse','doctor','patient') NOT NULL DEFAULT 'patient',
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Doctors
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctors (
    doctor_id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NULL,
    doctor_name  VARCHAR(150) NOT NULL,
    description  TEXT         NULL,
    skills       VARCHAR(255) NULL,
    services     TEXT         NULL,
    contact_info VARCHAR(180) NULL,
    status       ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (doctor_id),
    UNIQUE KEY uq_doctors_user (user_id),
    KEY idx_doctors_status (status)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Patients
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS patients (
    patient_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED NULL,
    patient_name   VARCHAR(150) NOT NULL,
    contact_info   VARCHAR(180) NULL,
    address        VARCHAR(255) NULL,
    date_of_birth  DATE         NULL,
    gender         ENUM('male','female','other') NOT NULL DEFAULT 'male',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (patient_id),
    UNIQUE KEY uq_patients_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Appointments (bill + test summary kept here per 4-table constraint)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    doctor_id        INT UNSIGNED NOT NULL,
    patient_id       INT UNSIGNED NOT NULL,
    appointment_date DATE         NOT NULL,
    appointment_time TIME         NOT NULL,
    status           ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    bill_amount      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    bill_status      ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
    test_name        VARCHAR(150) NULL,
    test_status      ENUM('none','pending','collected') NOT NULL DEFAULT 'none',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (appointment_id),
    KEY fk_appointments_doctor (doctor_id),
    KEY fk_appointments_patient (patient_id),
    KEY idx_appointments_date (appointment_date),
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id)
        REFERENCES doctors (doctor_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id)
        REFERENCES patients (patient_id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Reports (test reports; one report per appointment in minimal design)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    report_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_id INT UNSIGNED NOT NULL,
    patient_id     INT UNSIGNED NOT NULL,
    test_name      VARCHAR(150) NOT NULL,
    report_details TEXT         NULL,
    report_file    VARCHAR(255) NULL,
    report_date    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (report_id),
    UNIQUE KEY uq_reports_appointment (appointment_id),
    KEY fk_reports_patient (patient_id),
    CONSTRAINT fk_reports_appointment FOREIGN KEY (appointment_id)
        REFERENCES appointments (appointment_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reports_patient FOREIGN KEY (patient_id)
        REFERENCES patients (patient_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
