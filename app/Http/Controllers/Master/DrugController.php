<?php

namespace App\Http\Controllers\Master;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Drug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DrugController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_MASTER));

        $drugs = Drug::query()
            ->search($request->string('q')->toString())
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('master.drugs.index', [
            'drugs' => $drugs,
            'lowStockCount' => Drug::whereColumn('stock', '<=', 'min_stock')->count(),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_MASTER),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        Drug::create($this->validateData($request));

        return back()->with('success', 'Obat berhasil ditambahkan.');
    }

    public function update(Request $request, Drug $drug): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        $drug->update($this->validateData($request, $drug));

        return back()->with('success', "Data obat {$drug->name} berhasil diperbarui.");
    }

    public function destroy(Drug $drug): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_MASTER));

        $drug->delete();

        return back()->with('success', 'Data obat dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Drug $drug = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('drugs', 'code')->ignore($drug?->id)],
            'name' => ['required', 'string', 'max:150'],
            'generic_name' => ['nullable', 'string', 'max:150'],
            'form' => ['nullable', 'string', 'max:50'],
            'strength' => ['nullable', 'string', 'max:30'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'price.integer' => 'Harga harus berupa angka.',
        ], [
            'code' => 'kode obat',
            'name' => 'nama obat',
            'generic_name' => 'nama generik',
            'min_stock' => 'stok minimum',
        ]);

        return $data + [
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
