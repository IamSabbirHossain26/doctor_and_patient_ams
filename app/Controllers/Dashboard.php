<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\DoctorModel;
use App\Models\PatientModel;
use App\Models\ReportModel;

/**
 * Role-based dashboards.
 */
class Dashboard extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return match ($this->userRole()) {
            'manager' => $this->manager(),
            'nurse'   => $this->nurse(),
            'doctor'  => $this->doctor(),
            'patient' => $this->patient(),
            default   => redirect()->to('/login'),
        };
    }

    private function manager()
    {
        $appointments = new AppointmentModel();

        $data = [
            'title'  => 'Manager Dashboard',
            'counts' => [
                'doctors'      => (new DoctorModel())->countAllResults(),
                'patients'     => (new PatientModel())->countAllResults(),
                'appointments' => $appointments->counts(),
                'reports'      => (new ReportModel())->countAllResults(),
                'users'        => (new \App\Models\UserModel())->countAllResults(),
            ],
            'recent' => $appointments->forRole(null, null, []),
        ];

        // Only latest 8
        $data['recent'] = array_slice($data['recent'], 0, 8);

        return $this->render('dashboard/manager', $data);
    }

    private function nurse()
    {
        $appointments = new AppointmentModel();
        $today = $appointments->forRole(null, null, ['date' => date('Y-m-d')]);

        $data = [
            'title'  => 'Staff Nurse Dashboard',
            'counts' => [
                'patients'     => (new PatientModel())->countAllResults(),
                'today'        => count($today),
                'appointments' => $appointments->counts(),
                'reports'      => (new ReportModel())->countAllResults(),
            ],
            'today'  => $today,
        ];

        return $this->render('dashboard/nurse', $data);
    }

    private function doctor()
    {
        $doctorModel = new DoctorModel();
        $appointments = new AppointmentModel();

        // Doctor account is matched by email within doctors table contact info,
        // or by dedicated link user_id when present.
        $doctor = $doctorModel->where('user_id', $this->userId())->first();

        $data = [
            'title'  => 'Doctor Dashboard',
            'doctor' => $doctor,
            'counts' => [
                'today'   => $doctor ? count($appointments->todayForDoctor($doctor->doctor_id)) : 0,
                'total'   => $doctor ? count($appointments->forRole($doctor->doctor_id)) : 0,
                'pending' => 0,
            ],
            'today'  => $doctor ? $appointments->todayForDoctor($doctor->doctor_id) : [],
        ];

        if ($doctor) {
            $all = $appointments->forRole($doctor->doctor_id);
            $data['counts']['pending'] = count(array_filter($all, static fn ($a) => $a->status === 'pending'));
        }

        return $this->render('dashboard/doctor', $data);
    }

    private function patient()
    {
        $patient = $this->myPatient();
        $appointments = new AppointmentModel();

        $list = $patient
            ? $appointments->forRole(null, (int) $patient->patient_id)
            : [];

        $counts = [
            'total'     => count($list),
            'pending'   => count(array_filter($list, static fn ($a) => $a->status === 'pending')),
            'confirmed' => count(array_filter($list, static fn ($a) => $a->status === 'confirmed')),
            'unpaid'    => count(array_filter($list, static fn ($a) => $a->bill_status === 'unpaid' && $a->status !== 'cancelled')),
        ];

        $reports = $patient
            ? (new ReportModel())->forRole((int) $patient->patient_id)
            : [];

        return $this->render('dashboard/patient', [
            'title'    => 'My Dashboard',
            'patient'  => $patient,
            'counts'   => $counts,
            'upcoming' => array_slice(array_filter($list, static fn ($a) => $a->status === 'pending' || $a->status === 'confirmed'), 0, 5),
            'reports'  => array_slice($reports, 0, 5),
        ]);
    }
}
