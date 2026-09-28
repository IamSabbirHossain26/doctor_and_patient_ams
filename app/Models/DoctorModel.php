<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorModel extends Model
{
    protected $table         = 'doctors';
    protected $primaryKey    = 'doctor_id';
    protected $returnType    = 'object';

    protected $allowedFields = ['user_id', 'doctor_name', 'description', 'skills', 'services', 'contact_info', 'status'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'doctor_name'  => 'required|min_length[3]|max_length[150]',
        'description'  => 'permit_empty|max_length[2000]',
        'skills'       => 'permit_empty|max_length[255]',
        'services'     => 'permit_empty|max_length[2000]',
        'contact_info' => 'permit_empty|max_length[180]',
        'status'       => 'required|in_list[active,inactive]',
    ];

    /**
     * Search + filter listing with pagination support.
     */
    public function search(string $keyword = '', string $status = ''): array
    {
        $builder = $this->builder();

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('doctor_name', $keyword)
                ->orLike('skills', $keyword)
                ->orLike('services', $keyword)
                ->groupEnd();
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $builder->where('status', $status);
        }

        return $builder->orderBy('doctor_name', 'ASC')->get()->getResultObject();
    }

    /**
     * Active doctors only (for appointment booking).
     */
    public function activeDoctors(): array
    {
        return $this->where('status', 'active')->orderBy('doctor_name', 'ASC')->findAll();
    }
}
