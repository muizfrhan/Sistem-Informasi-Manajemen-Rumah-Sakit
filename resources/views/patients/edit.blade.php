@extends('layouts.app')

@section('title', 'Ubah Data Pasien')
@section('heading', 'Ubah Data Pasien')
@section('subheading', 'No. Rekam Medis '.$patient->mr_number)

@section('content')
    @include('patients.form-partial', ['patient' => $patient])
@endsection
