@extends('layouts.app')

@section('title', 'Ubah Data Pasien')
@section('heading', 'Ubah Data Pasien')
@section('subheading', $patient->name . ' - ' . $patient->mr_number)

@section('content')
    <div class="space-y-5">
        <x-page-header title="Ubah Data Pasien"
                       :description="'Nomor rekam medis ' . $patient->mr_number . ' tidak dapat diubah.'">
            <x-slot:actions>
                <x-button :href="route('patients.show', $patient)" variant="ghost" icon="arrow-left">Kembali</x-button>
            </x-slot:actions>
        </x-page-header>

        @include('patients.form-partial', [
            'editing' => true,
            'patient' => $patient,
            'insuranceTypes' => $insuranceTypes,
            'genders' => $genders,
        ])
    </div>
@endsection
