<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\InpatientAdmission;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Prescription;
use App\Models\Registration;
use App\Models\User;
use App\Services\AlertService;
use App\Services\InpatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AlertService $alerts,
        private readonly InpatientService $inpatient,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->denyUnless($user->hasPermission(Permission::VIEW_DASHBOARD));

        $today = today();

        $stats = [
            'registrationsToday' => Registration::query()->forToday()->count(),
            'registrationsActive' => Registration::query()->forToday()->open()->count(),
            'patientsToday' => Patient::query()->whereDate('created_at', $today)->count(),
            'patientsTotal' => Patient::query()->active()->count(),
            'doctorsActive' => Doctor::query()->active()->count(),
            'doctorsOnDuty' => Doctor::query()->active()->today()->count(),
            'poliesActive' => Poly::query()->active()->count(),
            'usersActive' => User::query()->where('is_active', true)->count(),
        ];

        $statusCounts = Registration::query()
            ->forToday()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $perPoly = Poly::active()
            ->withCount(['registrations as today_count' => fn ($q) => $q->whereDate('visit_date', $today)])
            ->orderByDesc('today_count')
            ->orderBy('name')
            ->get();

        $trendDays = match ($request->string('range')->toString() ?: 'week') {
            'month' => 30,
            'quarter' => 90,
            default => 7,
        };
        $range = $request->string('range')->toString() ?: 'week';
        $trend = $this->visitTrend($trendDays);

        $recent = Registration::query()->forToday()
            ->with(['patient:id,name,mr_number,gender', 'poly:id,name', 'doctor:id,name'])
            ->orderByDesc('queue_number')
            ->limit(8)
            ->get();

        $moduleStats = $this->moduleStats($user);

        $lowStockDrugs = $user->hasPermission(Permission::VIEW_MASTER)
            ? Drug::query()->active()->lowStock()->orderBy('stock')->limit(6)->get()
            : collect();

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'statuses' => RegistrationStatus::cases(),
            'perPoly' => $perPoly,
            'chart' => [
                'data' => $trend,
                'max' => max(1, (int) $trend->max('count')),
                'total' => (int) $trend->sum('count'),
                'range' => $range,
                'label' => match ($range) {
                    'month' => '30 Hari Terakhir',
                    'quarter' => '90 Hari Terakhir',
                    default => '7 Hari Terakhir',
                },
            ],
            'recent' => $recent,
            'lowStockDrugs' => $lowStockDrugs,
            'moduleStats' => $moduleStats,
            'alerts' => $this->alerts->operational($user),
            'activity' => $this->alerts->recentActivity(7, $user),
            'quickActions' => $this->quickActions($user),
        ]);
    }

    /** Deret kunjungan harian dalam satu query agregat (tanpa N+1). */
    protected function visitTrend(int $days = 7)
    {
        $start = today()->subDays($days - 1);

        $rows = Registration::query()
            ->whereBetween('visit_date', [$start->toDateString(), today()->toDateString()])
            ->selectRaw('visit_date, COUNT(*) as aggregate')
            ->groupBy('visit_date')
            ->pluck('aggregate', 'visit_date');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($rows, $start, $days) {
                $date = $start->copy()->addDays($offset);

                return [
                    'date' => $date->toDateString(),
                    'label' => $days <= 7
                        ? $date->translatedFormat('D')
                        : $date->translatedFormat('d/m'),
                    'title' => $date->translatedFormat('l, d F Y'),
                    'count' => (int) ($rows[$date->toDateString()] ?? 0),
                ];
            });
    }

    /** Statistik per modul, hanya untuk modul yang boleh diakses pengguna. */
    protected function moduleStats(User $user): array
    {
        $stats = [];

        if ($user->hasPermission(Permission::VIEW_EMR)) {
            $stats['emr'] = [
                'draft' => MedicalRecord::query()->draft()->count(),
                'finalized' => MedicalRecord::query()->finalized()->count(),
            ];
        }

        if ($user->hasPermission(Permission::VIEW_PHARMACY)) {
            $stats['pharmacy'] = [
                'pending' => Prescription::query()->pending()->count(),
                'low_stock' => Drug::query()->active()->lowStock()->count(),
                'expiring' => Drug::query()->active()->expiring(90)->count(),
            ];
        }

        if ($user->hasPermission(Permission::VIEW_BILLING)) {
            $outstanding = Invoice::query()
                ->open()
                ->selectRaw('COALESCE(SUM(GREATEST(total - paid_total, 0)), 0) as amount')
                ->value('amount');

            $stats['billing'] = [
                'open' => Invoice::query()->open()->count(),
                'outstanding' => (int) $outstanding,
                'paid_today' => (int) Invoice::query()->paid()->whereDate('paid_at', today())->sum('total'),
                'paid_month' => (int) Invoice::query()->paid()->forMonth(Carbon::now())->sum('total'),
            ];
        }

        if ($user->hasPermission(Permission::VIEW_INPATIENT)) {
            $stats['inpatient'] = $this->inpatient->summary() + [
                'active' => InpatientAdmission::query()->admitted()->count(),
            ];
        }

        if ($user->hasPermission(Permission::VIEW_SCHEDULE)) {
            $stats['schedule'] = [
                'on_duty' => Doctor::query()->active()->today()->count(),
            ];
        }

        return $stats;
    }

    /** @return list<array{label:string, route:string, icon:string}> */
    protected function quickActions(User $user): array
    {
        $actions = [];

        if ($user->hasPermission(Permission::MANAGE_PATIENT)) {
            $actions[] = ['label' => 'Pasien Baru', 'route' => 'patients.create', 'icon' => 'user-plus'];
        }

        if ($user->hasPermission(Permission::MANAGE_REGISTRATION)) {
            $actions[] = ['label' => 'Pendaftaran', 'route' => 'registrations.create', 'icon' => 'clipboard'];
        }

        if ($user->hasPermission(Permission::MANAGE_QUEUE)) {
            $actions[] = ['label' => 'Papan Antrean', 'route' => 'queue.index', 'icon' => 'list'];
        }

        if ($user->hasPermission(Permission::MANAGE_EMR)) {
            $actions[] = ['label' => 'Rekam Medis', 'route' => 'medical-records.create', 'icon' => 'stethoscope'];
        }

        if ($user->hasPermission(Permission::MANAGE_BILLING)) {
            $actions[] = ['label' => 'Buat Tagihan', 'route' => 'billing.create', 'icon' => 'receipt'];
        }

        if ($user->hasPermission(Permission::MANAGE_INPATIENT)) {
            $actions[] = ['label' => 'Rawat Inap', 'route' => 'inpatient.index', 'icon' => 'bed'];
        }

        return array_slice($actions, 0, 6);
    }
}
