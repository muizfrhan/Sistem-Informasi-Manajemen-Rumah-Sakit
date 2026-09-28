<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Models\Drug;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Prescription;
use App\Models\Registration;
use App\Services\InpatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly InpatientService $inpatient) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_REPORTS));

        [$from, $to] = $this->resolveRange($request);

        $registrations = Registration::query()
            ->whereBetween('visit_date', [$from, $to])
            ->whereNull('deleted_at');

        $invoices = Invoice::query()
            ->whereBetween('issued_at', [$from, $to])
            ->whereNull('deleted_at');

        return view('reports.index', [
            'range' => ['from' => $from, 'to' => $to, 'days' => $from->diffInDays($to) + 1],
            'visits' => [
                'total' => (clone $registrations)->count(),
                'outpatient' => (clone $registrations)->where('visit_type', 'OUTPATIENT')->count(),
                'inpatient' => (clone $registrations)->where('visit_type', 'INPATIENT')->count(),
                'emergency' => (clone $registrations)->where('is_emergency', true)->count(),
                'completed' => (clone $registrations)->where('status', 'DONE')->count(),
                'cancelled' => (clone $registrations)->whereIn('status', ['CANCELLED', 'NO_SHOW'])->count(),
            ],
            'revenue' => [
                'invoiced' => (int) (clone $invoices)->sum('total'),
                'collected' => (int) (clone $invoices)->sum('paid_total'),
                'outstanding' => (int) (clone $invoices)->get()->sum(fn (Invoice $i) => $i->outstanding),
                'invoice_count' => (clone $invoices)->count(),
            ],
            'medical' => [
                'records' => MedicalRecord::query()->whereBetween('created_at', [$from, $to])->count(),
                'finalized' => MedicalRecord::query()->finalized()->whereBetween('finalized_at', [$from, $to])->count(),
                'draft' => MedicalRecord::query()->draft()->count(),
                'prescriptions' => Prescription::query()->whereBetween('created_at', [$from, $to])->count(),
                'dispensed' => Prescription::query()->dispensed()->whereBetween('dispensed_at', [$from, $to])->count(),
            ],
            'patients' => [
                'new' => Patient::query()->whereBetween('created_at', [$from, $to])->count(),
                'active' => Patient::query()->active()->count(),
            ],
            'byPoly' => $this->visitsByPoly($from, $to),
            'byStatus' => $this->visitsByStatus($from, $to),
            'trend' => $this->dailyTrend($from, $to),
            'topDiagnoses' => $this->topDiagnoses($from, $to),
            'topDrugs' => $this->topDrugs($from, $to),
            'inpatient' => $this->inpatient->summary(),
            'inventory' => [
                'items' => Drug::query()->where('is_active', true)->count(),
                'value' => (int) Drug::query()->where('is_active', true)->sum(DB::raw('stock * price')),
                'low' => Drug::query()->active()->lowStock()->count(),
                'expiring' => Drug::query()->active()->expiring()->count(),
            ],
            'polies' => Poly::active()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function visitsByPoly(Carbon $from, Carbon $to)
    {
        return Poly::query()
            ->withCount(['registrations as total' => fn ($q) => $q->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])])
            ->orderByDesc('total')
            ->orderBy('name')
            ->get(['id', 'name', 'total'])
            ->map(fn (Poly $poly) => [
                'id' => $poly->id,
                'name' => $poly->name,
                'total' => $poly->total,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function visitsByStatus(Carbon $from, Carbon $to)
    {
        $rows = Registration::query()
            ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(RegistrationStatus::cases())
            ->map(fn ($status) => [
                'status' => $status,
                'label' => $status->label(),
                'count' => (int) ($rows[$status->value] ?? 0),
            ])
            ->filter(fn ($row) => $row['count'] > 0)
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function dailyTrend(Carbon $from, Carbon $to)
    {
        $days = min(62, $from->diffInDays($to) + 1);
        $start = $to->copy()->subDays($days - 1);

        $rows = Registration::query()
            ->whereBetween('visit_date', [$start->toDateString(), $to->toDateString()])
            ->selectRaw('visit_date, COUNT(*) as aggregate')
            ->groupBy('visit_date')
            ->pluck('aggregate', 'visit_date');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($rows, $start, $days) {
                $date = $start->copy()->addDays($offset);

                return [
                    'date' => $date->toDateString(),
                    'label' => $days <= 14 ? $date->translatedFormat('D, d M') : $date->translatedFormat('d/m'),
                    'count' => (int) ($rows[$date->toDateString()] ?? 0),
                ];
            });
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function topDiagnoses(Carbon $from, Carbon $to): Collection
    {
        return MedicalRecord::query()
            ->join('medical_record_diagnoses', 'medical_record_diagnoses.medical_record_id', '=', 'medical_records.id')
            ->join('icd10_codes', 'icd10_codes.id', '=', 'medical_record_diagnoses.icd10_code_id')
            // (created_at ada di ketiga tabel yang di-join, wajib qualify)
            ->whereBetween('medical_records.created_at', [$from, $to])
            ->groupBy('icd10_codes.id', 'icd10_codes.code', 'icd10_codes.description')
            ->selectRaw('icd10_codes.code, icd10_codes.description, COUNT(*) as aggregate')
            ->orderByDesc('aggregate')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'description' => $row->description,
                'count' => (int) $row->aggregate,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function topDrugs(Carbon $from, Carbon $to): Collection
    {
        return Drug::query()
            ->join('prescription_items', 'prescription_items.drug_id', '=', 'drugs.id')
            ->join('prescriptions', 'prescriptions.id', '=', 'prescription_items.prescription_id')
            ->whereBetween('prescriptions.created_at', [$from, $to])
            ->groupBy('drugs.id', 'drugs.name', 'drugs.unit')
            ->selectRaw('drugs.name, drugs.unit, SUM(prescription_items.quantity) as aggregate')
            ->orderByDesc('aggregate')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'unit' => $row->unit,
                'quantity' => (int) $row->aggregate,
            ]);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    protected function resolveRange(Request $request): array
    {
        $preset = $request->string('range')->toString() ?: 'month';

        $to = Carbon::today();

        $from = match ($preset) {
            'week' => $to->copy()->startOfWeek(),
            'quarter' => $to->copy()->subMonths(2)->startOfMonth(),
            'year' => $to->copy()->startOfYear(),
            'custom' => Carbon::parse($request->date('from') ?: $to->toDateString())->startOfDay(),
            default => $to->copy()->startOfMonth(),
        };

        if ($preset === 'custom' && $request->filled('to')) {
            $to = Carbon::parse($request->date('to'))->endOfDay();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from];
        }

        return [$from, $to];
    }
}
