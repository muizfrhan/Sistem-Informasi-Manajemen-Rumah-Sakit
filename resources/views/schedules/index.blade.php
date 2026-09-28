@extends('layouts.app')

@section('title', 'Jadwal Dokter')
@section('heading', 'Jadwal Praktik Dokter')
@section('subheading', now()->translatedFormat('l, d F Y') . ' - ' . \App\Models\Doctor::query()->active()->count() . ' dokter aktif')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Jadwal Praktik Dokter"
                       description="Jadwal per hari dalam seminggu. Waktu mulai, selesai, durasi per pasien, dan kapasitas.">
            <x-slot:actions>
                <x-button permission="schedule.manage" icon="plus" x-on:click="$dispatch('open-schedule')">
                    Tambah Jadwal
                </x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card padding="p-0">
            <x-filter-bar :action="route('schedules.index')" :reset="route('schedules.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Cari Dokter</label>
                        <input id="q" name="q" type="search" value="{{ request('q') }}"
                               placeholder="Nama dokter" class="field-control">
                    </div>

                    <div>
                        <label for="poly_id" class="field-label">Poli</label>
                        <select id="poly_id" name="poly_id" class="field-control">
                            <option value="">Semua poli</option>
                            @foreach ($polies as $poly)
                                <option value="{{ $poly->id }}" @selected(request('poly_id') == $poly->id)>
                                    {{ $poly->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$doctors->count()" min-width="72rem"
                          empty="Belum ada dokter"
                          empty-message="Belum ada dokter yang cocok dengan filter ini."
                          empty-icon="user">
                <x-slot:head>
                    <tr>
                        <th scope="col" class="min-w-[11rem]">Dokter</th>
                        @foreach ($orderedDays as $day)
                            <th scope="col" class="text-center">
                                <span @class(['font-bold' => $day === now()->isoWeekday()])>
                                    {{ \Illuminate\Support\Str::of($days[$day])->substr(0, 3) }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td data-label="Dokter">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-[11px] font-bold text-brand-ink">
                                        {{ \Illuminate\Support\Str::of($doctor->name)->after('dr. ')->substr(0, 1)->upper() }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-ink">{{ $doctor->name }}</span>
                                        <span class="block truncate text-xs text-ink-3">
                                            {{ $doctor->specialty ?? $doctor->poly?->name ?? '-' }}
                                        </span>
                                    </span>
                                </div>
                            </td>

                            @foreach ($orderedDays as $day)
                                @php $slots = $doctor->schedules->where('day_of_week', $day)->sortBy('start_time'); @endphp
                                <td data-label="{{ $days[$day] }}" class="align-top">
                                    @if ($slots->isEmpty())
                                        <span class="block text-center text-xs text-ink-3/50">&mdash;</span>
                                    @else
                                        <div class="space-y-1">
                                            @foreach ($slots as $slot)
                                                <div @class([
                                                    'rounded-md border-l-2 px-2 py-1 text-[11px]',
                                                    'border-brand bg-brand-soft text-brand-ink' => $slot->is_active,
                                                    'border-line-2 bg-surface-2 text-ink-3 line-through' => ! $slot->is_active,
                                                ])>
                                                    <p class="font-semibold tabular-nums">{{ $slot->time_range }}</p>
                                                    <p class="opacity-80">{{ $slot->capacity }} pasien/hari</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>
        </x-card>

        {{-- ===================== Daftar jadwal ===================== --}}
        <x-card title="Semua Jadwal Terdaftar" :subtitle="$doctors->sum(fn ($d) => $d->schedules->count()) . ' jadwal'"
                padding="p-0">
            @php $allSchedules = $doctors->flatMap(fn ($d) => $d->schedules->map(fn ($s) => ['doctor' => $d, 'schedule' => $s])); @endphp

            <x-data-table :cols="$allSchedules->count()" min-width="54rem"
                          empty="Belum ada jadwal"
                          empty-message="Belum ada jadwal praktik yang terdaftar. Tambahkan jadwal untuk dokter."
                          empty-icon="calendar">
                <x-slot:head>
                    <tr>
                        <th scope="col">Dokter</th>
                        <th scope="col" class="hidden sm:table-cell">Poli</th>
                        <th scope="col">Hari</th>
                        <th scope="col">Waktu</th>
                        <th scope="col" class="hidden lg:table-cell">Durasi/ Pasien</th>
                        <th scope="col" class="hidden lg:table-cell">Kapasitas</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($allSchedules as $row)
                        @php $schedule = $row['schedule']; @endphp
                        <tr>
                            <td data-label="Dokter">
                                <a href="{{ route('master.doctors.edit', $row['doctor']) }}"
                                   class="font-semibold text-ink hover:underline">{{ $row['doctor']->name }}</a>
                            </td>
                            <td data-label="Poli" class="hidden sm:table-cell text-xs text-ink-2">
                                {{ $schedule->poly?->name ?? $row['doctor']->poly?->name ?? '-' }}
                            </td>
                            <td data-label="Hari">
                                <x-badge variant="info">{{ $schedule->day_label }}</x-badge>
                            </td>
                            <td data-label="Waktu">
                                <span class="font-mono text-xs font-semibold text-ink">{{ $schedule->time_range }}</span>
                            </td>
                            <td data-label="Durasi" class="hidden lg:table-cell text-xs text-ink-2">
                                {{ $schedule->slot_minutes }} menit
                            </td>
                            <td data-label="Kapasitas" class="hidden lg:table-cell text-xs tabular-nums">
                                {{ $schedule->capacity }} pasien
                            </td>
                            <td data-label="Status">
                                <x-active-badge :active="$schedule->is_active" />
                            </td>
                            <td data-label="Aksi">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($canManage)
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                x-on:click="$dispatch('edit-schedule', @js([
                                                    'id' => $schedule->id,
                                                    'doctor_id' => $schedule->doctor_id,
                                                    'poly_id' => $schedule->poly_id,
                                                    'day_of_week' => $schedule->day_of_week,
                                                    'start_time' => substr((string) $schedule->start_time, 0, 5),
                                                    'end_time' => substr((string) $schedule->end_time, 0, 5),
                                                    'slot_minutes' => $schedule->slot_minutes,
                                                    'is_active' => $schedule->is_active,
                                                    'action' => route('schedules.update', $schedule),
                                                ]))">
                                            Ubah
                                        </button>

                                        <x-button type="button" size="sm" variant="ghost"
                                                  x-on:click="$dispatch('confirm', {
                                                      action: '{{ route('schedules.destroy', $schedule) }}',
                                                      method: 'DELETE',
                                                      title: 'Hapus jadwal {{ $schedule->day_label }}?',
                                                      message: 'Jadwal praktik {{ $row['doctor']->name }} pada hari {{ $schedule->day_label }} pukul {{ $schedule->time_range }} akan dihapus.',
                                                      confirmLabel: 'Hapus Jadwal',
                                                  })">
                                            Hapus
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-data-table>
        </x-card>
    </div>

    {{-- ===================== Modal tambah / ubah jadwal ===================== --}}
    @if ($canManage)
        @php
            $scheduleDoctors = \App\Models\Doctor::active()->orderBy('name')->get()
                ->mapWithKeys(fn ($d) => [$d->id => $d->name.' - '.($d->specialty ?? $d->poly?->name ?? 'Tanpa spesialisasi')]);
        @endphp

        <div x-data="{
                open: false,
                editing: false,
                form: { id: null, doctor_id: '', poly_id: '', day_of_week: 1, start_time: '08:00',
                        end_time: '12:00', slot_minutes: 15, is_active: true, action: '' },
                openCreate() { this.editing = false; this.form.action = '{{ route('schedules.store') }}'; this.open = true },
                openEdit(data) { this.editing = true; this.form = data; this.open = true },
            }"
             x-on:open-schedule.window="openCreate()"
             x-on:edit-schedule.window="openEdit($event.detail)">

            <x-modal title="Form Jadwal" icon="calendar" maxWidth="lg">
                <p class="mb-4 text-xs text-ink-3" x-text="editing ? 'Mode ubah: jadwal yang dipilih akan diperbarui.' : 'Mode tambah: isi jadwal praktik baru.'"></p>

                <form :action="editing ? form.action : @js(route('schedules.store'))" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <x-select name="doctor_id" label="Dokter" required
                              :options="$scheduleDoctors->all()"
                              placeholder="Pilih dokter" x-bind:value="form.doctor_id" />

                    <x-select name="poly_id" label="Poli"
                              :options="$polies->pluck('name', 'id')->all()"
                              placeholder="Ikuti poli dokter" x-bind:value="form.poly_id" />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label for="day_of_week" class="field-label">Hari</label>
                            <select id="day_of_week" name="day_of_week" class="field-control" x-model="form.day_of_week" required>
                                @foreach ($days as $number => $label)
                                    <option value="{{ $number }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="start_time" class="field-label">Mulai</label>
                            <input id="start_time" name="start_time" type="time" class="field-control"
                                   x-bind:value="form.start_time" required>
                        </div>

                        <div>
                            <label for="end_time" class="field-label">Selesai</label>
                            <input id="end_time" name="end_time" type="time" class="field-control"
                                   x-bind:value="form.end_time" required>
                        </div>
                    </div>

                    <x-input name="slot_minutes" label="Durasi per Pasien" type="number" required
                             min="5" max="240" suffix="menit" x-bind:value="form.slot_minutes" />

                    <x-checkbox name="is_active" label="Jadwal Aktif" :value="true" />

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary" x-text="editing ? 'Simpan Perubahan' : 'Tambah Jadwal'"></button>
                    </x-slot:footer>
                </form>
            </x-modal>
        </div>
    @endif
@endsection
