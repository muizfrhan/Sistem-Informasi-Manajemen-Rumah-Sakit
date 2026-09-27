<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DemoRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_REPORTS));

        $leads = DemoRequest::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'ILIKE', '%'.$request->string('q')->toString().'%')
                ->orWhere('institution', 'ILIKE', '%'.$request->string('q')->toString().'%')
                ->orWhere('email', 'ILIKE', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('facility_type'), fn ($q) => $q->where('facility_type', $request->string('facility_type')->toString()))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.demo-requests.index', [
            'leads' => $leads,
            'search' => $request->string('q')->toString(),
            'status' => $request->string('status')->toString(),
            'facilityType' => $request->string('facility_type')->toString(),
            'counts' => DemoRequest::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, DemoRequest $demorequest): RedirectResponse
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_REPORTS));

        $data = $request->validate([
            'status' => ['required', Rule::in(['NEW', 'CONTACTED', 'DEMO_SCHEDULED', 'WON', 'LOST'])],
            'internal_note' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'status' => 'status',
            'internal_note' => 'catatan internal',
        ]);

        $demorequest->update($data);

        return back()->with('success', "Status permintaan {$demorequest->institution} diperbarui menjadi {$demorequest->statusLabel()}.");
    }

    public function destroy(DemoRequest $demorequest): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_REPORTS));

        $demorequest->delete();

        return back()->with('success', 'Permintaan demo dihapus.');
    }
}
