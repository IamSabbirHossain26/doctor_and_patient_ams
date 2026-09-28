<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\UserModel;

/**
 * Patient management: staff listing/CRUD + patient self-profile.
 */
class Patients extends BaseController
{
    public function index(): string
    {
        $patients = new PatientModel();

        return $this->render('patients/index', [
            'title'    => 'Patients',
            'patients' => $patients->search(trim((string) $this->request->getGet('q'))),
            'q'        => trim((string) $this->request->getGet('q')),
        ]);
    }

    public function view(int $id)
    {
        $patient = (new PatientModel())->find($id);

        if ($patient === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Patient not found');
        }

        $appointments = (new \App\Models\AppointmentModel())
            ->forRole(null, (int) $patient->patient_id);

        return $this->render('patients/view', [
            'title'        => $patient->patient_name,
            'patient'      => $patient,
            'appointments' => $appointments,
        ]);
    }

    // -----------------------------------------------------------------
    // Manager/Nurse CRUD
    // -----------------------------------------------------------------

    public function create(): string
    {
        return $this->render('patients/form', ['title' => 'Add Patient', 'patient' => null]);
    }

    public function store()
    {
        $patientModel = new PatientModel();
        $data = $this->postData();

        if (! $this->validateData($data, $patientModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $patientModel->insert($data);

        return redirect()->to('/patients')->with('success', 'Patient created successfully.');
    }

    public function edit(int $id)
    {
        $patient = (new PatientModel())->find($id);

        if ($patient === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Patient not found');
        }

        return $this->render('patients/form', ['title' => 'Edit Patient', 'patient' => $patient]);
    }

    public function update(int $id)
    {
        $patientModel = new PatientModel();

        if ($patientModel->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Patient not found');
        }

        $data = $this->postData();

        if (! $this->validateData($data, $patientModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $patientModel->update($id, $data);

        return redirect()->to('/patients')->with('success', 'Patient updated successfully.');
    }

    public function delete(int $id)
    {
        try {
            (new PatientModel())->delete($id);

            return redirect()->to('/patients')->with('success', 'Patient deleted.');
        } catch (\Throwable $e) {
            return redirect()->to('/patients')
                ->with('error', 'Cannot delete: this patient has appointments or reports.');
        }
    }

    // -----------------------------------------------------------------
    // Patient self profile
    // -----------------------------------------------------------------

    public function myProfile()
    {
        $patient = $this->myPatient();

        return $this->render('patients/my_profile', [
            'title'   => 'My Profile',
            'patient' => $patient,
        ]);
    }

    public function updateMyProfile()
    {
        $patient = $this->myPatient();
        $patientModel = new PatientModel();

        $data = $this->postData();

        if (! $this->validateData($data, $patientModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $patientModel->update((int) $patient->patient_id, $data);

        return redirect()->to('/my-profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Collect patient form data.
     */
    private function postData(): array
    {
        $dob = (string) $this->request->getPost('date_of_birth');

        return [
            'patient_name'  => trim((string) $this->request->getPost('patient_name')),
            'contact_info'  => trim((string) $this->request->getPost('contact_info')),
            'address'       => trim((string) $this->request->getPost('address')),
            'date_of_birth' => $dob !== '' ? $dob : null,
            'gender'        => (string) $this->request->getPost('gender') ?: 'male',
        ];
    }
}
