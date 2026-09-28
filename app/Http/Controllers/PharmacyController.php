<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\PrescriptionStatus;
use App\Models\Drug;
use App\Models\Prescription;
use App\Services\PharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function __construct(private readonly PharmacyService $pharmacy) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_PHARMACY));

        $prescriptions = Prescription::query()
            ->with(['patient', 'doctor', 'items.drug'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('patient', fn ($w) => $w->search($request->string('q')->toString())))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('pharmacy.prescriptions.index', [
            'prescriptions' => $prescriptions,
            'statuses' => PrescriptionStatus::cases(),
            'pendingCount' => Prescription::query()->pending()->count(),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_PHARMACY),
        ]);
    }

    public function show(Prescription $prescription): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_PHARMACY));

        $prescription->load(['patient', 'doctor', 'dispenser', 'items.drug', 'medicalRecord']);

        return view('pharmacy.prescriptions.show', [
            'prescription' => $prescription,
            'canManage' => auth()->user()->hasPermission(Permission::MANAGE_PHARMACY),
        ]);
    }

    public function dispense(Prescription $prescription): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_PHARMACY));

        try {
            $this->pharmacy->dispense($prescription);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Resep {$prescription->prescription_number} diserahkan dan stok obat telah diperbarui.");
    }

    public function cancel(Prescription $prescription): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_PHARMACY));

        try {
            $this->pharmacy->cancel($prescription);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Resep {$prescription->prescription_number} dibatalkan.");
    }

    public function adjustStock(Request $request, Drug $drug): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_PHARMACY));

        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], [
            'stock' => 'jumlah stok',
            'note' => 'keterangan',
        ]);

        try {
            $this->pharmacy->adjustStock($drug, (int) $data['stock'], $data['note'] ?? 'Penyesuaian stok');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Stok {$drug->name} disesuaikan menjadi {$data['stock']} {$drug->unit}.");
    }
}
