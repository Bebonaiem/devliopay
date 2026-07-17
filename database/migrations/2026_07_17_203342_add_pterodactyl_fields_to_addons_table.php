<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->integer('extra_ram')->default(0)->after('sort_order');
            $table->integer('extra_disk')->default(0)->after('extra_ram');
            $table->integer('extra_cpu')->default(0)->after('extra_disk');
            $table->integer('extra_databases')->default(0)->after('extra_cpu');
            $table->integer('extra_allocations')->default(0)->after('extra_databases');
            $table->integer('extra_backups')->default(0)->after('extra_allocations');
        });
    }

    public function down(): void
    {
        Schema::table('addons', function (Blueprint $table) {
            $table->dropColumn([
                'extra_ram', 'extra_disk', 'extra_cpu',
                'extra_databases', 'extra_allocations', 'extra_backups',
            ]);
        });
    }
};
