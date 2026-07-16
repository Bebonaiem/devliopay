<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('setup_fee');
            $table->decimal('tax', 10, 2)->default(0)->after('subtotal');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')->nullable()->after('tax')->constrained('tax_rates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn('tax_rate_id');
        });
    }
};
