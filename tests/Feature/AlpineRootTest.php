<?php

namespace Tests\Feature;

use App\Enums\DiagnosisType;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Drug;
use App\Models\Icd10Code;
use App\Models\InpatientAdmission;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Prescription;
use App\Models\Registration;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menjamin setiap atribut Alpine berada di dalam akar Alpine.
 *
 * Alpine hanya menginisialisasi subtree yang berakar pada elemen ber-`[x-data]`
 * atau `[x-init]`. Elemen dengan `x-on:click`, `x-show`, atau `x-for` yang
 * berada di luar akar tersebut tidak pernah dijalankan, dan tidak ada error apa
 * pun yang muncul — Chrome hanya diam.
 *
 * Akibat yang pernah terjadi: tombol mode gelap, pembuka menu mobile, dan setiap
 * tombol aksi di baris tabel (Hapus, Ubah, Serahkan, Panggil Berikutnya) mati
 * total di semua halaman dan semua peran, sementara seluruh test tetap hijau
 * karena HTML-nya memang terbaca sempurna.
 */
class AlpineRootTest extends TestCase
{
    use RefreshDatabase;

    /** Atribut yang membuat elemen bekerja dan wajib punya akar di atasnya. */
    private const ACTIVE_DIRECTIVES = [
        'x-on:',
        'x-bind:',
        'x-model',
        'x-show',
        'x-for',
        'x-if',
        'x-text',
        'x-html',
        'x-ref',
        'x-transition:',
        'x-cloak',
        '@',
    ];

    /** Atribut yang justru menjadi akar, jadi tidak perlu diperiksa. */
    private const ROOT_DIRECTIVES = ['x-data', 'x-init'];

    public function test_every_alpine_directive_sits_inside_an_alpine_root(): void
    {
        $this->seedDomain();

        $offenders = [];

        foreach ($this->pages() as $page) {
            $html = $this->actingAs($this->superAdmin())->get($page)->assertOk()->getContent();

            foreach ($this->orphanedDirectives($html) as $problem) {
                $offenders[] = $page.' → '.$problem;
            }
        }

        $this->assertSame([], $offenders, "Atribut Alpine di luar akar x-data:\n".implode("\n", $offenders));
    }

    /**
     * Halaman yang memuat interaksi Alpine. Tombol topbar ada di semua halaman,
     * sedangkan tombol aksi baris tabel dan modal hanya di halaman tertentu.
     *
     * @return list<string>
     */
    private function pages(): array
    {
        $pages = [
            '/dashboard', '/profile', '/reports',
            '/patients', '/patients/create', '/registrations', '/registrations/create', '/queue', '/schedules',
            '/inpatient', '/medical-records', '/medical-records/create',
            '/pharmacy/prescriptions', '/billing', '/billing/create', '/rooms',
            '/master/polies', '/master/polies/create', '/master/doctors', '/master/doctors/create',
            '/master/icd10', '/master/drugs',
            '/admin/users', '/admin/users/create', '/admin/audit-logs', '/admin/demo-requests',
        ];

        $detail = array_filter([
            Patient::first() ? route('patients.show', Patient::first()) : null,
            Registration::first() ? route('registrations.show', Registration::first()) : null,
            MedicalRecord::first() ? route('medical-records.show', MedicalRecord::first()) : null,
            Prescription::first() ? route('pharmacy.prescriptions.show', Prescription::first()) : null,
            Invoice::first() ? route('billing.show', Invoice::first()) : null,
            Room::first() ? route('rooms.index') : null,
            Doctor::first() ? route('master.doctors.edit', Doctor::first()) : null,
        ]);

        return array_values(array_filter(
            array_merge($pages, $detail),
            // Hanya URL yang benar-benar terdaftar, agar test gagal karena
            // masalah Alpine dan bukan karena alamat yang salah.
            fn (string $p) => $this->resolves($p),
        ));
    }

    private function resolves(string $path): bool
    {
        try {
            Route::getRoutes()->match(Request::create($path, 'GET'));

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    private function orphanedDirectives(string $html): array
    {
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $problems = [];

        foreach ($dom->getElementsByTagName('*') as $element) {
            if (! $element instanceof \DOMElement) {
                continue;
            }

            $active = $this->activeDirectivesOn($element);

            if ($active === [] || $this->insideAlpineRoot($element)) {
                continue;
            }

            $problems[] = '<'.$element->tagName.'> '.implode(' ', $active);
        }

        return array_values(array_unique($problems));
    }

    /**
     * @return list<string>
     */
    private function activeDirectivesOn(\DOMElement $element): array
    {
        $found = [];

        foreach ($element->attributes as $attribute) {
            $name = $attribute->nodeName;

            foreach (self::ROOT_DIRECTIVES as $root) {
                if ($name === $root) {
                    continue 2;
                }
            }

            foreach (self::ACTIVE_DIRECTIVES as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $found[] = $name;
                    break;
                }
            }
        }

        return $found;
    }

    private function insideAlpineRoot(\DOMElement $element): bool
    {
        for ($node = $element; $node !== null; $node = $node->parentNode) {
            if ($node instanceof \DOMElement
                && ($node->hasAttribute('x-data') || $node->hasAttribute('x-init'))) {
                return true;
            }
        }

        return false;
    }

    private function seedDomain(): void
    {
        $poly = Poly::factory()->create(['name' => 'Poli Umum', 'queue_prefix' => 'A']);
        $doctor = Doctor::factory()->create(['poly_id' => $poly->id, 'name' => 'dr. Tester, Sp.A']);
        $patient = Patient::factory()->create(['name' => 'Pasien Uji']);
        $registration = Registration::factory()->create([
            'patient_id' => $patient->id,
            'poly_id' => $poly->id,
            'doctor_id' => $doctor->id,
        ]);

        DoctorSchedule::factory()->create(['doctor_id' => $doctor->id]);
        Icd10Code::factory()->create(['code' => 'J06']);
        Drug::factory()->create(['code' => 'OBT1', 'name' => 'Amoxicillin', 'category' => 'Antibiotik']);
        Room::factory()->create(['code' => '101']);

        $record = MedicalRecord::factory()->create([
            'registration_id' => $registration->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $record->diagnoses()->create([
            'icd10_code_id' => Icd10Code::first()->id,
            'diagnosis_type' => DiagnosisType::PRIMARY,
        ]);

        Prescription::factory()->create([
            'medical_record_id' => $record->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ])->items()->create([
            'drug_id' => Drug::first()->id,
            'quantity' => 3,
            'unit_price' => 1000,
            'subtotal' => 3000,
        ]);

        Invoice::factory()->create([
            'patient_id' => $patient->id,
            'registration_id' => $registration->id,
        ]);

        InpatientAdmission::factory()->create([
            'patient_id' => $patient->id,
            'room_id' => Room::first()->id,
            'bed_number' => '1',
        ]);
    }
}
