<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('institution', 200);
            $table->string('position', 100)->nullable();
            $table->string('email', 150);
            $table->string('phone', 30);
            $table->enum('facility_type', ['HOSPITAL', 'CLINIC', 'PRIVATE_PRACTICE', 'OTHER'])->default('HOSPITAL');
            $table->unsignedInteger('bed_count')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['NEW', 'CONTACTED', 'DEMO_SCHEDULED', 'WON', 'LOST'])->default('NEW');
            $table->text('internal_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_requests');
    }
};
