<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_pricing_id')->constrained('product_pricing')->nullOnDelete();
            $table->foreignId('to_pricing_id')->constrained('product_pricing')->nullOnDelete();
            $table->decimal('price_difference', 10, 2)->default(0);
            $table->decimal('credit_applied', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('type')->default('upgrade');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_upgrades');
    }
};
