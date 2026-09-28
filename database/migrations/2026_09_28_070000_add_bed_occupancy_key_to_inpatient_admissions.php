<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan inpatient_admissions.bed_occupancy_key.
 *
 * Kolom ini sudah tertulis di migrasi 2026_09_27_001100, tetapi migrasi itu
 * tercatat sudah dijalankan (batch 3) sebelum kolomnya ditambahkan, sehingga
 * database yang sudah ada tidak pernah ikut mendapat kolom tersebut.
 * App\Models\InpatientAdmission::booted() mengisinya di setiap penyimpanan, jadi
 * tanpa kolom ini setiap penyimpanan rawat inap — termasuk
 * `inpatient.admissions.cancel` — berakhir dengan QueryException 500.
 *
 * Menyunting migrasi lama tidak akan pernah mengubah database yang sudah
 * menjalankan migrasi tersebut, jadi diperlukan migrasi baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('inpatient_admissions', 'bed_occupancy_key')) {
            return;
        }

        Schema::table('inpatient_admissions', function (Blueprint $table) {
            $table->string('bed_occupancy_key', 40)->nullable()->after('bed_number');
        });

        // Satu baris per kamar dan nomor tempat tidur. Kolom NULL tidak
        // melanggar unique, jadi hanya baris yang terisi yang saling mengunci.
        Schema::table('inpatient_admissions', function (Blueprint $table) {
            $table->unique('bed_occupancy_key', 'inpatient_admissions_bed_occupancy_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('inpatient_admissions', 'bed_occupancy_key')) {
            return;
        }

        Schema::table('inpatient_admissions', function (Blueprint $table) {
            $table->dropUnique('inpatient_admissions_bed_occupancy_unique');
        });

        Schema::table('inpatient_admissions', function (Blueprint $table) {
            $table->dropColumn('bed_occupancy_key');
        });
    }
};
