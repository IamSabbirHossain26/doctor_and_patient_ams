<?php

/**
 * Doctor & Patient Appointment Management System - custom helpers.
 */

if (! function_exists('dpams_roles')) {
    /**
     * All application roles.
     *
     * @return list<string>
     */
    function dpams_roles(): array
    {
        return ['manager', 'nurse', 'doctor', 'patient'];
    }
}

if (! function_exists('dpams_role_label')) {
    function dpams_role_label(string $role): string
    {
        return match ($role) {
            'manager' => 'Manager / Representative',
            'nurse'   => 'Staff Nurse',
            'doctor'  => 'Doctor',
            'patient' => 'Patient',
            default   => ucfirst($role),
        };
    }
}

if (! function_exists('dpams_badge')) {
    /**
     * Map a status string to a CSS badge class.
     */
    function dpams_badge(?string $status): string
    {
        $status = strtolower(trim((string) $status));
        $map = [
            'pending'    => 'badge-warning',
            'scheduled'  => 'badge-info',
            'confirmed'  => 'badge-info',
            'completed'  => 'badge-success',
            'paid'       => 'badge-success',
            'collected'  => 'badge-success',
            'cancelled'  => 'badge-danger',
            'unpaid'     => 'badge-danger',
            'waiting'    => 'badge-warning',
            'processing' => 'badge-info',
            'ready'      => 'badge-success',
            'active'     => 'badge-success',
            'inactive'   => 'badge-secondary',
        ];

        return $map[$status] ?? 'badge-secondary';
    }
}

if (! function_exists('dpams_e')) {
    /**
     * Shorthand HTML escape used in views.
     */
    function dpams_e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('dpams_fmt_date')) {
    function dpams_fmt_date(?string $date): string
    {
        if ($date === null || $date === '') {
            return '-';
        }
        $ts = strtotime($date);

        return $ts === false ? $date : date('d M Y', $ts);
    }
}

if (! function_exists('dpams_fmt_time')) {
    function dpams_fmt_time(?string $time): string
    {
        if ($time === null || $time === '') {
            return '-';
        }
        $ts = strtotime($time);

        return $ts === false ? $time : date('h:i A', $ts);
    }
}

if (! function_exists('dpams_money')) {
    function dpams_money($amount): string
    {
        return 'Tk ' . number_format((float) $amount, 2);
    }
}
