<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_DASHBOARD));

        $today = today();

        $registrationsToday = Registration::query()->forToday()->count();
        $registrationsActive = Registration::query()->forToday()->open()->count();
        $patientsToday = Patient::query()->whereDate('created_at', $today)->count();
        $patientsTotal = Patient::query()->active()->count();

        $statusCounts = Registration::query()
            ->forToday()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $perPoly = Poly::active()
            ->withCount(['registrations as today_count' => fn ($q) => $q->whereDate('visit_date', $today)])
            ->orderBy('name')
            ->get();

        $last7Days = collect(range(6, 0))
            ->map(function (int $offset) {
                $date = today()->subDays($offset);

                return [
                    'date' => $date->translatedFormat('D, d M'),
                    'count' => Registration::query()->whereDate('visit_date', $date)->count(),
                ];
            });

        $maxDay = max(1, (int) $last7Days->max('count'));

        $recent = Registration::query()
            ->forToday()
            ->with(['patient', 'poly', 'doctor'])
            ->orderByDesc('queue_number')
            ->limit(8)
            ->get();

        $lowStockDrugs = Drug::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('is_active', true)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'stats' => [
                'registrationsToday' => $registrationsToday,
                'registrationsActive' => $registrationsActive,
                'patientsToday' => $patientsToday,
                'patientsTotal' => $patientsTotal,
                'usersActive' => User::query()->where('is_active', true)->count(),
            ],
            'statusCounts' => $statusCounts,
            'statuses' => RegistrationStatus::cases(),
            'perPoly' => $perPoly,
            'chart' => ['data' => $last7Days, 'max' => $maxDay],
            'recent' => $recent,
            'lowStockDrugs' => $lowStockDrugs,
        ]);
    }
}
