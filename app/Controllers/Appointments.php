<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\DoctorModel;
use App\Models\PatientModel;
use App\Models\ReportModel;

/**
 * Appointment management: booking + status/bill/test workflows.
 */
class Appointments extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new AppointmentModel();

        $doctorId  = null;
        $patientId = null;

        if ($this->userRole() === 'doctor') {
            $doctor = (new DoctorModel())->where('user_id', $this->userId())->first();
            $doctorId = $doctor ? (int) $doctor->doctor_id : -1;
        } elseif ($this->userRole() === 'patient') {
            $patient = $this->myPatient();
            $patientId = $patient ? (int) $patient->patient_id : -1;
        }

        $filters = [
            'date'       => (string) ($this->request->getGet('date') ?? ''),
            'status'     => (string) ($this->request->getGet('status') ?? ''),
            'bill_status' => (string) ($this->request->getGet('bill_status') ?? ''),
        ];

        return $this->render('appointments/index', [
            'title'    => 'Appointments',
            'items'    => $model->forRole($doctorId, $patientId, $filters),
            'filters'  => $filters,
            'canManage' => in_array($this->userRole(), ['manager', 'nurse', 'doctor'], true),
        ]);
    }

    public function view(int $id)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new AppointmentModel();
        $appointment = $model->getWithNames($id);

        if ($appointment === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Appointment not found');
        }

        // Access control: patients see only theirs, doctors only their own.
        if ($this->userRole() === 'patient') {
            $patient = $this->myPatient();
            if (! $patient || (int) $appointment->patient_id !== (int) $patient->patient_id) {
                return redirect()->to('/dashboard')->with('error', 'Not authorized.');
            }
        } elseif ($this->userRole() === 'doctor') {
            $doctor = (new DoctorModel())->where('user_id', $this->userId())->first();
            if (! $doctor || (int) $appointment->doctor_id !== (int) $doctor->doctor_id) {
                return redirect()->to('/dashboard')->with('error', 'Not authorized.');
            }
        }

        $report = (new ReportModel())->where('appointment_id', $id)->first();

        return $this->render('appointments/view', [
            'title'       => 'Appointment #' . $id,
            'appointment' => $appointment,
            'report'      => $report,
            'canManage'   => in_array($this->userRole(), ['manager', 'nurse', 'doctor'], true),
        ]);
    }

    // -----------------------------------------------------------------
    // Booking (any logged-in user; patients book for themselves)
    // -----------------------------------------------------------------

    public function create()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $patientId = null;

        if ($this->userRole() === 'patient') {
            $patient = $this->myPatient();
            $patientId = $patient ? (int) $patient->patient_id : null;
        }

        return $this->render('appointments/form', [
            'title'     => 'Book Appointment',
            'doctors'   => (new DoctorModel())->activeDoctors(),
            'patients'  => in_array($this->userRole(), ['manager', 'nurse'], true)
                ? (new PatientModel())->orderBy('patient_name', 'ASC')->findAll()
                : [],
            'patientId' => $patientId,
            'selectedDoctor' => (int) ($this->request->getGet('doctor') ?? 0),
            'appointment' => null,
        ]);
    }

    public function store()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $doctorId  = (int) $this->request->getPost('doctor_id');
        $date      = (string) $this->request->getPost('appointment_date');
        $time      = (string) $this->request->getPost('appointment_time');

        // Resolve patient: patients book for themselves.
        if ($this->userRole() === 'patient') {
            $patient = $this->myPatient();
            $patientId = $patient ? (int) $patient->patient_id : 0;
        } else {
            $patientId = (int) $this->request->getPost('patient_id');
        }

        $rules = [
            'doctor_id'        => 'required|is_natural_no_zero',
            'patient_id'       => 'required|is_natural_no_zero',
            'appointment_date' => 'required|valid_date[Y-m-d]',
            'appointment_time' => 'required|valid_date[H:i]',
        ];

        $data = [
            'doctor_id'        => $doctorId,
            'patient_id'       => $patientId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'status'           => 'pending',
            'bill_amount'      => 0.00,
            'bill_status'      => 'unpaid',
            'test_name'        => null,
            'test_status'      => 'none',
        ];

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Date must not be in the past.
        if ($date < date('Y-m-d')) {
            return redirect()->back()->withInput()
                ->with('error', 'Appointment date cannot be in the past.');
        }

        // Slot conflict validation (FR: recommended control).
        $model = new AppointmentModel();
        if ($model->hasConflict($doctorId, $date, $time)) {
            return redirect()->back()->withInput()
                ->with('error', 'That time slot is already booked for this doctor. Please pick another.');
        }

        try {
            $model->insert($data);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not create the appointment. Please try again.');
        }

        return redirect()->to('/appointments')
            ->with('success', 'Appointment booked successfully. Status: pending confirmation.');
    }

    // -----------------------------------------------------------------
    // Management (manager / nurse / doctor)
    // -----------------------------------------------------------------

    public function updateStatus(int $id)
    {
        if (! $this->canManageAppointment($id)) {
            return redirect()->to('/dashboard')->with('error', 'Not authorized.');
        }

        $status = (string) $this->request->getPost('status');

        if (! in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        (new AppointmentModel())->update($id, ['status' => $status]);

        return redirect()->back()->with('success', 'Appointment status updated.');
    }

    public function updateBill(int $id)
    {
        if (! $this->canManageAppointment($id)) {
            return redirect()->to('/dashboard')->with('error', 'Not authorized.');
        }

        $amount = (float) $this->request->getPost('bill_amount');
        $billStatus = $this->request->getPost('bill_status') === 'paid' ? 'paid' : 'unpaid';

        if ($amount < 0 || $amount > 99999999.99) {
            return redirect()->back()->with('error', 'Invalid bill amount.');
        }

        (new AppointmentModel())->update($id, [
            'bill_amount' => $amount,
            'bill_status' => $billStatus,
        ]);

        return redirect()->back()->with('success', 'Bill updated successfully.');
    }

    public function updateTest(int $id)
    {
        if (! $this->canManageAppointment($id)) {
            return redirect()->to('/dashboard')->with('error', 'Not authorized.');
        }

        $testName   = trim((string) $this->request->getPost('test_name'));
        $testStatus = (string) $this->request->getPost('test_status');

        if (! in_array($testStatus, ['none', 'pending', 'collected'], true)) {
            return redirect()->back()->with('error', 'Invalid test status.');
        }

        if ($testName === '') {
            $testStatus = 'none';
        }

        (new AppointmentModel())->update($id, [
            'test_name'   => $testName === '' ? null : $testName,
            'test_status' => $testStatus,
        ]);

        return redirect()->back()->with('success', 'Test information updated.');
    }

    public function delete(int $id)
    {
        if (! in_array($this->userRole(), ['manager', 'nurse'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Not authorized.');
        }

        $model = new AppointmentModel();

        try {
            $model->delete($id);

            return redirect()->to('/appointments')->with('success', 'Appointment deleted.');
        } catch (\Throwable $e) {
            return redirect()->to('/appointments')
                ->with('error', 'Cannot delete: report exists for this appointment. Delete the report first.');
        }
    }

    /**
     * Authorization helper: manager/nurse can manage all;
     * doctor can manage only their own appointments.
     */
    private function canManageAppointment(int $id): bool
    {
        if (! session()->get('isLoggedIn')) {
            return false;
        }

        if (in_array($this->userRole(), ['manager', 'nurse'], true)) {
            return true;
        }

        if ($this->userRole() === 'doctor') {
            $doctor = (new DoctorModel())->where('user_id', $this->userId())->first();
            $appointment = (new AppointmentModel())->find($id);

            return $appointment && $doctor && (int) $appointment->doctor_id === (int) $doctor->doctor_id;
        }

        return false;
    }
}
