<?php

namespace Tests\Feature;

use App\Enums\RoleCode;
use App\Models\Drug;
use App\Models\DrugStockMovement;
use App\Models\InpatientAdmission;
use App\Models\Patient;
use App\Models\Poly;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dua endpoint yang semula mengembalikan 500.
 */
class BrokenWriteActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * prescription_items memakai ON DELETE RESTRICT, jadi menghapus obat yang
     * pernah diresepkan tanpa penjagaan menghasilkan QueryException.
     */
    public function test_drug_with_prescription_history_cannot_be_deleted(): void
    {
        $drug = Drug::factory()->create();
        $prescription = Prescription::factory()->create();

        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'drug_id' => $drug->id,
            'dosage' => '1 tablet',
            'quantity' => 1,
            'unit_price' => 1000,
            'subtotal' => 1000,
        ]);

        $this->actingAs($this->superAdmin())
            ->delete('/master/drugs/'.$drug->id)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('drugs', ['id' => $drug->id]);
    }

    public function test_drug_with_stock_movement_cannot_be_deleted(): void
    {
        $drug = Drug::factory()->create();

        DrugStockMovement::create([
            'drug_id' => $drug->id,
            'type' => \App\Enums\StockMovementType::ADJUSTMENT,
            'quantity' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
            'note' => 'Uji',
        ]);

        $this->actingAs($this->superAdmin())
            ->delete('/master/drugs/'.$drug->id)
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('drugs', ['id' => $drug->id]);
    }

    public function test_unused_drug_can_still_be_deleted(): void
    {
        $drug = Drug::factory()->create();

        $this->actingAs($this->superAdmin())
            ->delete('/master/drugs/'.$drug->id)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('drugs', ['id' => $drug->id]);
    }

    /**
     * Kolom bed_occupancy_key diisi model pada setiap penyimpanan, jadi tanpa
     * kolom ini seluruh penyimpanan rawat inap gagal.
     */
    public function test_inpatient_admission_has_bed_occupancy_key_column(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('inpatient_admissions', 'bed_occupancy_key'),
            'Kolom inpatient_admissions.bed_occupancy_key belum ada. Jalankan php artisan migrate.',
        );
    }

    public function test_super_admin_can_cancel_admission(): void
    {
        $poly = Poly::factory()->create();
        $room = Room::factory()->create(['capacity' => 2]);
        $patient = Patient::factory()->create();

        $admission = InpatientAdmission::factory()->create([
            'room_id' => $room->id,
            'patient_id' => $patient->id,
        ]);

        $this->actingAs($this->superAdmin())
            ->delete('/inpatient/admissions/'.$admission->id)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(
            \App\Enums\AdmissionStatus::CANCELLED,
            $admission->refresh()->status,
        );
    }

    public function test_cancelling_admission_requires_permission(): void
    {
        $admission = InpatientAdmission::factory()->create();

        // Perawat memegang inpatient.manage, jadi diizinkan.
        $this->actingAs($this->userFor(RoleCode::NURSE))
            ->delete('/inpatient/admissions/'.$admission->id)
            ->assertRedirect();

        // Manajemen hanya membaca, jadi ditolak.
        $admission2 = InpatientAdmission::factory()->create();

        $this->actingAs($this->userFor(RoleCode::MANAGER))
            ->delete('/inpatient/admissions/'.$admission2->id)
            ->assertForbidden();
    }
}
