<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\MedicalRecordStatus;
use App\Enums\PaymentMethod;
use App\Enums\Permission;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(private readonly BillingService $billing) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_BILLING));

        $invoices = Invoice::query()
            ->with('patient')
            ->withSum('payments', 'amount')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->integer('patient_id')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('invoice_number', 'ILIKE', '%'.$request->string('q')->toString().'%')
                ->orWhereHas('patient', fn ($p) => $p->search($request->string('q')->toString()))))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('issued_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('issued_at', '<=', $request->date('to')))
            ->latest('issued_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $summary = $this->summary();

        return view('billing.index', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::cases(),
            'paymentMethods' => PaymentMethod::cases(),
            'summary' => $summary,
        ]);
    }

    /** Agregat penagihan untuk kartu statistik. */
    protected function summary(): array
    {
        $outstanding = Invoice::query()
            ->open()
            ->selectRaw('COALESCE(SUM(GREATEST(total - paid_total, 0)), 0) as amount')
            ->value('amount');

        return [
            'unpaid_count' => Invoice::query()->open()->count(),
            'unpaid_total' => (int) $outstanding,
            'paid_today' => (int) Invoice::query()->paid()->whereDate('paid_at', today())->sum('total'),
            'paid_month' => (int) Invoice::query()->paid()->forMonth(now())->sum('total'),
            'count_today' => Invoice::query()->whereDate('issued_at', today())->count(),
        ];
    }

    public function create(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_BILLING));

        $records = MedicalRecord::query()
            ->finalized()
            ->with(['patient', 'doctor', 'procedures', 'prescriptions.items'])
            ->whereDoesntHave('registration.invoices')
            ->latest('finalized_at')
            ->limit(50)
            ->get();

        $selected = $request->filled('medical_record_id')
            ? $records->firstWhere('id', $request->integer('medical_record_id'))
            : null;

        return view('billing.create', [
            'records' => $records,
            'record' => $selected?->load(['patient', 'doctor', 'procedures', 'prescriptions.items.drug']),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_BILLING),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_BILLING));

        $data = $request->validate([
            'medical_record_id' => ['required', 'exists:medical_records,id'],
            'consultation_fee' => ['nullable', 'integer', 'min:0', 'max:100000000'],
        ], [], [
            'medical_record_id' => 'rekam medis',
            'consultation_fee' => 'biaya konsultasi',
        ]);

        $record = MedicalRecord::findOrFail($data['medical_record_id']);

        if ($record->status !== MedicalRecordStatus::FINALIZED) {
            return back()->withInput()->with('error', 'Hanya rekam medis yang sudah difinalisasi yang dapat ditagihkan.');
        }

        if ($record->registration?->invoices()->exists()) {
            return back()->withInput()->with('error', 'Kunjungan ini sudah memiliki tagihan.');
        }

        try {
            $invoice = $this->billing->generateFromMedicalRecord(
                $record,
                (int) ($data['consultation_fee'] ?? 0),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('billing.show', $invoice)
            ->with('success', "Tagihan {$invoice->invoice_number} berhasil dibuat.");
    }

    public function show(Invoice $invoice): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_BILLING));

        $invoice->load(['patient', 'registration', 'items', 'payments.cashier', 'author']);

        return view('billing.show', [
            'invoice' => $invoice,
            'paymentMethods' => $invoice->preferredPaymentMethods(),
            'canManage' => auth()->user()->hasPermission(Permission::MANAGE_BILLING),
        ]);
    }

    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_BILLING));

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'method.in' => 'Metode pembayaran tidak dikenal.',
        ], [
            'amount' => 'nominal',
            'method' => 'metode pembayaran',
            'reference' => 'nomor referensi',
        ]);

        try {
            $this->billing->recordPayment(
                $invoice,
                (int) $data['amount'],
                PaymentMethod::from($data['method']),
                $data['reference'] ?? null,
                $data['notes'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pembayaran Rp '.number_format((int) $data['amount'], 0, ',', '.')." untuk tagihan {$invoice->invoice_number} berhasil dicatat.");
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_BILLING));

        try {
            $this->billing->cancel($invoice);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('billing.index')->with('success', "Tagihan {$invoice->invoice_number} dibatalkan.");
    }
}
