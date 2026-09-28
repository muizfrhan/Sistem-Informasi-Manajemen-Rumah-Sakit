<?php

namespace Database\Seeders;

use App\Enums\RoomClass;
use App\Enums\RoomStatus;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Drug;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FacilitySeeder extends Seeder
{
    /**
     * Data master pendukung modul baru: jadwal dokter, kamar rawat inap,
     * serta tanggal kedaluwarsa dan kategori obat.
     *
     * Tidak menghapus data yang sudah ada; memakai updateOrCreate /
     * firstOrCreate agar aman dijalankan berulang.
     */
    public function run(): void
    {
        $this->seedSchedules();
        $this->seedRooms();
        $this->seedDrugMetadata();
    }

    protected function seedSchedules(): void
    {
        // Pola jadwal tetap per dokter agar realistis dan tidak acak.
        $patterns = [
            // [hari, mulai, selesai, slot, kuota]
            [[1, 3, 5], '08:00', '12:00', 15, 16],
            [[2, 4], '08:00', '12:00', 15, 16],
            [[1, 2, 3, 4, 5], '14:00', '17:00', 15, 12],
            [[6], '08:00', '11:00', 20, 9],
        ];

        foreach (Doctor::with('poly')->orderBy('id')->get() as $index => $doctor) {
            $pattern = $patterns[$index % count($patterns)];

            foreach ($pattern[0] as $day) {
                DoctorSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                        'start_time' => $pattern[1].':00',
                    ],
                    [
                        'poly_id' => $doctor->poly_id,
                        'end_time' => $pattern[2].':00',
                        'slot_minutes' => $pattern[3],
                        'quota' => $pattern[4],
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    protected function seedRooms(): void
    {
        $rooms = [
            ['101', 'Kamar Melati 1', RoomClass::VIP, 1, 900000],
            ['102', 'Kamar Melati 2', RoomClass::VIP, 1, 900000],
            ['201', 'Kamar Anggrek 1', RoomClass::CLASS_1, 2, 750000],
            ['202', 'Kamar Anggrek 2', RoomClass::CLASS_1, 2, 750000],
            ['301', 'Kamar Kenanga 1', RoomClass::CLASS_2, 3, 500000],
            ['302', 'Kamar Kenanga 2', RoomClass::CLASS_2, 3, 500000],
            ['401', 'Kamar Melur 1', RoomClass::CLASS_3, 4, 300000],
            ['402', 'Kamar Melur 2', RoomClass::CLASS_3, 4, 300000],
            ['ICU-1', 'Intensive Care 1', RoomClass::ICU, 2, 2500000],
            ['ISO-1', 'Kamar Isolasi 1', RoomClass::ISOLATION, 2, 1200000],
        ];

        foreach ($rooms as $index => [$code, $name, $class, $capacity, $rate]) {
            Room::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'room_class' => $class->value,
                    'capacity' => $capacity,
                    'status' => RoomStatus::AVAILABLE->value,
                    'gender_policy' => 'ANY',
                    'daily_rate' => $rate,
                    'notes' => null,
                ],
            );
        }
    }

    protected function seedDrugMetadata(): void
    {
        $categories = [
            'OB001' => 'Antibiotik', 'OB002' => 'Analgetik', 'OB003' => 'Antiinflamasi',
            'OB004' => 'Antibiotik', 'OB005' => 'Antihistamin', 'OB006' => 'Mukolitik',
            'OB007' => 'Antidiabetes', 'OB008' => 'Statin', 'OB009' => 'Antihipertensi',
            'OB010' => 'Antihipertensi', 'OB011' => 'Bronkodilator', 'OB012' => 'Antibiotik',
            'OB013' => 'Rehidrasi', 'OB014' => 'Vitamin', 'OB015' => 'Antasid',
            'OB016' => 'Bronkodilator', 'OB017' => 'Antiemetik',
        ];

        foreach (Drug::all() as $drug) {
            $updates = [];

            if (isset($categories[$drug->code])) {
                $updates['category'] = $categories[$drug->code];
            }

            $updates['expired_at'] = $this->demoExpiry($drug->code);

            $drug->forceFill(array_filter($updates, fn ($v) => $v !== null))->save();
        }
    }

    /**
     * Tanggal kedaluwarsa yang relevan untuk demo peringatan farmasi.
     * Setiap kode dikunci agar tidak berubah-ubah saat seeder dijalankan ulang.
     */
    protected function demoExpiry(string $code): ?string
    {
        return match ($code) {
            'OB001' => Carbon::today()->addMonths(14)->toDateString(),
            'OB003' => Carbon::today()->addDays(45)->toDateString(),
            'OB006' => Carbon::today()->addMonths(9)->toDateString(),
            'OB011' => Carbon::today()->addDays(20)->toDateString(),
            'OB012' => Carbon::today()->addMonths(6)->toDateString(),
            'OB014' => Carbon::today()->addMonths(11)->toDateString(),
            'OB016' => Carbon::today()->addDays(60)->toDateString(),
            'OB017' => Carbon::today()->addDays(25)->toDateString(),
            default => Carbon::today()->addYears(2)->toDateString(),
        };
    }
}
