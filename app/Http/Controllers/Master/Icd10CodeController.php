<?php

namespace App\Http\Controllers\Master;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Icd10Code;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class Icd10CodeController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_MASTER));

        $codes = Icd10Code::query()
            ->search($request->string('q')->toString())
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('code')
            ->paginate(30)
            ->withQueryString();

        return view('master.icd10.index', [
            'codes' => $codes,
            'categories' => Icd10Code::query()->distinct()->orderBy('category')->pluck('category')->filter(),
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_MASTER),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        Icd10Code::create($this->validateData($request));

        return back()->with('success', 'Kode ICD-10 berhasil ditambahkan.');
    }

    public function update(Request $request, Icd10Code $icd10code): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        $icd10code->update($this->validateData($request, $icd10code));

        return back()->with('success', 'Kode ICD-10 berhasil diperbarui.');
    }

    public function destroy(Icd10Code $icd10code): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_MASTER));

        $icd10code->delete();

        return back()->with('success', 'Kode ICD-10 dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Icd10Code $icd10code = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('icd10_codes', 'code')->ignore($icd10code?->id)],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'code' => 'kode',
            'description' => 'deskripsi',
        ]);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
