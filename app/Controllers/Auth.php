<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\UserModel;

/**
 * Authentication: login, registration (patients), logout.
 */
class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return $this->render('auth/login', ['title' => 'Login'], 'layouts/auth');
    }

    public function attemptLogin()
    {
        $this->request->setLocale('en');

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = trim($this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $users = new UserModel();
        $user  = $users->findByEmail($email);

        if ($user === null || ! password_verify($password, $user->password_hash)) {
            return redirect()->back()->withInput()
                ->with('error', 'Invalid email or password.');
        }

        $this->startSession($user);

        return redirect()->to(session()->get('redirect_url') ?? $this->dashboardFor($user->role))
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return $this->render('auth/register', ['title' => 'Register'], 'layouts/auth');
    }

    public function attemptRegister()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[120]',
            'email'    => 'required|valid_email|max_length[180]|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm'  => 'required|matches[password]',
            'gender'   => 'required|in_list[male,female,other]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = new UserModel();

        $db = db_connect();
        $db->transBegin();

        try {
            $users->insert([
                'name'     => trim($this->request->getPost('name')),
                'email'    => strtolower(trim($this->request->getPost('email'))),
                'password' => (string) $this->request->getPost('password'),
                'role'     => 'patient',
            ], false);

            $userId = (int) $db->insertID();

            // Create the linked patient profile right away.
            $patientName = trim($this->request->getPost('name'));
            (new PatientModel())->insert([
                'user_id'      => $userId,
                'patient_name' => $patientName,
                'contact_info' => trim((string) $this->request->getPost('contact_info')),
                'address'      => trim((string) $this->request->getPost('address')),
                'gender'       => $this->request->getPost('gender'),
                'date_of_birth' => $this->request->getPost('date_of_birth') ?: null,
            ], false);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }

        $user = $users->find($userId);
        $this->startSession($user);

        return redirect()->to('/dashboard')
            ->with('success', 'Registration successful. Welcome, ' . $user->name . '!');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    /**
     * Persist user data in the session.
     */
    private function startSession(object $user): void
    {
        session()->set([
            'user_id'     => (int) $user->user_id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'isLoggedIn'  => true,
        ]);
    }

    /**
     * Dashboard route per role.
     */
    private function dashboardFor(string $role): string
    {
        return '/dashboard';
    }
}
