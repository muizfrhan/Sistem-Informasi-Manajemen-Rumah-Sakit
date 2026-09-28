@extends('layouts.app')

@section('title', 'Kamar')
@section('heading', 'Data Kamar')
@section('subheading', $rooms->total() . ' kamar terdaftar')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Data Kamar"
                       :description="'Total ' . number_format($rooms->total(), 0, ',', '.') . ' kamar'">
            <x-slot:actions>
                <x-button :href="route('inpatient.index')" variant="secondary" icon="bed">Rawat Inap</x-button>
                <x-button permission="inpatient.manage" icon="plus" x-on:click="$dispatch('open-room')">Tambah Kamar</x-button>
            </x-slot:actions>
        </x-page-header>

        <x-card padding="p-0">
            <x-filter-bar :action="route('rooms.index')" :reset="route('rooms.index')">
                <x-slot:fields>
                    <div>
                        <label for="q" class="field-label">Pencarian</label>
                        <input id="q" name="q" type="search" value="{{ request('q') }}"
                               placeholder="Kode atau nama kamar" class="field-control">
                    </div>

                    <div>
                        <label for="class" class="field-label">Kelas Kamar</label>
                        <select id="class" name="class" class="field-control">
                            <option value="">Semua kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->value }}" @selected(request('class') === $class->value)>
                                    {{ $class->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="field-label">Status</label>
                        <select id="status" name="status" class="field-control">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:fields>
            </x-filter-bar>

            <x-data-table :cols="$rooms->count()" min-width="56rem"
                          empty="Belum ada kamar"
                          empty-message="Belum ada kamar yang cocok dengan filter ini."
                          empty-icon="bed">
                <x-slot:emptyAction>
                    <x-button :href="route('rooms.index')" size="sm" variant="secondary" icon="refresh">Reset Filter</x-button>
                </x-slot:emptyAction>

                <x-slot:head>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama Kamar</th>
                        <th scope="col" class="hidden sm:table-cell">Kelas</th>
                        <th scope="col" class="hidden lg:table-cell">Tarif Harian</th>
                        <th scope="col">Okupansi</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </x-slot:head>

                <x-slot:body>
                    @foreach ($rooms as $room)
                        <tr>
                            <td data-label="Kode">
                                <span class="font-mono text-xs font-bold text-ink">{{ $room->code }}</span>
                            </td>

                            <td data-label="Nama Kamar">
                                <span class="font-semibold text-ink">{{ $room->name }}</span>
                                <span class="mt-0.5 block text-xs text-ink-3">
                                    {{ $room->gender_policy->label() }}
                                </span>
                            </td>

                            <td data-label="Kelas" class="hidden sm:table-cell">
                                <x-badge :variant="$room->room_class->badgeClass()">
                                    {{ $room->room_class->label() }}
                                </x-badge>
                            </td>

                            <td data-label="Tarif Harian" class="hidden lg:table-cell text-xs font-semibold tabular-nums">
                                Rp {{ number_format($room->daily_rate, 0, ',', '.') }}
                            </td>

                            <td data-label="Okupansi">
                                @php $ratio = $room->capacity > 0 ? (int) round($room->active_admissions_count / $room->capacity * 100) : 0; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-surface-3">
                                        <div @class([
                                                'h-full rounded-full',
                                                'bg-rose-500' => $ratio >= 100,
                                                'bg-amber-500' => $ratio >= 50 && $ratio < 100,
                                                'bg-emerald-500' => $ratio < 50,
                                            ]) style="width: {{ $ratio }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-ink-2 tabular-nums">
                                        {{ $room->active_admissions_count }}/{{ $room->capacity }}
                                    </span>
                                </div>
                            </td>

                            <td data-label="Status">
                                <x-badge :class="$room->status->badgeClass()" dot>
                                    {{ $room->status->label() }}
                                </x-badge>
                            </td>

                            <td data-label="Aksi">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($canManage)
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                x-on:click="$dispatch('edit-room', @js([
                                                    'id' => $room->id,
                                                    'code' => $room->code,
                                                    'name' => $room->name,
                                                    'room_class' => $room->room_class->value,
                                                    'capacity' => $room->capacity,
                                                    'status' => $room->status->value,
                                                    'gender_policy' => $room->gender_policy->value,
                                                    'daily_rate' => $room->daily_rate,
                                                    'notes' => $room->notes,
                                                    'action' => route('rooms.update', $room),
                                                ]))">
                                            Ubah
                                        </button>

                                        <x-button type="button" size="sm" variant="ghost"
                                                  x-on:click="$dispatch('confirm', {
                                                      action: '{{ route('rooms.destroy', $room) }}',
                                                      method: 'DELETE',
                                                      title: 'Hapus kamar {{ $room->code }}?',
                                                      message: 'Kamar hanya dapat dihapus bila belum pernah digunakan untuk perawatan inap.',
                                                      confirmLabel: 'Hapus Kamar',
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

            @if ($rooms->hasPages())
                {{ $rooms->links() }}
            @endif
        </x-card>
    </div>

    {{-- ===================== Modal tambah / ubah kamar ===================== --}}
    @if ($canManage)
        <div x-data="{
                open: false,
                editing: false,
                form: { id: null, code: '', name: '', room_class: 'CLASS_3', capacity: 1,
                        status: 'AVAILABLE', gender_policy: 'ANY', daily_rate: 0, notes: '' },
                openCreate() { this.editing = false; this.form = { id: null, code: '', name: '', room_class: 'CLASS_3', capacity: 1, status: 'AVAILABLE', gender_policy: 'ANY', daily_rate: 0, notes: '' }; this.open = true },
                openEdit(data) { this.editing = true; this.form = data; this.open = true },
            }"
             x-on:open-room.window="openCreate()"
             x-on:edit-room.window="openEdit($event.detail)">

            <x-modal title="Form Kamar" icon="bed" maxWidth="2xl">
                {{-- Judul dinamis ditangani Alpine karena atribut komponen Blade
                     dievaluasi di sisi server, bukan di browser. --}}
                <p class="mb-4 text-xs text-ink-3" x-text="editing ? 'Mode ubah: data kamar yang sudah ada akan diperbarui.' : 'Mode tambah: isi data kamar baru.'"></p>

                <form :action="editing ? form.action : @js(route('rooms.store'))" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-input name="code" label="Kode Kamar" required :maxlength="20"
                                 placeholder="Contoh: 101" x-bind:value="form.code" />

                        <x-input name="name" label="Nama Kamar" required :maxlength="100"
                                 placeholder="Contoh: Kamar Melati 1" x-bind:value="form.name" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="room_class" class="field-label">Kelas Kamar</label>
                            <select id="room_class" name="room_class" class="field-control" x-model="form.room_class" required>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->value }}">{{ $class->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="gender_policy" class="field-label">Kebijakan Gender</label>
                            <select id="gender_policy" name="gender_policy" class="field-control" x-model="form.gender_policy" required>
                                @foreach ($genderPolicies as $policy)
                                    <option value="{{ $policy->value }}">{{ $policy->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <x-input name="capacity" label="Kapasitas" type="number" required min="1" max="100"
                                 x-bind:value="form.capacity" />

                        <x-input name="daily_rate" label="Tarif Harian" type="number" required min="0" max="100000000"
                                 prefix="Rp" x-bind:value="form.daily_rate" />

                        <div>
                            <label for="status" class="field-label">Status</label>
                            <select id="status" name="status" class="field-control" x-model="form.status" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <x-textarea name="notes" label="Catatan" :rows="2" :maxlength="255"
                                placeholder="Fasilitas atau catatan khusus kamar"
                                x-bind:value="form.notes" />

                    <x-slot:footer>
                        <button type="button" x-on:click="open = false" class="btn btn-md btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-md btn-primary" x-text="editing ? 'Simpan Perubahan' : 'Tambah Kamar'"></button>
                    </x-slot:footer>
                </form>
            </x-modal>
        </div>
    @endif
@endsection
