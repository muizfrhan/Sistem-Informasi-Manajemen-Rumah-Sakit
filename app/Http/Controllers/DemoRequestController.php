<?php

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DemoRequestController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'institution' => ['required', 'string', 'max:200'],
            'position' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'facility_type' => ['required', Rule::in(['HOSPITAL', 'CLINIC', 'PRIVATE_PRACTICE', 'OTHER'])],
            'bed_count' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'message' => ['nullable', 'string', 'max:2000'],
            'company_website' => ['nullable', 'max:0'],
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + - ( ).',
            'company_website' => 'Permintaan ditolak.',
        ], [
            'name' => 'nama lengkap',
            'institution' => 'nama rumah sakit/klinik',
            'position' => 'jabatan',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'bed_count' => 'jumlah tempat tidur',
            'message' => 'kebutuhan',
        ]);

        unset($data['company_website']);

        $lead = DemoRequest::create($data + [
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        $this->audit->write('demo_requested', $lead);

        return redirect()
            ->route('home')
            ->with('success', "Terima kasih, {$lead->name}. Tim kami akan menghubungi Anda dalam 1x24 jam kerja untuk menjadwalkan demo.")
            ->with('open_demo_form', true);
    }
}
