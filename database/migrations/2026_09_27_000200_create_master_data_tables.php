<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('queue_prefix', 10)->default('A');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('poly_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('nip', 30)->nullable()->index();
            $table->string('specialty', 100)->nullable();
            $table->string('sip_number', 50)->nullable();
            $table->string('phone', 25)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['poly_id', 'is_active']);
        });

        Schema::create('icd10_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('description', 255);
            $table->string('category', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('generic_name', 150)->nullable();
            $table->string('form', 50)->nullable();
            $table->string('strength', 30)->nullable();
            $table->string('unit', 30)->default('Tablet');
            $table->bigInteger('price')->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drugs');
        Schema::dropIfExists('icd10_codes');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('polies');
    }
};
