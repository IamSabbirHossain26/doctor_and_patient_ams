<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model
{
    protected $table         = 'reports';
    protected $primaryKey    = 'report_id';
    protected $returnType    = 'object';

    protected $allowedFields = ['appointment_id', 'patient_id', 'test_name', 'report_details', 'report_file', 'report_date'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'appointment_id' => 'required|is_natural_no_zero',
        'patient_id'     => 'required|is_natural_no_zero',
        'test_name'      => 'required|min_length[2]|max_length[150]',
        'report_date'    => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    /**
     * Base select with joined names.
     */
    private function baseSelect(): object
    {
        return $this->builder()
            ->select('reports.*, p.patient_name, d.doctor_name, a.appointment_date, a.appointment_time')
            ->join('appointments a', 'a.appointment_id = reports.appointment_id', 'left')
            ->join('patients p', 'p.patient_id = reports.patient_id', 'left')
            ->join('doctors d', 'd.doctor_id = a.doctor_id', 'left');
    }

    /**
     * Role-scoped listing.
     */
    public function forRole(?int $patientId = null, array $filters = []): array
    {
        $builder = $this->baseSelect();

        if ($patientId !== null) {
            $builder->where('reports.patient_id', $patientId);
        }
        if (! empty($filters['keyword'])) {
            $builder->groupStart()
                ->like('reports.test_name', $filters['keyword'])
                ->orLike('p.patient_name', $filters['keyword'])
                ->groupEnd();
        }

        return $builder->orderBy('reports.report_id', 'DESC')->get()->getResultObject();
    }

    /**
     * Single report with joined names.
     */
    public function getWithNames(int $id): ?object
    {
        return $this->baseSelect()->where('report_id', $id)->get()->getRowObject();
    }
}
