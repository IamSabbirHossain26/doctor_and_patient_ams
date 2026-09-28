<?php

namespace App\Controllers;

use App\Models\DoctorModel;

/**
 * Doctor management: public listing/view + manager CRUD.
 */
class Doctors extends BaseController
{
    public function index(): string
    {
        $doctors = new DoctorModel();

        return $this->render('doctors/index', [
            'title'   => 'Doctors',
            'doctors' => $doctors->search(
                trim((string) $this->request->getGet('q')),
                (string) $this->request->getGet('status')
            ),
            'q'       => trim((string) $this->request->getGet('q')),
            'status'  => (string) $this->request->getGet('status'),
        ]);
    }

    public function view(int $id)
    {
        $doctor = (new DoctorModel())->find($id);

        if ($doctor === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Doctor not found');
        }

        return $this->render('doctors/view', ['title' => $doctor->doctor_name, 'doctor' => $doctor]);
    }

    // -----------------------------------------------------------------
    // Manager only below
    // -----------------------------------------------------------------

    public function create(): string
    {
        return $this->render('doctors/form', [
            'title'  => 'Add Doctor',
            'doctor' => null,
        ]);
    }

    public function store()
    {
        $data = $this->postData();

        if (! $this->validateData($data, (new DoctorModel())->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new DoctorModel())->insert($data);

        return redirect()->to('/doctors')->with('success', 'Doctor created successfully.');
    }

    public function edit(int $id)
    {
        $doctor = (new DoctorModel())->find($id);

        if ($doctor === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Doctor not found');
        }

        return $this->render('doctors/form', ['title' => 'Edit Doctor', 'doctor' => $doctor]);
    }

    public function update(int $id)
    {
        $doctorModel = new DoctorModel();

        if ($doctorModel->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Doctor not found');
        }

        $data = $this->postData();

        if (! $this->validateData($data, $doctorModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $doctorModel->update($id, $data);

        return redirect()->to('/doctors')->with('success', 'Doctor updated successfully.');
    }

    public function delete(int $id)
    {
        try {
            (new DoctorModel())->delete($id);

            return redirect()->to('/doctors')->with('success', 'Doctor deleted.');
        } catch (\Throwable $e) {
            return redirect()->to('/doctors')
                ->with('error', 'Cannot delete: this doctor has appointments. Deactivate instead.');
        }
    }

    /**
     * Collect doctor form data.
     */
    private function postData(): array
    {
        return [
            'doctor_name'  => trim((string) $this->request->getPost('doctor_name')),
            'description'  => trim((string) $this->request->getPost('description')),
            'skills'       => trim((string) $this->request->getPost('skills')),
            'services'     => trim((string) $this->request->getPost('services')),
            'contact_info' => trim((string) $this->request->getPost('contact_info')),
            'status'       => $this->request->getPost('status') === 'inactive' ? 'inactive' : 'active',
        ];
    }
}
