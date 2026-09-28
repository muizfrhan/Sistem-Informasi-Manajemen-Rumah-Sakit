<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('record_type', ['OUTPATIENT', 'INPATIENT', 'EMERGENCY'])->default('OUTPATIENT');
            $table->enum('status', ['DRAFT', 'FINALIZED'])->default('DRAFT');

            $table->string('chief_complaint', 255)->nullable();
            $table->text('anamnesis')->nullable();
            $table->text('physical_exam')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('notes')->nullable();

            $table->string('blood_pressure', 20)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();

            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'created_at']);
            $table->index(['registration_id', 'status']);
        });

        Schema::create('medical_record_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('icd10_code_id')->constrained('icd10_codes')->restrictOnDelete();
            $table->enum('diagnosis_type', ['PRIMARY', 'SECONDARY', 'DIFFERENTIAL'])->default('PRIMARY');
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['medical_record_id', 'diagnosis_type']);
        });

        Schema::create('medical_record_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->unsignedInteger('fee')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_procedures');
        Schema::dropIfExists('medical_record_diagnoses');
        Schema::dropIfExists('medical_records');
    }
};
