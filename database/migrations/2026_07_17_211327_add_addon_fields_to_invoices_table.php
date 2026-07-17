<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('addon_id')->nullable()->constrained()->nullOnDelete()->after('service_id');
            $table->foreignId('service_addon_id')->nullable()->after('addon_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['addon_id', 'service_addon_id']);
        });
    }
};
