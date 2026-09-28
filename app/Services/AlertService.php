<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Enums\RoomStatus;
use App\Models\Drug;
use App\Models\InpatientAdmission;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Registration;
use App\Models\Room;
use App\Models\User;

/**
 * Menyusun notifikasi operasional langsung dari state database.
 * Tidak ada data simulasi: setiap butir berasal dari agregat nyata.
 *
 * Setiap notifikasi hanya ditampilkan bila pengguna berhak membuka modul yang
 * terkait, sehingga tidak pernah muncul tautan yang berakhir di halaman 403.
 */
class AlertService
{
    /**
     * @return list<array{key:string, tone:string, title:string, detail:string, route:string|null, count:int}>
     */
    public function operational(?User $user = null): array
    {
        $user ??= auth()->user();

        $allowed = fn (Permission $permission) => $user === null || $user->hasPermission($permission);

        $alerts = [];

        $waiting = $allowed(Permission::MANAGE_QUEUE)
            ? Registration::query()
                ->forToday()
                ->whereIn('status', [RegistrationStatus::REGISTERED, RegistrationStatus::QUEUING])
                ->count()
            : 0;

        if ($waiting > 0) {
            $alerts[] = [
                'key' => 'queue',
                'tone' => 'amber',
                'title' => 'Antrean menunggu panggilan',
                'detail' => "{$waiting} pasien sudah terdaftar hari ini dan belum dipanggil.",
                'route' => 'queue.index',
                'count' => $waiting,
            ];
        }

        $draftRecords = $allowed(Permission::VIEW_EMR)
            ? MedicalRecord::query()->draft()->count()
            : 0;

        if ($draftRecords > 0) {
            $alerts[] = [
                'key' => 'emr',
                'tone' => 'sky',
                'title' => 'Rekam medis belum difinalisasi',
                'detail' => "{$draftRecords} rekam medis masih berstatus draft.",
                'route' => 'medical-records.index',
                'count' => $draftRecords,
            ];
        }

        $pendingPrescriptions = $allowed(Permission::VIEW_PHARMACY)
            ? Prescription::query()->pending()->count()
            : 0;

        if ($pendingPrescriptions > 0) {
            $alerts[] = [
                'key' => 'pharmacy',
                'tone' => 'violet',
                'title' => 'Resep menunggu apoteker',
                'detail' => "{$pendingPrescriptions} resep belum diserahkan ke pasien.",
                'route' => 'pharmacy.prescriptions.index',
                'count' => $pendingPrescriptions,
            ];
        }

        $outstanding = $allowed(Permission::VIEW_BILLING)
            ? Invoice::query()
                ->open()
                ->selectRaw('COALESCE(SUM(GREATEST(total - paid_total, 0)), 0) as amount, COUNT(*) as total')
                ->first()
            : null;

        if ($outstanding && (int) $outstanding->total > 0) {
            $alerts[] = [
                'key' => 'billing',
                'tone' => 'rose',
                'title' => 'Tagihan belum lunas',
                'detail' => (int) $outstanding->total.' tagihan tersisa sebesar Rp '
                    .number_format((int) $outstanding->amount, 0, ',', '.').'.',
                'route' => 'billing.index',
                'count' => (int) $outstanding->total,
            ];
        }

        $lowStock = $allowed(Permission::VIEW_PHARMACY) || $allowed(Permission::VIEW_MASTER)
            ? Drug::query()->active()->lowStock()->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', today()->toDateString());
            })->count()
            : 0;

        if ($lowStock > 0) {
            $alerts[] = [
                'key' => 'low-stock',
                'tone' => 'amber',
                'title' => 'Stok obat menipis',
                'detail' => "{$lowStock} jenis obat berada di bawah stok minimum.",
                'route' => 'master.drugs.index',
                'count' => $lowStock,
            ];
        }

        $expiring = $allowed(Permission::VIEW_PHARMACY) || $allowed(Permission::VIEW_MASTER)
            ? Drug::query()->active()->expiring(30)
                ->whereDate('expired_at', '>=', today()->toDateString())
                ->count()
            : 0;

        if ($expiring > 0) {
            $alerts[] = [
                'key' => 'expiring',
                'tone' => 'rose',
                'title' => 'Obat mendekati kedaluwarsa',
                'detail' => "{$expiring} jenis obat kedaluwarsa dalam 30 hari ke depan.",
                'route' => 'master.drugs.index',
                'count' => $expiring,
            ];
        }

        $maintenance = $allowed(Permission::VIEW_INPATIENT)
            ? Room::query()->where('status', RoomStatus::MAINTENANCE)->count()
            : 0;

        if ($maintenance > 0) {
            $alerts[] = [
                'key' => 'rooms',
                'tone' => 'slate',
                'title' => 'Kamar dalam pemeliharaan',
                'detail' => "{$maintenance} kamar sedang tidak beroperasi.",
                'route' => 'inpatient.index',
                'count' => $maintenance,
            ];
        }

        return $alerts;
    }

    /** Aktivitas terbaru lintas modul (berbasis data nyata). */
    public function recentActivity(int $limit = 8, ?User $user = null): array
    {
        $user ??= auth()->user();

        $allowed = fn (Permission $permission) => $user === null || $user->hasPermission($permission);

        $activity = [];

        $recentRecords = $allowed(Permission::VIEW_EMR)
            ? MedicalRecord::query()
                ->with(['patient:id,name,mr_number', 'doctor:id,name'])
                ->latest('created_at')
                ->limit($limit)
                ->get()
            : collect();

        foreach ($recentRecords as $record) {
            $activity[] = [
                'at' => $record->created_at,
                'tone' => 'sky',
                'title' => 'Rekam medis dibuat',
                'detail' => $record->patient?->name.' - '.($record->doctor?->name ?? 'Tanpa dokter'),
                'route' => route('medical-records.show', $record),
            ];
        }

        $recentPayments = $allowed(Permission::VIEW_BILLING)
            ? Payment::query()
                ->with(['invoice.patient:id,name', 'cashier:id,name'])
                ->latest('paid_at')
                ->limit($limit)
                ->get()
            : collect();

        foreach ($recentPayments as $payment) {
            $activity[] = [
                'at' => $payment->paid_at,
                'tone' => 'emerald',
                'title' => 'Pembayaran diterima',
                'detail' => 'Rp '.number_format($payment->amount, 0, ',', '.')
                    .' - '.($payment->invoice?->patient?->name ?? 'Pasien'),
                'route' => route('billing.show', $payment->invoice_id),
            ];
        }

        $recentAdmissions = $allowed(Permission::VIEW_INPATIENT)
            ? InpatientAdmission::query()
                ->with(['patient:id,name', 'room:id,code'])
                ->where('status', AdmissionStatus::ADMITTED)
                ->latest('admitted_at')
                ->limit($limit)
                ->get()
            : collect();

        foreach ($recentAdmissions as $admission) {
            $activity[] = [
                'at' => $admission->admitted_at,
                'tone' => 'violet',
                'title' => 'Pasien dirawat inap',
                'detail' => $admission->patient?->name.' - kamar '.$admission->room?->code,
                'route' => route('inpatient.index'),
            ];
        }

        return collect($activity)
            ->filter(fn ($item) => $item['at'] !== null)
            ->sortByDesc('at')
            ->take($limit)
            ->values()
            ->all();
    }
}
