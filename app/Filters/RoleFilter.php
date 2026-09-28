<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Role based access control.
 *
 * Usage: filter 'role:manager,nurse' - user must be logged in AND
 * have one of the listed roles. Includes auth check as well.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            session()->set('redirect_url', current_url());

            return redirect()->to('/login')
                ->with('error', 'Please login to continue.');
        }

        $role = (string) session()->get('role');
        $allowed = $arguments ?? [];

        if ($allowed !== [] && ! in_array($role, $allowed, true)) {
            return redirect()->to('/dashboard')
                ->with('error', 'You are not authorized to access that page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
