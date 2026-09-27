<?php

namespace Database\Seeders;

use App\Enums\RegistrationStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $queue = app(QueueService::class);
        $patients = Patient::orderBy('id')->get();
        $polies = Poly::active()->get();
        $receptionist = User::where('username', 'receptionist')->first();

        if (! $receptionist) {
            return;
        }

        auth()->login($receptionist);

        foreach ($patients as $index => $patient) {
            $poly = $polies[$index % $polies->count()];
            $doctor = Doctor::where('poly_id', $poly->id)->where('is_active', true)->first();

            $registration = $queue->register(
                $patient,
                $poly,
                $doctor,
                $this->complaints[$index % count($this->complaints)],
            );

            if ($index < 4) {
                $queue->advance($registration, RegistrationStatus::QUEUING);
            }

            if ($index < 2) {
                $queue->advance($registration, RegistrationStatus::CALLED);
                $queue->advance($registration, RegistrationStatus::IN_EXAMINATION);
                $queue->advance($registration, RegistrationStatus::DONE);
            }
        }

        auth()->logout();
    }

    protected array $complaints = [
        'Demam dan sakit kepala sejak tiga hari',
        'Batuk kering dan pilek',
        'Nyeri perut bagian bawah',
        'Gangguan penglihatan mendadak',
        'Nyeri pinggang saat mengangkat beban',
        'Susah makan dan mual',
        'Keluhan kaki bengkak',
        'Sakit kepala berulang',
    ];
}
