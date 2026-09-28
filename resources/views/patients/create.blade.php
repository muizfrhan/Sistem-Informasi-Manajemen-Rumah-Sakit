@extends('layouts.app')

@section('title', 'Pasien Baru')
@section('heading', 'Pendaftaran Pasien Baru')
@section('subheading', 'Nomor rekam medis dibuat otomatis oleh sistem')

@section('content')
    <div class="space-y-5">
        <x-page-header title="Pendaftaran Pasien Baru"
                       description="Isi data identitas pasien sesuai dokumen resmi. Field bertanda * wajib diisi.">
            <x-slot:actions>
                <x-button :href="route('patients.index')" variant="ghost" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        @include('patients.form-partial', [
            'editing' => false,
            'patient' => new \App\Models\Patient,
            'insuranceTypes' => $insuranceTypes,
            'genders' => $genders,
        ])
    </div>
@endsection
