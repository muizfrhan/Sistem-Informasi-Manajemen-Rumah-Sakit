<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drugs', function (Blueprint $table) {
            $table->string('category', 60)->nullable()->after('generic_name');
            $table->date('expired_at')->nullable()->after('stock');
        });

        Schema::table('drugs', function (Blueprint $table) {
            $table->index(['is_active', 'expired_at'], 'drugs_active_expiry_index');
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number', 30)->unique();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['DRAFT', 'DISPENSED', 'CANCELLED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained()->restrictOnDelete();
            $table->string('dosage', 100)->nullable();
            $table->string('route', 60)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('duration', 100)->nullable();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedInteger('subtotal')->default(0);
            $table->timestamps();

            $table->index(['prescription_id']);
        });

        Schema::create('drug_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained()->restrictOnDelete();
            $table->foreignId('prescription_item_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['IN', 'OUT', 'ADJUSTMENT', 'EXPIRED']);
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->string('note', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['drug_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_stock_movements');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');

        Schema::table('drugs', function (Blueprint $table) {
            $table->dropIndex('drugs_active_expiry_index');
            $table->dropColumn(['category', 'expired_at']);
        });
    }
};
