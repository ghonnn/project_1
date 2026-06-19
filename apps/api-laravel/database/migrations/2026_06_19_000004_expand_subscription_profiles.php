<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('mikrotik_group')->default('RLRADIUS')->after('name');
            $table->string('mikrotik_rate_limit')->nullable()->after('mikrotik_group');
            $table->unsignedInteger('shared_users')->default(1)->after('mikrotik_rate_limit');
            $table->unsignedInteger('active_days')->default(30)->after('shared_users');
            $table->decimal('hpp', 14, 2)->default(0)->after('active_days');
            $table->decimal('commission', 14, 2)->default(0)->after('hpp');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'mikrotik_group',
                'mikrotik_rate_limit',
                'shared_users',
                'active_days',
                'hpp',
                'commission',
            ]);
        });
    }
};
