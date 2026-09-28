<?php

namespace App\Controllers;

/**
 * Public pages.
 */
class Home extends BaseController
{
    public function index(): string
    {
        $doctors = (new \App\Models\DoctorModel())
            ->where('status', 'active')
            ->orderBy('doctor_name', 'ASC')
            ->findAll(6);

        return $this->render('home/index', [
            'title'   => 'Welcome',
            'doctors' => $doctors,
        ]);
    }

    public function about(): string
    {
        return $this->render('home/about', ['title' => 'About']);
    }
}
