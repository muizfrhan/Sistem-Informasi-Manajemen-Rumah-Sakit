@extends('layouts.app')

@section('title', 'Rawat Inap')
@section('heading', 'Rawat Inap')
@section('subheading', $summary['occupied'] . ' dari ' . $summary['capacity'] . ' tempat tidur terisi')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Rawat Inap"
                       :description="'Tingkat hunian ' . $summary['occupancy_rate'] . '% pada ' . $summary['rooms_total'] . ' kamar'">
            <x-slot:actions>
                <x-button :href="route('rooms.index')" variant="secondary" icon="scale">Kelola Kamar</x-button>
                <x-button permission="inpatient.manage" icon="plus" x-on:click="$dispatch('open-admit')">
                    Terima Pasien
                </x-button>
            </x-slot:actions>
        </x-page-header>

        {{-- ===================== Statistik ===================== --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <x-stat-card label="Tempat Tidur" :value="$summary['capacity']" unit="bed" tone="slate" icon="bed" />
            <x-stat-card label="Terisi" :value="$summary['occupied']" unit="bed" tone="info" icon="users" />
            <x-stat-card label="Tersedia" :value="$summary['available']" unit="bed" tone="success" icon="check-circle" />
            <x-stat-card label="Pemeliharaan" :value="$summary['rooms_maintenance']" unit="kamar"
                         tone="warning" icon="cog" />
        </div>

        {{-- ===================== Papan kamar ===================== --}}
        <x-card title="Papan Kamar" subtitle="Status setiap kamar beserta pasien yang menghuni">
            <x-slot:actions>
                <form method="GET" action="{{ route('inpatient.index') }}">
                    <label for="class" class="sr-only">Filter kelas kamar</label>
                    <select id="class" name="class" onchange="this.form.submit()" class="field-control w-auto py-1.5 text-xs">
                        <option value="">Semua kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->value }}" @selected(request('class') === $class->value)>
                                {{ $class->label() }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </x-slot:actions>

            @if ($rooms->isEmpty())
                <x-empty-state icon="bed"
                               title="Belum ada kamar"
                               message="Tambahkan kamar tidur terlebih dahulu untuk mulai menerima pasien inap.">
                    <x-button permission="inpatient.manage" :href="route('rooms.index')" size="sm" icon="plus">Kelola Kamar</x-button>
                </x-empty-state>
            @else
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($rooms as $room)
                        @php
                            $maintenance = $room->status === \App\Enums\RoomStatus::MAINTENANCE;
                            $free = $room->availableBeds();
                        @endphp

                        <div @class([
                            'rounded-xl border p-4 transition',
                            'border-line bg-surface' => ! $maintenance,
                            'border-warning-ink/30 bg-warning-soft/40' => $maintenance,
                        ])>
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-mono text-sm font-bold text-ink">{{ $room->code }}</p>
                                    <p class="truncate text-xs text-ink-3">{{ $room->name }}</p>
                                </div>
                                <x-badge :variant="$room->room_class->badgeClass()">
                                    {{ $room->room_class->label() }}
                                </x-badge>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2">
                                @if ($maintenance)
                                    <x-badge variant="warning" dot>Pemeliharaan</x-badge>
                                @elseif ($free === 0)
                                    <x-badge variant="danger" dot>Penuh</x-badge>
                                @elseif ($room->active_admissions_count === 0)
                                    <x-badge variant="success" dot>Tersedia</x-badge>
                                @else
                                    <x-badge variant="info" dot>Sebagian Terisi</x-badge>
                                @endif

                                <span class="text-xs font-semibold text-ink-2 tabular-nums">
                                    {{ $room->active_admissions_count }} / {{ $room->capacity }} bed
                                </span>
                            </div>

                            @if ($room->activeAdmissions->isNotEmpty())
                                <ul class="mt-3 space-y-1.5 border-t border-line pt-3">
                                    @foreach ($room->activeAdmissions as $admission)
                                        <li class="flex items-center justify-between gap-2 text-xs">
                                            <a href="{{ route('patients.show', $admission->patient) }}"
                                               class="truncate font-medium text-ink hover:underline">
                                                {{ $admission->patient?->name }}
                                            </a>
                                            <span class="shrink-0 font-mono text-ink-3">Bed {{ $admission->bed_number }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- ===================== Daftar pasien inap ===================== --}}
        <x-card padding="p-0" title="Pasien Rawat Inap" :subtitle="$admissions->total() . ' catatan'">
            <x-filter-bar :action="route('inpatient.index')" :reset="route('inpatient.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Cari Pasien</label>
                        <input id="q" name="q" type="search" value="{{ request('q') }}"
                               placeholder="Nama, No. RM, atau NIK" class="field-control">
                    </div>

                    <div>
                        <label for="status" class="field-label">Status</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            @foreach (\App\Enums\AdmissionStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($statusFilter === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="room_id" class="field-label">Kamar</label>
                        <select id="room_id" name="room_id" class="field-control">
                            <option value="">Semua kamar</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(request('room_id') == $room->id)>
                                    {{ $room->code }} - {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$admissions->count()" min-width="58rem"
                          empty="Belum ada pasien inap"
                          empty-message="Belum ada catatan perawatan inap yang cocok dengan filter ini."
                          empty-icon="bed">
                <x-slot:head>
                    <tr>
                        <th scope="col">No. Rawat</th>
                        <th scope="col">Pasien</th>
                        <th scope="col" class="hidden sm:table-cell">Kamar</th>
                        <th scope="col" class="hidden lg:table-cell">Diagnosa</th>
                        <th scope="col" class="hidden md:table-cell">Masuk</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($admissions as $admission)
                        <tr>
                            <td data-label="No. Rawat">
                                <span class="font-mono text-xs font-bold text-ink">
                                    {{ $admission->admission_number }}
                                </span>
                            </td>

                            <td data-label="Pasien">
                                <a href="{{ route('patients.show', $admission->patient) }}"
                                   class="font-semibold text-ink hover:underline">
                                    {{ $admission->patient->name }}
                                </a>
                                <span class="block font-mono text-xs text-ink-3">
                                    {{ $admission->patient->mr_number }}
                                </span>
                            </td>

                            <td data-label="Kamar" class="hidden sm:table-cell">
                                <span class="font-mono font-semibold text-ink">{{ $admission->room->code }}</span>
                                <span class="block text-xs text-ink-3">Bed {{ $admission->bed_number }}</span>
                            </td>

                            <td data-label="Diagnosa" class="hidden lg:table-cell">
                                <span class="line-clamp-1 text-xs text-ink-2">
                                    {{ $admission->diagnosis ?: '-' }}
                                </span>
                            </td>

                            <td data-label="Masuk" class="hidden md:table-cell text-xs text-ink-2">
                                {{ $admission->admitted_at->translatedFormat('d M Y') }}
                                <span class="block text-ink-3">{{ $admission->length_of_stay }} hari</span>
                            </td>

                            <td data-label="Status">
                                <x-badge :class="$admission->status->badgeClass()">
                                    {{ $admission->status->label() }}
                                </x-badge>
                            </td>

                            <td data-label="Aksi">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($canManage && $admission->status === \App\Enums\AdmissionStatus::ADMITTED)
                                        <form method="POST" action="{{ route('inpatient.admissions.discharge', $admission) }}">
                                            @csrf
                                            <x-button type="submit" size="sm" variant="primary"
                                                      x-on:click.prevent="$dispatch('confirm', {
                                                          action: $el.form.action,
                                                          method: 'POST',
                                                          title: 'Akhiri perawatan inap?',
                                                          message: '{{ $admission->patient->name }} akan ditandai pulang dan tempat tidur {{ $admission->room->code }}-{{ $admission->bed_number }} dikosongkan.',
                                                          confirmLabel: 'Akhiri Perawatan',
                                                      })">
                                                Pulang
                                            </x-button>
                                        </form>
                                    @endif

                                    <x-button :href="route('patients.show', $admission->patient)" size="sm" variant="ghost">
                                        Detail
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>

            @if ($admissions->hasPages())
                {{ $admissions->links() }}
            @endif
        </x-card>
    </div>

    {{-- ===================== Modal terima pasien ===================== --}}
    @if ($canManage)
        @php
            $admitPatients = \App\Models\Patient::active()
                ->whereDoesntHave('admissions', fn ($q) => $q->admitted())
                ->orderBy('name')
                ->get(['id', 'name', 'mr_number', 'gender', 'birth_date'])
                ->map(fn ($p) => [
                    'value' => $p->id,
                    'label' => $p->name.' - '.$p->mr_number.' - '.$p->gender->label().' - '.$p->age.' th',
                ])->all();

            $admitRooms = \App\Models\Room::available()
                ->withCount('activeAdmissions')
                ->orderBy('code')
                ->get()
                ->filter(fn ($r) => $r->availableBeds() > 0)
                ->map(fn ($r) => [
                    'value' => $r->id,
                    'label' => $r->code.' - '.$r->name.' ('.$r->room_class->label().') - sisa '.$r->availableBeds().' bed',
                ])->all();
        @endphp

        <div x-data="{
                open: false,
                patientId: @js(old('patient_id')),
                roomId: @js(old('room_id')),
                bedNumber: @js(old('bed_number')),
            }"
             x-on:open-admit.window="open = true">

            <x-modal title="Terima Pasien Rawat Inap" icon="bed" maxWidth="lg">
                <form method="POST" action="{{ route('inpatient.admissions.store') }}" class="space-y-4">
                    @csrf

                    @if ($admitPatients === [])
                        <x-empty-state compact icon="users" title="Tidak ada pasien yang dapat dirawat"
                                       message="Semua pasien aktif sedang dirawat inap." />
                    @else
                        <x-select name="patient_id" label="Pasien" required
                                  :value="old('patient_id')" :options="$admitPatients"
                                  placeholder="Cari dan pilih pasien"
                                  x-model="patientId" />

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-select name="room_id" label="Kamar" required
                                      :value="old('room_id')" :options="$admitRooms"
                                      placeholder="Pilih kamar" x-model="roomId" />

                            <x-input name="bed_number" label="Nomor Tempat Tidur" required :maxlength="10"
                                     :value="old('bed_number')" placeholder="Contoh: 1 atau A"
                                     x-model="bedNumber" />
                        </div>

                        <x-select name="doctor_id" label="Dokter Penanggung Jawab"
                                  :value="old('doctor_id')" :options="$doctors->pluck('name', 'id')->all()"
                                  placeholder="Opsional" />

                        <x-input name="diagnosis" label="Diagnosa Masuk" :maxlength="255"
                                 :value="old('diagnosis')" placeholder="Contoh: Demam typhoid" />

                        <x-textarea name="notes" label="Catatan" :rows="2" :maxlength="2000"
                                    :value="old('notes')" placeholder="Catatan perawatan (opsional)" />

                        <x-slot:footer>
                            <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                            <button type="submit" class="btn btn-md btn-primary">Terima Pasien</button>
                        </x-slot:footer>
                    @endif
                </form>
            </x-modal>
        </div>
    @endif
@endsection
