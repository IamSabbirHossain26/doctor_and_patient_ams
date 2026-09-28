<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\ReportModel;

/**
 * Test & Report management: listing, upload, download, delete.
 */
class Reports extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $patientId = null;

        if ($this->userRole() === 'patient') {
            $patient = $this->myPatient();
            $patientId = $patient ? (int) $patient->patient_id : -1;
        }

        return $this->render('reports/index', [
            'title'  => 'Test Reports',
            'items'  => (new ReportModel())->forRole($patientId, [
                'keyword' => trim((string) ($this->request->getGet('q') ?? '')),
            ]),
            'q'      => trim((string) ($this->request->getGet('q') ?? '')),
            'canManage' => in_array($this->userRole(), ['manager', 'nurse'], true),
        ]);
    }

    public function view(int $id)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $report = (new ReportModel())->getWithNames($id);

        if ($report === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Report not found');
        }

        if (! $this->canAccess($report)) {
            return redirect()->to('/dashboard')->with('error', 'Not authorized.');
        }

        return $this->render('reports/view', ['title' => 'Report #' . $id, 'report' => $report]);
    }

    // -----------------------------------------------------------------
    // Manager / Nurse
    // -----------------------------------------------------------------

    public function create()
    {
        $appointments = new AppointmentModel();

        // Only appointments that do not have a report yet.
        $available = $appointments->builder()
            ->select('appointments.*, d.doctor_name, p.patient_name')
            ->join('doctors d', 'd.doctor_id = appointments.doctor_id', 'left')
            ->join('patients p', 'p.patient_id = appointments.patient_id', 'left')
            ->where('appointments.status !=', 'cancelled')
            ->whereNotIn('appointments.appointment_id', function ($builder) {
                return $builder->select('appointment_id')->from('reports');
            })
            ->orderBy('appointments.appointment_date', 'DESC')
            ->get()->getResultObject();

        return $this->render('reports/form', [
            'title'        => 'Upload Report',
            'report'       => null,
            'appointments' => $available,
        ]);
    }

    public function store()
    {
        $appointmentId = (int) $this->request->getPost('appointment_id');
        $appointment = (new AppointmentModel())->find($appointmentId);

        if ($appointment === null) {
            return redirect()->back()->withInput()->with('error', 'Invalid appointment.');
        }

        if ((new ReportModel())->where('appointment_id', $appointmentId)->first() !== null) {
            return redirect()->back()->withInput()
                ->with('error', 'This appointment already has a report.');
        }

        $data = [
            'appointment_id' => $appointmentId,
            'patient_id'     => (int) $appointment->patient_id,
            'test_name'      => trim((string) $this->request->getPost('test_name')),
            'report_details' => trim((string) $this->request->getPost('report_details')),
            'report_date'    => date('Y-m-d H:i:s'),
        ];

        if ($data['test_name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Test name is required.');
        }

        // Default test name from the appointment if not provided.
        if ($data['test_name'] === '' && ! empty($appointment->test_name)) {
            $data['test_name'] = $appointment->test_name;
        }

        // Optional file upload (pdf/jpg/png, max 4 MB).
        $file = $this->request->getFile('report_file');
        if ($file !== null && $file->isValid()) {
            $validation = service('validation');
            $rules = [
                'report_file' => 'uploaded[report_file]|max_size[report_file,4096]'
                    . '|mime_in[report_file,application/pdf,image/jpeg,image/png]',
            ];

            if (! $this->validate(['report_file' => $file->getName()], $rules)) {
                return redirect()->back()->withInput()
                    ->with('error', 'Invalid file. Allowed: PDF, JPG, PNG up to 4 MB.');
            }

            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/reports', $newName);
            $data['report_file'] = $newName;
        }

        try {
            (new ReportModel())->insert($data);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Could not save the report. Please try again.');
        }

        // Mark the test as collected on the appointment.
        (new AppointmentModel())->update($appointmentId, [
            'test_status' => 'collected',
            'test_name'   => $appointment->test_name ?: $data['test_name'],
        ]);

        return redirect()->to('/reports')->with('success', 'Report saved successfully.');
    }

    public function edit(int $id)
    {
        $report = (new ReportModel())->find($id);

        if ($report === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Report not found');
        }

        $appointment = (new AppointmentModel())
            ->getWithNames((int) $report->appointment_id);

        return $this->render('reports/form', [
            'title'        => 'Edit Report',
            'report'       => $report,
            'appointments' => $appointment ? [$appointment] : [],
        ]);
    }

    public function update(int $id)
    {
        $reportModel = new ReportModel();
        $report = $reportModel->find($id);

        if ($report === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Report not found');
        }

        $data = [
            'test_name'      => trim((string) $this->request->getPost('test_name')),
            'report_details' => trim((string) $this->request->getPost('report_details')),
        ];

        if ($data['test_name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Test name is required.');
        }

        // Replace the file if a new one is uploaded.
        $file = $this->request->getFile('report_file');
        if ($file !== null && $file->isValid()) {
            $rules = [
                'report_file' => 'max_size[report_file,4096]'
                    . '|mime_in[report_file,application/pdf,image/jpeg,image/png]',
            ];

            if (! $this->validate(['report_file' => $file->getName()], $rules)) {
                return redirect()->back()->withInput()
                    ->with('error', 'Invalid file. Allowed: PDF, JPG, PNG up to 4 MB.');
            }

            // Remove old file.
            if (! empty($report->report_file)) {
                @unlink(WRITEPATH . 'uploads/reports/' . $report->report_file);
            }

            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/reports', $newName);
            $data['report_file'] = $newName;
        }

        $reportModel->update($id, $data);

        return redirect()->to('/reports')->with('success', 'Report updated successfully.');
    }

    public function delete(int $id)
    {
        $reportModel = new ReportModel();
        $report = $reportModel->find($id);

        if ($report === null) {
            return redirect()->to('/reports')->with('error', 'Report not found.');
        }

        if (! empty($report->report_file)) {
            @unlink(WRITEPATH . 'uploads/reports/' . $report->report_file);
        }

        try {
            $reportModel->delete($id);

            // Reset test status on the appointment.
            (new AppointmentModel())->update((int) $report->appointment_id, ['test_status' => 'pending']);
        } catch (\Throwable $e) {
            return redirect()->to('/reports')->with('error', 'Could not delete the report.');
        }

        return redirect()->to('/reports')->with('success', 'Report deleted.');
    }

    /**
     * Download / stream the report file (protected access).
     */
    public function download(int $id)
    {
        $report = (new ReportModel())->getWithNames($id);

        if ($report === null || empty($report->report_file)) {
            return redirect()->to('/reports')->with('error', 'No file attached to this report.');
        }

        if (! $this->canAccess($report)) {
            return redirect()->to('/dashboard')->with('error', 'Not authorized.');
        }

        $path = WRITEPATH . 'uploads/reports/' . $report->report_file;

        if (! is_file($path)) {
            return redirect()->to('/reports')->with('error', 'File is missing on the server.');
        }

        return $this->response->download($path, null);
    }

    /**
     * Access check: manager/nurse all; doctor via their appointment;
     * patient only their own reports.
     */
    private function canAccess(object $report): bool
    {
        if (in_array($this->userRole(), ['manager', 'nurse'], true)) {
            return true;
        }

        if ($this->userRole() === 'patient') {
            $patient = $this->myPatient();

            return $patient && (int) $report->patient_id === (int) $patient->patient_id;
        }

        if ($this->userRole() === 'doctor') {
            $doctor = (new \App\Models\DoctorModel())->where('user_id', $this->userId())->first();
            $appointment = (new AppointmentModel())->find((int) $report->appointment_id);

            return $doctor && $appointment && (int) $appointment->doctor_id === (int) $doctor->doctor_id;
        }

        return false;
    }
}
