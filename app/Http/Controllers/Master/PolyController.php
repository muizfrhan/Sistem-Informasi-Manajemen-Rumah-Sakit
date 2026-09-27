<?php

namespace App\Http\Controllers\Master;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Poly;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PolyController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_MASTER));

        $polies = Poly::query()
            ->withCount('doctors')
            ->orderBy('name')
            ->paginate(25);

        return view('master.polies.index', [
            'polies' => $polies,
            'canManage' => $request->user()->hasPermission(Permission::MANAGE_MASTER),
        ]);
    }

    public function create(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        return view('master.polies.form', ['poly' => new Poly]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        $data = $this->validateData($request);

        $poly = Poly::create($data);

        return redirect()
            ->route('master.polies.index')
            ->with('success', "Poli {$poly->name} berhasil ditambahkan.");
    }

    public function edit(Request $request, Poly $poly): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        return view('master.polies.form', ['poly' => $poly]);
    }

    public function update(Request $request, Poly $poly): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_MASTER));

        $poly->update($this->validateData($request, $poly));

        return redirect()
            ->route('master.polies.index')
            ->with('success', "Poli {$poly->name} berhasil diperbarui.");
    }

    public function destroy(Poly $poly): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_MASTER));

        if ($poly->registrations()->exists()) {
            return back()->with('error', 'Poli tidak dapat dihapus karena sudah memiliki data pendaftaran. Nonaktifkan saja.');
        }

        $poly->delete();

        return back()->with('success', 'Poli dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Poly $poly = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('polies', 'code')->ignore($poly?->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'queue_prefix' => ['required', 'string', 'max:10', 'alpha'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.unique' => 'Kode poli sudah digunakan.',
            'queue_prefix.alpha' => 'Prefiks antrean harus berupa huruf.',
        ], [
            'code' => 'kode poli',
            'name' => 'nama poli',
            'queue_prefix' => 'prefiks antrean',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
