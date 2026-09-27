<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('mr_number', 20)->unique();
            $table->string('name', 150);
            $table->string('nik', 255);
            $table->string('nik_hash', 64)->unique();
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date');
            $table->string('birth_place', 100)->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->enum('marital_status', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->nullable();
            $table->string('phone', 25)->nullable();
            $table->text('address')->nullable();
            $table->string('village', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->enum('insurance_type', ['NONE', 'BPJS', 'ASURANSI', 'PERUSAHAAN'])->default('NONE');
            $table->string('insurance_number', 100)->nullable();
            $table->string('insurance_name', 150)->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 25)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name']);
            $table->index(['insurance_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
