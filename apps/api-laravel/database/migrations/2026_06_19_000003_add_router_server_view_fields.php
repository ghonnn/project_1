<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->string('connection_type')->default('ip_public')->after('router_role');
            $table->string('radius_secret')->nullable()->after('connection_type');
            $table->unsignedInteger('online_sessions')->default(0)->after('radius_secret');
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['connection_type', 'radius_secret', 'online_sessions']);
        });
    }
};
