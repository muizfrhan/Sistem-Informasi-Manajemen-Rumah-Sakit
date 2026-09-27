<?php

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DemoRequestController as AdminDemoRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\Master\DoctorController;
use App\Http\Controllers\Master\DrugController;
use App\Http\Controllers\Master\Icd10CodeController;
use App\Http\Controllers\Master\PolyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing.index')->name('home');

Route::post('demo', [DemoRequestController::class, 'store'])
    ->middleware('throttle:demo')
    ->name('demo.store');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:'.Permission::VIEW_DASHBOARD->value)
        ->name('dashboard');

    Route::get('profile', function () {
        return view('profile', ['user' => auth()->user()->load('role.permissions')]);
    })->name('profile');

    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('create', [PatientController::class, 'create'])->name('create');
        Route::post('/', [PatientController::class, 'store'])->name('store');
        Route::get('{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('{patient}', [PatientController::class, 'update'])->name('update');
        Route::patch('{patient}/toggle', [PatientController::class, 'toggle'])->name('toggle');
        Route::delete('{patient}', [PatientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/', [RegistrationController::class, 'index'])->name('index');
        Route::get('create', [RegistrationController::class, 'create'])->name('create');
        Route::post('/', [RegistrationController::class, 'store'])->name('store');
        Route::get('{registration}', [RegistrationController::class, 'show'])->name('show');
    });

    Route::prefix('queue')->name('queue.')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::post('polies/{poly}/call', [QueueController::class, 'call'])->name('call');
        Route::patch('registrations/{registration}/status/{status}', [QueueController::class, 'advance'])
            ->whereIn('status', array_column(RegistrationStatus::cases(), 'value'))
            ->name('advance');
    });

    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/', fn () => redirect()->route('master.polies.index'))->name('index');

        Route::resource('polies', PolyController::class)->parameters(['polies' => 'poly'])->except('show');

        Route::resource('doctors', DoctorController::class)->parameters(['doctors' => 'doctor'])->except('show');

        Route::prefix('icd10')->name('icd10.')->group(function () {
            Route::get('/', [Icd10CodeController::class, 'index'])->name('index');
            Route::post('/', [Icd10CodeController::class, 'store'])->name('store');
            Route::put('{icd10code}', [Icd10CodeController::class, 'update'])->name('update');
            Route::delete('{icd10code}', [Icd10CodeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('drugs')->name('drugs.')->group(function () {
            Route::get('/', [DrugController::class, 'index'])->name('index');
            Route::post('/', [DrugController::class, 'store'])->name('store');
            Route::put('{drug}', [DrugController::class, 'update'])->name('update');
            Route::delete('{drug}', [DrugController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        Route::get('demo-requests', [AdminDemoRequestController::class, 'index'])->name('demo-requests.index');
        Route::patch('demo-requests/{demorequest}', [AdminDemoRequestController::class, 'update'])->name('demo-requests.update');
        Route::delete('demo-requests/{demorequest}', [AdminDemoRequestController::class, 'destroy'])->name('demo-requests.destroy');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditlog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});
