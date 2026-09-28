<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * Staff (user account) management - manager only.
 */
class Staff extends BaseController
{
    public function index(): string
    {
        $users = new UserModel();

        return $this->render('staff/index', [
            'title' => 'Staff & Users',
            'users' => $users->orderBy('user_id', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return $this->render('staff/form', ['title' => 'Add Staff', 'user' => null]);
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[120]',
            'email'    => 'required|valid_email|max_length[180]|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[manager,nurse,doctor,patient]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role  = (string) $this->request->getPost('role');
        $email = strtolower(trim((string) $this->request->getPost('email')));

        $db = db_connect();
        $db->transBegin();

        try {
            $users = new UserModel();
            $users->insert([
                'name'     => trim((string) $this->request->getPost('name')),
                'email'    => $email,
                'password' => (string) $this->request->getPost('password'),
                'role'     => $role,
                'status'   => $this->request->getPost('status') === 'inactive' ? 'inactive' : 'active',
            ], false);

            $userId = (int) $db->insertID();

            // A doctor account gets a linked doctors profile; a patient account
            // gets a linked patients profile.
            if ($role === 'doctor') {
                (new \App\Models\DoctorModel())->insert([
                    'user_id'      => $userId,
                    'doctor_name'  => trim((string) $this->request->getPost('name')),
                    'contact_info' => $email,
                    'description'  => '',
                    'skills'       => '',
                    'services'     => '',
                ], false);
            } elseif ($role === 'patient') {
                (new \App\Models\PatientModel())->insert([
                    'user_id'      => $userId,
                    'patient_name' => trim((string) $this->request->getPost('name')),
                    'contact_info' => $email,
                ], false);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', 'Could not create the staff account.');
        }

        return redirect()->to('/staff')->with('success', 'Staff account created successfully.');
    }

    public function edit(int $id)
    {
        $user = (new UserModel())->find($id);

        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User not found');
        }

        return $this->render('staff/form', ['title' => 'Edit Staff', 'user' => $user]);
    }

    public function update(int $id)
    {
        $users = new UserModel();

        if ($users->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User not found');
        }

        $rules = [
            'name'  => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[180]|is_unique[users.email,user_id,' . $id . ']',
            'role'  => 'required|in_list[manager,nurse,doctor,patient]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users->update($id, [
            'name'   => trim((string) $this->request->getPost('name')),
            'email'  => strtolower(trim((string) $this->request->getPost('email'))),
            'role'   => (string) $this->request->getPost('role'),
            'status' => $this->request->getPost('status') === 'inactive' ? 'inactive' : 'active',
        ]);

        return redirect()->to('/staff')->with('success', 'Staff account updated successfully.');
    }

    public function delete(int $id)
    {
        if ((int) $id === (int) session()->get('user_id')) {
            return redirect()->to('/staff')->with('error', 'You cannot delete your own account.');
        }

        $users = new UserModel();

        try {
            $users->delete($id);

            return redirect()->to('/staff')->with('success', 'User deleted.');
        } catch (\Throwable $e) {
            return redirect()->to('/staff')->with('error', 'Could not delete the user.');
        }
    }

    public function resetPassword(int $id)
    {
        $password = (string) $this->request->getPost('password');

        if (strlen($password) < 6) {
            return redirect()->back()->with('error', 'Password must be at least 6 characters.');
        }

        (new UserModel())->updatePassword($id, $password);

        return redirect()->to('/staff')->with('success', 'Password reset successfully.');
    }
}
