<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotspot_templates') && !Schema::hasColumn('hotspot_templates', 'logo_path')) {
            Schema::table('hotspot_templates', function (Blueprint $table) {
                $table->string('logo_path')->nullable()->after('support_phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotspot_templates') && Schema::hasColumn('hotspot_templates', 'logo_path')) {
            Schema::table('hotspot_templates', function (Blueprint $table) {
                $table->dropColumn('logo_path');
            });
        }
    }
};
