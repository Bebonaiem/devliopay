<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_currency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_id')->constrained('product_pricing')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('setup_fee', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['pricing_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_currency');
    }
};
