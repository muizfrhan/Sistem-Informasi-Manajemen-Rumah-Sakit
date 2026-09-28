<?php

use App\Enums\Permission;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DemoRequestController as AdminDemoRequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoRequestController;
use App\Http\Controllers\InpatientController;
use App\Http\Controllers\Master\DoctorController;
use App\Http\Controllers\Master\DrugController;
use App\Http\Controllers\Master\Icd10CodeController;
use App\Http\Controllers\Master\PolyController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Pengguna yang sudah masuk langsung diarahkan ke dashboard; tamu melihat
// halaman pemasaran publik.
Route::get('/', function (Request $request) {
    return $request->user()
        ? redirect()->route('dashboard')
        : view('landing.index');
})->name('home');

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
        $user = auth()->user()->load(['role', 'permissions']);

        // Aktivitas audit milik pengguna sendiri, hanya bila berwenang.
        $recentActivity = $user->can(Permission::VIEW_AUDIT_LOG)
            ? AuditLog::query()
                ->where('user_id', $user->id)
                ->latest('id')
                ->limit(8)
                ->get()
            : collect();

        return view('profile', compact('user', 'recentActivity'));
    })->name('profile');

    // ------------------------------------------------------------------
    // Pasien
    // ------------------------------------------------------------------
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

    // ------------------------------------------------------------------
    // Pendaftaran & Antrean
    // ------------------------------------------------------------------
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

    // ------------------------------------------------------------------
    // Rekam Medis
    // ------------------------------------------------------------------
    Route::prefix('medical-records')->name('medical-records.')->group(function () {
        Route::get('/', [MedicalRecordController::class, 'index'])->name('index');
        Route::get('create', [MedicalRecordController::class, 'create'])->name('create');
        Route::post('/', [MedicalRecordController::class, 'store'])->name('store');
        Route::post('{medicalrecord}/prescriptions', [MedicalRecordController::class, 'storePrescription'])
            ->name('prescriptions.store');
        Route::post('{medicalrecord}/finalize', [MedicalRecordController::class, 'finalize'])->name('finalize');
        Route::get('{medicalrecord}', [MedicalRecordController::class, 'show'])->name('show');
        Route::get('{medicalrecord}/edit', [MedicalRecordController::class, 'edit'])->name('edit');
        Route::put('{medicalrecord}', [MedicalRecordController::class, 'update'])->name('update');
    });

    // ------------------------------------------------------------------
    // Farmasi
    // ------------------------------------------------------------------
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
            Route::get('/', [PharmacyController::class, 'index'])->name('index');
            Route::get('{prescription}', [PharmacyController::class, 'show'])->name('show');
            Route::post('{prescription}/dispense', [PharmacyController::class, 'dispense'])->name('dispense');
            Route::post('{prescription}/cancel', [PharmacyController::class, 'cancel'])->name('cancel');
        });

        Route::patch('drugs/{drug}/stock', [PharmacyController::class, 'adjustStock'])->name('drugs.stock');
    });

    // ------------------------------------------------------------------
    // Keuangan
    // ------------------------------------------------------------------
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('create', [BillingController::class, 'create'])->name('create');
        Route::post('/', [BillingController::class, 'store'])->name('store');
        Route::get('{invoice}', [BillingController::class, 'show'])->name('show');
        Route::post('{invoice}/payments', [BillingController::class, 'pay'])->name('payments.store');
        Route::delete('{invoice}', [BillingController::class, 'destroy'])->name('destroy');
    });

    // ------------------------------------------------------------------
    // Rawat Inap
    // ------------------------------------------------------------------
    Route::prefix('inpatient')->name('inpatient.')->group(function () {
        Route::get('/', [InpatientController::class, 'index'])->name('index');
        Route::post('/admissions', [InpatientController::class, 'store'])->name('admissions.store');
        Route::post('admissions/{inpatientadmission}/discharge', [InpatientController::class, 'discharge'])
            ->name('admissions.discharge');
        Route::delete('admissions/{inpatientadmission}', [InpatientController::class, 'cancel'])
            ->name('admissions.cancel');
    });

    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::put('{room}', [RoomController::class, 'update'])->name('update');
        Route::delete('{room}', [RoomController::class, 'destroy'])->name('destroy');
    });

    // ------------------------------------------------------------------
    // Jadwal Dokter
    // ------------------------------------------------------------------
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/', [ScheduleController::class, 'store'])->name('store');
        Route::put('{schedule}', [ScheduleController::class, 'update'])->name('update');
        Route::delete('{schedule}', [ScheduleController::class, 'destroy'])->name('destroy');
    });

    // ------------------------------------------------------------------
    // Laporan
    // ------------------------------------------------------------------
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // ------------------------------------------------------------------
    // Master Data
    // ------------------------------------------------------------------
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

    // ------------------------------------------------------------------
    // Administrasi
    // ------------------------------------------------------------------
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
