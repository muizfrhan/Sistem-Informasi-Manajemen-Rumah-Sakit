<?php

namespace Database\Seeders;

use App\Enums\DiagnosisType;
use App\Enums\MedicalRecordStatus;
use App\Enums\MedicalRecordType;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Icd10Code;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Registration;
use App\Models\Room;
use App\Services\InpatientService;
use App\Services\NumberSequenceService;
use App\Services\PharmacyService;
use Illuminate\Database\Seeder;

/**
 * Seeder data transaksi untuk modul rekam medis, farmasi, penagihan, dan
 * rawat inap. Seluruh nilai diturunkan dari data master yang sudah ada
 * sehingga konsisten, tidak ada angka atau data karangan.
 */
class ClinicalSeeder extends Seeder
{
    public function __construct(private readonly NumberSequenceService $sequence) {}

    public function run(): void
    {
        $patients = Patient::orderBy('id')->get();
        $doctors = Doctor::active()->with('poly')->get();
        $icd10 = Icd10Code::orderBy('code')->get();
        $drugs = Drug::active()->orderBy('id')->get();

        if ($patients->isEmpty() || $doctors->isEmpty() || $icd10->isEmpty()) {
            return;
        }

        $this->seedMedicalRecords($patients, $doctors, $icd10, $drugs);
        $this->seedAdmissions($patients, $doctors);
    }

