<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Models\Poly;
use App\Models\Registration;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function __construct(private readonly QueueService $queue) {}

    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::MANAGE_QUEUE));

        $polies = Poly::active()->orderBy('name')->get();
        $polyId = $request->integer('poly_id') ?: $polies->first()?->id;

        $registrations = Registration::query()
            ->forToday()
            ->when($polyId, fn ($q) => $q->where('poly_id', $polyId))
            ->whereNotIn('status', [RegistrationStatus::CANCELLED, RegistrationStatus::NO_SHOW])
            ->with(['patient', 'poly', 'doctor'])
            ->orderByRaw('is_emergency DESC')
            ->orderBy('queue_number')
            ->get();

        $counts = $registrations->groupBy(fn (Registration $r) => $r->status->value)
            ->map(fn ($group) => $group->count());

        $nowServing = $registrations->first(fn (Registration $r) => $r->status === RegistrationStatus::IN_EXAMINATION);
        $nextUp = $registrations->first(fn (Registration $r) => $r->status === RegistrationStatus::QUEUING);

        return view('queue.index', [
            'polies' => $polies,
            'selectedPoly' => $polyId ? $polies->firstWhere('id', $polyId) : null,
            'registrations' => $registrations,
            'counts' => $counts,
            'statuses' => RegistrationStatus::cases(),
            'waiting' => $counts->get(RegistrationStatus::QUEUING->value, 0)
                + $counts->get(RegistrationStatus::REGISTERED->value, 0),
            'called' => $counts->get(RegistrationStatus::CALLED->value, 0),
            'inExamination' => $counts->get(RegistrationStatus::IN_EXAMINATION->value, 0),
            'done' => $counts->get(RegistrationStatus::DONE->value, 0),
            'nowServing' => $nowServing,
            'nextUp' => $nextUp,
        ]);
    }

    public function call(Poly $poly): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_QUEUE));

        try {
            $registration = $this->queue->callNext($poly);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $registration) {
            return back()->with('error', 'Tidak ada pasien yang menunggu panggilan.');
        }

        return back()->with('success', "Memanggil {$registration->queue_code} - {$registration->patient->name}.");
    }

    public function advance(Registration $registration, RegistrationStatus $status): RedirectResponse
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::MANAGE_QUEUE));

        try {
            $this->queue->advance($registration, $status);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Status diubah ke {$status->label()}.");
    }
}
