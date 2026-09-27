@extends('layouts.app')

@section('title', 'Pasien Baru')
@section('heading', 'Pendaftaran Pasien Baru')
@section('subheading', 'Lengkapi data identitas pasien sesuai dokumen resmi')

@section('content')
    @include('patients.form-partial', ['patient' => $patient])
@endsection
