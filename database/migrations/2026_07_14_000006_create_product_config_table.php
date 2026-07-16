<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('label');
            $table->enum('type', ['text', 'textarea', 'number', 'select', 'checkbox', 'radio'])->default('text');
            $table->json('options')->nullable();
            $table->string('default')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('is_checkout_field')->default(false);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_config');
    }
};
