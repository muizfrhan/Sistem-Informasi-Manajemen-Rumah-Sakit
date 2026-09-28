<?php

namespace App\Support;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Satu sumber kebenaran untuk navigasi aplikasi.
 *
 * Setiap item hanya ditampilkan bila route-nya benar-benar ada DAN pengguna
 * memiliki izin yang sesuai di sisi backend. Jangan pernah menambahkan menu
 * tanpa backend yang mendukungnya.
 */
class Navigation
{
    /** @return array<int, array{label: string, items: array<int, array<string, mixed>>}> */
    public static function for(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $link = static function (
            string $label,
            string $route,
            string $icon,
            Permission $permission,
            string $pattern,
        ) use ($user): ?array {
            if (! $user->hasPermission($permission) || ! Route::has($route)) {
                return null;
            }

            return [
                'label' => $label,
                'route' => $route,
                'icon' => $icon,
                'pattern' => $pattern,
            ];
        };

        $groups = [
            'Utama' => [
                $link('Dashboard', 'dashboard', 'dashboard', Permission::VIEW_DASHBOARD, 'dashboard'),
            ],

            'Operasional' => [
                $link('Pasien', 'patients.index', 'users', Permission::VIEW_PATIENT, 'patients.*'),
                $link('Pendaftaran', 'registrations.index', 'clipboard', Permission::VIEW_REGISTRATION, 'registrations.*'),
                $link('Antrean', 'queue.index', 'ticket', Permission::MANAGE_QUEUE, 'queue.*'),
                $link('Jadwal Dokter', 'schedules.index', 'calendar', Permission::VIEW_SCHEDULE, 'schedules.*'),
                $link('Rawat Inap', 'inpatient.index', 'bed', Permission::VIEW_INPATIENT, 'inpatient.*'),
            ],

            'Medis' => [
                $link('Rekam Medis', 'medical-records.index', 'stethoscope', Permission::VIEW_EMR, 'medical-records.*'),
            ],

            'Farmasi' => [
                $link('Resep & Stok', 'pharmacy.prescriptions.index', 'pill', Permission::VIEW_PHARMACY, 'pharmacy.*'),
                $link('Data Obat', 'master.drugs.index', 'cube', Permission::VIEW_MASTER, 'master.drugs.*'),
            ],

            'Keuangan' => [
                $link('Tagihan', 'billing.index', 'receipt', Permission::VIEW_BILLING, 'billing.*'),
            ],

            'Manajemen' => [
                $link('Poli', 'master.polies.index', 'building', Permission::VIEW_MASTER, 'master.polies.*'),
                $link('Dokter', 'master.doctors.index', 'user', Permission::VIEW_MASTER, 'master.doctors.*'),
                $link('Kode ICD-10', 'master.icd10.index', 'document', Permission::VIEW_MASTER, 'master.icd10.*'),
                $link('Kamar', 'rooms.index', 'scale', Permission::VIEW_INPATIENT, 'rooms.*'),
                $link('Laporan', 'reports.index', 'chart', Permission::VIEW_REPORTS, 'reports.*'),
                $link('Permintaan Demo', 'admin.demo-requests.index', 'inbox', Permission::VIEW_REPORTS, 'admin.demo-requests.*'),
                $link('Pengguna', 'admin.users.index', 'users', Permission::MANAGE_USER, 'admin.users.*'),
                $link('Audit Trail', 'admin.audit-logs.index', 'shield', Permission::VIEW_AUDIT_LOG, 'admin.audit-logs.*'),
            ],
        ];

        $result = [];

        foreach ($groups as $label => $items) {
            $visible = array_values(array_filter($items));

            if ($visible !== []) {
                $result[] = ['label' => $label, 'items' => $visible];
            }
        }

        return $result;
    }
}