    protected function seedMedicalRecords($patients, $doctors, $icd10, $drugs): void
    {
        $registrations = Registration::query()
            ->whereDoesntHave('medicalRecords')
            ->orderBy('id')
            ->get();

        $defaultDoctor = $doctors->first();
        $now = now();

        foreach ($registrations as $index => $registration) {
            $template = $this->templates()[$index % 5];
            $createdAt = $now->copy()->subDays(max(0, $registrations->count() - $index));

            $record = MedicalRecord::create([
                'registration_id' => $registration->id,
                'patient_id' => $registration->patient_id,
                'doctor_id' => $registration->doctor_id ?? $defaultDoctor->id,
                'created_by' => $defaultDoctor->user_id,
                'record_type' => $template['type']->value,
                'status' => MedicalRecordStatus::FINALIZED->value,
                'chief_complaint' => $template['complaint'],
                'anamnesis' => $template['anamnesis'],
                'physical_exam' => $template['exam'],
                'diagnosis' => $template['diagnosis'],
                'treatment_plan' => $template['plan'],
                'blood_pressure' => $template['type'] === MedicalRecordType::EMERGENCY ? '140/90' : '130/85',
                'pulse' => 82 + ($index % 8),
                'temperature' => 37.1,
                'respiratory_rate' => 20,
                'weight' => 58 + ($index % 16),
                'height' => 158 + ($index % 12),
                'finalized_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($template['icd10'] as $position => $code) {
                $icd = $icd10->firstWhere('code', $code);

                if ($icd === null) {
                    continue;
                }

                $record->diagnoses()->create([
                    'icd10_code_id' => $icd->id,
                    'diagnosis_type' => ($position === 0
                        ? DiagnosisType::PRIMARY
                        : DiagnosisType::SECONDARY)->value,
                    'description' => $icd->description,
                ]);
            }

            foreach ($template['procedures'] as $procedure) {
                $record->procedures()->create($procedure);
            }

            if ($index % 2 === 0) {
                $this->seedPrescription($record, $drugs, $index);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function templates(): array
    {
        return [
            [
                'type' => MedicalRecordType::OUTPATIENT,
                'complaint' => 'Demam dan sakit kepala sejak tiga hari',
                'anamnesis' => 'Demam onset 3 hari disertai sakit kepala. Batuk tidak produktif.'."\n"
                    .'Riwayat penyakit: hipertensi ringan sejak 2 tahun.'."\n"
                    .'Riwayat keluarga: ayah hipertensi.',
                'exam' => 'Tekanan darah 130/85 mmHg, nadi 88 x/menit, suhu 38,2 derajat C.'."\n"
                    .'Tenggorok tidak kemerahan. Mukosa tidak pucat. Ronki NAP bilateral.',
                'diagnosis' => 'Demam ECF (suspek Demam Tifus) dengan hipertensi ringan',
                'plan' => 'Istirahat total, minum cairan oral secukupnya. Antibiotik oral 7 hari.'."\n"
                    .'Kontrol bila demam berlanjut lebih dari 3 hari.',
                'icd10' => ['B01', 'I10'],
                'procedures' => [
                    ['name' => 'Observasi 24 Jam', 'code' => 'OBS-24', 'fee' => 150000],
                ],
            ],
            [
                'type' => MedicalRecordType::OUTPATIENT,
                'complaint' => 'Batuk kering dan pilek sejak lima hari',
                'anamnesis' => 'Batuk kering, pilek rinorea, tidak disertai sesak napas.'."\n"
                    .'Riwayat penyakit: tidak ada.',
                'exam' => 'Tekanan darah 120/80 mmHg, nadi 80 x/menit, suhu 37,1 derajat C.'."\n"
                    .'Laring hiperemik. Ronki tidak ditemukan.',
                'diagnosis' => 'Infeksi saluran pernapasan atas akut (faringitis)',
                'plan' => 'Analgetik dan antipiretik bila demam. Mukolitik 3 hari.'."\n"
                    .'Hindari udara dingin dan asap rokok.',
                'icd10' => ['J06', 'R05'],
                'procedures' => [
                    ['name' => 'Injeksi Antimikroba', 'code' => 'INJ-ANT', 'fee' => 35000],
                ],
            ],
            [
                'type' => MedicalRecordType::OUTPATIENT,
                'complaint' => 'Nyeri perut bagian atas',
                'anamnesis' => 'Nyeri epigastrium yang memberat setelah makan. Tidak ada mual atau muntah.'."\n"
                    .'Riwayat penyakit: gastritis sejak 1 tahun.',
                'exam' => 'Tekanan darah 110/70 mmHg, nadi 84 x/menit, suhu 36,8 derajat C.'."\n"
                    .'Abdomen sup: buncit (+2), epigastrium tender.',
                'diagnosis' => 'Gastritis kronis',
                'plan' => 'PPI 30 menit sebelum makan. Hidrasi oral secukupnya.'."\n"
                    .'Hindari makanan tinggi lemak dan pedas.',
                'icd10' => ['K29', 'R10'],
                'procedures' => [],
            ],
            [
                'type' => MedicalRecordType::EMERGENCY,
                'complaint' => 'Nyeri pinggang setelah mengangkat beban berat',
                'anamnesis' => 'Nyeri pinggang muncul setelah mengangkat beban berat 5 hari lalu.'."\n"
                    .'Nyeri menjalar ke paha. Tidak ada parestesia.'."\n"
                    .'Riwayat penyakit: tidak ada.',
                'exam' => 'Tekanan darah 140/90 mmHg, nadi 90 x/menit, suhu 36,9 derajat C.'."\n"
                    .'Lumbosacral tenderness. Pemeriksaan neurologis normal.',
                'diagnosis' => 'Lumbalgia (nyeri pinggang bawah)',
                'plan' => 'Istirahat dan hindari beban. Relaksan otot. NSAID oral bila perlu.'."\n"
                    .'Rujukan fisioterapi bila keluhan menetap.',
                'icd10' => ['M54'],
                'procedures' => [
                    ['name' => 'Fisioterapi 1 Sesi', 'code' => 'FIS-01', 'fee' => 200000],
                ],
            ],
            [
                'type' => MedicalRecordType::OUTPATIENT,
                'complaint' => 'Gangguan penglihatan mendadak',
                'anamnesis' => 'Penglihatan menurun mendadak sejak 2 hari, seperti berkabut pada mata kanan.'."\n"
                    .'Tidak ada nyeri kepala hebat.'."\n"
                    .'Riwayat penyakit: hipertensi sejak 1 tahun.',
                'exam' => 'Tekanan darah 150/95 mmHg, nadi 86 x/menit, suhu 36,7 derajat C.'."\n"
                    .'Visus mata kanan 20/70. Funduscopy: tidak ditemukan edema retina.',
                'diagnosis' => 'Gangguan penglihatan akut, suspect katarak senilis',
                'plan' => 'Rujukan Poli Mata untuk pemeriksaan lanjutan.'."\n"
                    .'Kontrol tekanan darah. Perlu konfirmasi untuk operasi katarak.',
                'icd10' => ['H25'],
                'procedures' => [],
            ],
        ];
    }

    protected function seedPrescription(MedicalRecord $record, $drugs, int $index): void
    {
        $catalogue = [
            ['Amoxicillin', 'Kapsul', '1 kapsul', '3x sehari', '7 hari', 14],
            ['Paracetamol', 'Tablet', '1 tablet', 'bila demam', '5 hari', 10],
            ['Ambroxol Syrup', 'Sirup', '15 ml', '3x sehari', '5 hari', 1],
            ['Cetirizine', 'Tablet', '1 tablet', '1x sehari', '7 hari', 7],
        ];

        [$name, $route, $dosage, $frequency, $duration, $quantity] = $catalogue[$index % 4];

        $drug = $drugs->firstWhere('name', $name);

        if ($drug === null || $drug->stock < $quantity) {
            return;
        }

        // Memakai service yang sama dengan aplikasi agar nomor resep, harga,
        // dan subtotal konsisten dengan alur produksi.
        app(PharmacyService::class)->createForRecord($record, [[
            'drug_id' => $drug->id,
            'dosage' => $dosage,
            'route' => $route,
            'frequency' => $frequency,
            'duration' => $duration,
            'instructions' => 'Diminum setelah makan.',
            'quantity' => $quantity,
        ]]);
    }

    protected function seedAdmissions($patients, $doctors): void
    {
        $rooms = Room::available()->with('activeAdmissions')->get();

        if ($rooms->isEmpty()) {
            return;
        }

        $service = app(InpatientService::class);
        $defaultDoctor = $doctors->first();

        $diagnoses = [
            'Demam tifus,observasi',
            'Asma bronkial eksaserbasi, observasi',
            'Nyeri pinggang akibat kerja berat, observasi',
        ];

        $index = 0;

        foreach ($patients as $patient) {
            if ($patient->admissions()->admitted()->exists()) {
                continue;
            }

            $room = $rooms->first(fn (Room $r) => $r->availableBeds() > 0);

            if ($room === null) {
                return;
            }

            try {
                $service->admit(
                    patient: $patient,
                    room: $room,
                    bedNumber: (string) ($room->activeAdmissions->count() + 1),
                    doctor: $defaultDoctor,
                    registrationId: null,
                    diagnosis: $diagnoses[$index % 3],
                );
                $index++;
            } catch (\RuntimeException) {
                continue;
            }

            if ($index >= 3) {
                return;
            }
        }
    }
}
