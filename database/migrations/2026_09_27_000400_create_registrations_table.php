<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 30)->unique();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('poly_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('visit_date');
            $table->unsignedInteger('queue_number');
            $table->string('queue_code', 20);
            $table->enum('status', [
                'REGISTERED',
                'QUEUING',
                'CALLED',
                'IN_EXAMINATION',
                'DONE',
                'CANCELLED',
                'NO_SHOW',
            ])->default('REGISTERED');
            $table->boolean('is_emergency')->default(false);
            $table->string('complaint', 255)->nullable();
            $table->enum('visit_type', ['OUTPATIENT', 'INPATIENT'])->default('OUTPATIENT');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('examined_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['poly_id', 'visit_date', 'queue_number'], 'registrations_poly_date_queue_unique');
            $table->index(['visit_date', 'status']);
            $table->index(['patient_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
