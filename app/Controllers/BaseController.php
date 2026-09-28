<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * Provides session, helpers and current-user helpers for all controllers.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'dpams'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = service('session');
    }

    /**
     * Currently logged-in user id (or null).
     */
    protected function userId(): ?int
    {
        $id = session()->get('user_id');

        return $id === null ? null : (int) $id;
    }

    /**
     * Currently logged-in user role (or null).
     */
    protected function userRole(): ?string
    {
        $role = session()->get('role');

        return $role === null ? null : (string) $role;
    }

    /**
     * Currently logged-in user name (or 'Guest').
     */
    protected function userName(): string
    {
        return (string) (session()->get('name') ?? 'Guest');
    }

    /**
     * Patient profile row linked to the logged-in user, creating it lazily
     * for users with the patient role.
     */
    protected function myPatient(): ?object
    {
        if ($this->userRole() !== 'patient') {
            return null;
        }

        $patients = new \App\Models\PatientModel();
        $existing = $patients->where('user_id', $this->userId())->first();

        if ($existing !== null) {
            return $existing;
        }

        // Lazily create a patient profile for this account.
        $patients->insert([
            'user_id'      => $this->userId(),
            'patient_name' => $this->userName(),
        ], false);

        return $patients->where('user_id', $this->userId())->first();
    }

    /**
     * Render a view inside a layout.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): string
    {
        $data['content'] = view($view, $data);

        return view($layout, $data);
    }
}
