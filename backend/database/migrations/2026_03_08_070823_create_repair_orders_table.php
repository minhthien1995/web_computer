<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('repair_service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique(); // RP-20260308-0001
            // Snapshot of customer info (for walk-ins or history preservation)
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            // Device info
            $table->string('device_type'); // phone, laptop, tablet
            $table->string('device_brand');
            $table->string('device_model');
            $table->string('device_serial')->nullable();
            $table->text('issue_description');
            $table->text('customer_notes')->nullable();
            // Repair status
            $table->enum('status', ['received', 'diagnosing', 'waiting_parts', 'repairing', 'quality_check', 'ready', 'delivered', 'cancelled'])->default('received');
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            // Diagnosis & repair notes
            $table->text('diagnosis_notes')->nullable();
            $table->text('repair_notes')->nullable();
            // Pricing
            $table->decimal('quoted_price', 15, 2)->nullable();
            $table->decimal('final_price', 15, 2)->nullable();
            $table->decimal('deposit_paid', 15, 2)->default(0);
            // Timing
            $table->timestamp('estimated_ready_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            // Device images stored as JSON array of URLs
            $table->json('device_images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
