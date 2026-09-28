<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table         = 'patients';
    protected $primaryKey    = 'patient_id';
    protected $returnType    = 'object';

    protected $allowedFields = ['user_id', 'patient_name', 'contact_info', 'address', 'date_of_birth', 'gender'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'patient_name'  => 'required|min_length[3]|max_length[150]',
        'contact_info'  => 'permit_empty|max_length[180]',
        'address'       => 'permit_empty|max_length[255]',
        'date_of_birth' => 'permit_empty|valid_date[Y-m-d]',
        'gender'        => 'required|in_list[male,female,other]',
    ];

    /**
     * Search listing.
     */
    public function search(string $keyword = ''): array
    {
        $builder = $this->builder();

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('patient_name', $keyword)
                ->orLike('contact_info', $keyword)
                ->orLike('address', $keyword)
                ->groupEnd();
        }

        return $builder->orderBy('patient_name', 'ASC')->get()->getResultObject();
    }

    /**
     * Count patients registered within a period (dashboard stat).
     */
    public function countNew(int $days = 30): int
    {
        return $this->where('created_at >=', date('Y-m-d 00:00:00', strtotime("-{$days} days")))
            ->countAllResults();
    }
}
