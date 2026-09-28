<?php

namespace App\Enums;

enum RoleCode: string
{
    case SUPER_ADMIN = 'super_admin';
    case RECEPTIONIST = 'receptionist';
    case DOCTOR = 'doctor';
    case NURSE = 'nurse';
    case PHARMACIST = 'pharmacist';
    case CASHIER = 'cashier';
    case MANAGER = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin / IT',
            self::RECEPTIONIST => 'Recepsionis / Pendaftaran',
            self::DOCTOR => 'Dokter',
            self::NURSE => 'Perawat',
            self::PHARMACIST => 'Apoteker',
            self::CASHIER => 'Kasir / Keuangan',
            self::MANAGER => 'Manajemen / Direksi',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Konfigurasi sistem, manajemen user, pemeliharaan database.',
            self::RECEPTIONIST => 'Pendaftaran pasien, pencarian data pasien, penjadwalan antrean.',
            self::DOCTOR => 'Input rekam medis, diagnosis ICD-10, resep, permintaan penunjang.',
            self::NURSE => 'Asesmen awal tanda-tanda vital, catatan keperawatan.',
            self::PHARMACIST => 'Validasi resep, pengeluaran obat, manajemen stok farmasi.',
            self::CASHIER => 'Pembayaran layanan, penutupan kasir (closing shift).',
            self::MANAGER => 'Akses laporan analitik dan eksekutif rumah sakit.',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => Permission::cases(),
            self::RECEPTIONIST => [
                Permission::VIEW_DASHBOARD,
                Permission::VIEW_PATIENT,
                Permission::MANAGE_PATIENT,
                Permission::VIEW_REGISTRATION,
                Permission::MANAGE_REGISTRATION,
                Permission::MANAGE_QUEUE,
                Permission::VIEW_SCHEDULE,
                Permission::VIEW_BILLING,
            ],
            self::DOCTOR => [
                Permission::VIEW_DASHBOARD,
                Permission::VIEW_PATIENT,
                Permission::VIEW_REGISTRATION,
                Permission::MANAGE_QUEUE,
                Permission::VIEW_EMR,
                Permission::MANAGE_EMR,
                Permission::VIEW_PHARMACY,
                Permission::VIEW_INPATIENT,
                Permission::VIEW_SCHEDULE,
                Permission::VIEW_MASTER,
            ],
            self::NURSE => [
                Permission::VIEW_DASHBOARD,
                Permission::VIEW_PATIENT,
                Permission::VIEW_REGISTRATION,
                Permission::MANAGE_QUEUE,
                Permission::VIEW_EMR,
                Permission::MANAGE_NURSING_ASSESSMENT,
                Permission::VIEW_INPATIENT,
                Permission::MANAGE_INPATIENT,
                Permission::VIEW_PHARMACY,
                Permission::VIEW_SCHEDULE,
            ],
            self::PHARMACIST => [
                Permission::VIEW_DASHBOARD,
                Permission::VIEW_PATIENT,
                Permission::VIEW_EMR,
                Permission::VIEW_PHARMACY,
                Permission::MANAGE_PHARMACY,
                Permission::VIEW_MASTER,
            ],
            self::CASHIER => [
                Permission::VIEW_DASHBOARD,
                Permission::VIEW_PATIENT,
                Permission::VIEW_REGISTRATION,
                Permission::VIEW_BILLING,
                Permission::MANAGE_BILLING,
            ],
            self::MANAGER => [
                Permission::VIEW_DASHBOARD,
                Permission::VIEW_PATIENT,
                Permission::VIEW_REGISTRATION,
                Permission::VIEW_EMR,
                Permission::VIEW_PHARMACY,
                Permission::VIEW_BILLING,
                Permission::VIEW_INPATIENT,
                Permission::VIEW_SCHEDULE,
                Permission::VIEW_MASTER,
                Permission::VIEW_REPORTS,
                Permission::VIEW_AUDIT_LOG,
            ],
        };
    }
}
