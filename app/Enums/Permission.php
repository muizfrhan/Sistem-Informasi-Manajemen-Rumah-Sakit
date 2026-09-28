<?php

namespace App\Enums;

enum Permission: string
{
    case VIEW_DASHBOARD = 'dashboard.view';
    case VIEW_PATIENT = 'patient.view';
    case MANAGE_PATIENT = 'patient.manage';
    case VIEW_REGISTRATION = 'registration.view';
    case MANAGE_REGISTRATION = 'registration.manage';
    case MANAGE_QUEUE = 'queue.manage';
    case VIEW_EMR = 'emr.view';
    case MANAGE_EMR = 'emr.manage';
    case MANAGE_NURSING_ASSESSMENT = 'emr.nursing_assessment';
    case VIEW_PHARMACY = 'pharmacy.view';
    case MANAGE_PHARMACY = 'pharmacy.manage';
    case VIEW_BILLING = 'billing.view';
    case MANAGE_BILLING = 'billing.manage';
    case VIEW_INPATIENT = 'inpatient.view';
    case MANAGE_INPATIENT = 'inpatient.manage';
    case VIEW_SCHEDULE = 'schedule.view';
    case MANAGE_SCHEDULE = 'schedule.manage';
    case VIEW_MASTER = 'master.view';
    case MANAGE_MASTER = 'master.manage';
    case VIEW_REPORTS = 'reports.view';
    case VIEW_AUDIT_LOG = 'audit.view';
    case MANAGE_USER = 'user.manage';

    public function label(): string
    {
        return match ($this) {
            self::VIEW_DASHBOARD => 'Lihat Dashboard',
            self::VIEW_PATIENT => 'Lihat Data Pasien',
            self::MANAGE_PATIENT => 'Kelola Data Pasien',
            self::VIEW_REGISTRATION => 'Lihat Pendaftaran',
            self::MANAGE_REGISTRATION => 'Kelola Pendaftaran',
            self::MANAGE_QUEUE => 'Kelola Antrean',
            self::VIEW_EMR => 'Lihat Rekam Medis',
            self::MANAGE_EMR => 'Kelola Rekam Medis',
            self::MANAGE_NURSING_ASSESSMENT => 'Asesmen Keperawatan',
            self::VIEW_PHARMACY => 'Lihat Farmasi',
            self::MANAGE_PHARMACY => 'Kelola Farmasi',
            self::VIEW_BILLING => 'Lihat Penagihan',
            self::MANAGE_BILLING => 'Kelola Penagihan',
            self::VIEW_INPATIENT => 'Lihat Rawat Inap',
            self::MANAGE_INPATIENT => 'Kelola Rawat Inap',
            self::VIEW_SCHEDULE => 'Lihat Jadwal Dokter',
            self::MANAGE_SCHEDULE => 'Kelola Jadwal Dokter',
            self::VIEW_MASTER => 'Lihat Master Data',
            self::MANAGE_MASTER => 'Kelola Master Data',
            self::VIEW_REPORTS => 'Lihat Laporan',
            self::VIEW_AUDIT_LOG => 'Lihat Audit Trail',
            self::MANAGE_USER => 'Kelola Pengguna',
        };
    }

    public function group(): string
    {
        return str($this->value)->before('.')->toString();
    }

    public function groupLabel(): string
    {
        return match ($this->group()) {
            'dashboard' => 'Dashboard',
            'patient' => 'Manajemen Pasien',
            'registration' => 'Pendaftaran',
            'queue' => 'Antrean',
            'emr' => 'Rekam Medis',
            'pharmacy' => 'Farmasi',
            'billing' => 'Penagihan',
            'inpatient' => 'Rawat Inap',
            'schedule' => 'Jadwal Dokter',
            'master' => 'Master Data',
            'reports' => 'Laporan',
            'audit' => 'Audit',
            'user' => 'Pengguna',
            default => $this->group(),
        };
    }
}
