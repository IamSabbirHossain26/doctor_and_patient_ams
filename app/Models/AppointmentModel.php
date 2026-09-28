<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table         = 'appointments';
    protected $primaryKey    = 'appointment_id';
    protected $returnType    = 'object';

    protected $allowedFields = [
        'doctor_id', 'patient_id', 'appointment_date', 'appointment_time',
        'status', 'bill_amount', 'bill_status', 'test_name', 'test_status',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'doctor_id'        => 'required|is_natural_no_zero',
        'patient_id'       => 'required|is_natural_no_zero',
        'appointment_date' => 'required|valid_date[Y-m-d]',
        'appointment_time' => 'required|valid_date[H:i]',
        'status'           => 'required|in_list[pending,confirmed,completed,cancelled]',
    ];

    /**
     * Base select with joined names.
     */
    private function baseSelect(): object
    {
        return $this->builder()
            ->select('appointments.*, d.doctor_name, p.patient_name, p.contact_info AS patient_contact')
            ->join('doctors d', 'd.doctor_id = appointments.doctor_id', 'left')
            ->join('patients p', 'p.patient_id = appointments.patient_id', 'left');
    }

    /**
     * Role-scoped listing with optional filters.
     *
     * @param int|null $doctorId  limit to doctor (role doctor)
     * @param int|null $patientId limit to patient (role patient)
     */
    public function forRole(?int $doctorId = null, ?int $patientId = null, array $filters = []): array
    {
        $builder = $this->baseSelect();

        if ($doctorId !== null) {
            $builder->where('appointments.doctor_id', $doctorId);
        }
        if ($patientId !== null) {
            $builder->where('appointments.patient_id', $patientId);
        }
        if (! empty($filters['date'])) {
            $builder->where('appointments.appointment_date', $filters['date']);
        }
        if (! empty($filters['status'])) {
            $builder->where('appointments.status', $filters['status']);
        }
        if (! empty($filters['bill_status'])) {
            $builder->where('appointments.bill_status', $filters['bill_status']);
        }
        if (! empty($filters['keyword'])) {
            $builder->groupStart()
                ->like('d.doctor_name', $filters['keyword'])
                ->orLike('p.patient_name', $filters['keyword'])
                ->groupEnd();
        }

        return $builder->orderBy('appointments.appointment_date', 'DESC')
            ->orderBy('appointments.appointment_time', 'DESC')
            ->orderBy('appointments.appointment_id', 'DESC')
            ->get()->getResultObject();
    }

    /**
     * Single appointment with joined names.
     */
    public function getWithNames(int $id): ?object
    {
        return $this->baseSelect()->where('appointment_id', $id)->get()->getRowObject();
    }

    /**
     * Slot conflict check: same doctor, same date & time,
     * excluding cancelled appointments and a specific appointment id.
     */
    public function hasConflict(int $doctorId, string $date, string $time, ?int $exceptId = null): bool
    {
        $builder = $this->builder()
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->where('status !=', 'cancelled');

        if ($exceptId !== null) {
            $builder->where('appointment_id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Next appointment for a doctor (dashboard).
     */
    public function nextForDoctor(int $doctorId): ?object
    {
        return $this->baseSelect()
            ->where('appointments.doctor_id', $doctorId)
            ->where('appointments.appointment_date >=', date('Y-m-d'))
            ->whereIn('appointments.status', ['pending', 'confirmed'])
            ->orderBy('appointments.appointment_date', 'ASC')
            ->orderBy('appointments.appointment_time', 'ASC')
            ->get(0, 1)
            ->getRowObject();
    }

    /**
     * Today's appointments for a doctor (dashboard).
     */
    public function todayForDoctor(int $doctorId): array
    {
        return $this->baseSelect()
            ->where('appointments.doctor_id', $doctorId)
            ->where('appointments.appointment_date', date('Y-m-d'))
            ->orderBy('appointments.appointment_time', 'ASC')
            ->get()->getResultObject();
    }

    /**
     * Dashboard counters.
     */
    public function counts(): object
    {
        $row = $this->builder()->select([
            'COUNT(*) AS total',
            "SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending",
            "SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed",
            "SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed",
            "SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled",
            "SUM(CASE WHEN bill_status = 'unpaid' AND status != 'cancelled' THEN 1 ELSE 0 END) AS unpaid",
            'COALESCE(SUM(CASE WHEN bill_status = \'paid\' THEN bill_amount ELSE 0 END), 0) AS revenue',
        ])->get()->getRowObject();

        foreach (['total', 'pending', 'confirmed', 'completed', 'cancelled', 'unpaid', 'revenue'] as $key) {
            $row->{$key} = $row->{$key} === null ? 0 : $row->{$key};
        }

        return $row;
    }

    /**
     * Create appointment + optional first report inside a transaction.
     */
    public function createWithReport(array $appointmentData, ?array $reportData = null): int
    {
        $db = $this->db;
        $db->transBegin();

        try {
            $this->insert($appointmentData, false);
            $appointmentId = (int) $db->insertID();

            if ($reportData !== null) {
                $reportData['appointment_id'] = $appointmentId;
                model(ReportModel::class)->insert($reportData, false);
            }

            $db->transCommit();

            return $appointmentId;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
