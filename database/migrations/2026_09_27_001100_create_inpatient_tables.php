<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->enum('room_class', ['VIP', 'CLASS_1', 'CLASS_2', 'CLASS_3', 'ICU', 'ISOLATION'])->default('CLASS_3');
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->enum('status', ['AVAILABLE', 'MAINTENANCE'])->default('AVAILABLE');
            $table->enum('gender_policy', ['ANY', 'MALE', 'FEMALE'])->default('ANY');
            $table->unsignedInteger('daily_rate')->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['status', 'room_class']);
        });

        Schema::create('inpatient_admissions', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number', 30)->unique();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bed_number', 10);
            $table->enum('status', ['ADMITTED', 'DISCHARGED', 'CANCELLED'])->default('ADMITTED');
            $table->string('diagnosis', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('admitted_at');
            $table->timestamp('discharged_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Satu tempat tidur hanya boleh dihuni satu pasien pada waktu yang
            // sama. Kunci unik pada kolom ini baru diisi selama status
            // ADMITTED dan di-nolkan saat pasien pulang, sehingga riwayat
            // perawatan tetap tersimpan. Kolom NULL tidak melanggar unique
            // index di MySQL maupun PostgreSQL.
            $table->string('bed_occupancy_key', 40)->nullable()->unique();

            $table->index(['status', 'admitted_at']);
            $table->index(['patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inpatient_admissions');
        Schema::dropIfExists('rooms');
    }
};
