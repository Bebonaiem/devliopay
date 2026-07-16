<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable()->after('email');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('country', 2)->nullable()->after('state');
            $table->string('zip_code')->nullable()->after('country');
            $table->string('phone')->nullable()->after('zip_code');
            $table->string('company')->nullable()->after('phone');
            $table->decimal('balance', 10, 2)->default(0)->after('company');
            $table->boolean('is_admin')->default(false)->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'city', 'state', 'country', 'zip_code',
                'phone', 'company', 'balance', 'is_admin',
            ]);
        });
    }
};
