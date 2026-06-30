<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hotspot_vouchers')) {
            return;
        }

        if (! Schema::hasColumn('hotspot_vouchers', 'mitra_id')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table): void {
                $table->uuid('mitra_id')->nullable()->after('outlet_id');
                $table->foreign('mitra_id')->references('id')->on('mitras')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('hotspot_vouchers', 'admin_user_id')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table): void {
                $table->uuid('admin_user_id')->nullable()->after('mitra_id');
                $table->foreign('admin_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('hotspot_vouchers', 'balance_deducted')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table): void {
                $table->boolean('balance_deducted')->default(false)->after('admin_user_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('hotspot_vouchers')) {
            return;
        }

        if (Schema::hasColumn('hotspot_vouchers', 'balance_deducted')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table): void {
                $table->dropColumn('balance_deducted');
            });
        }

        if (Schema::hasColumn('hotspot_vouchers', 'admin_user_id')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table): void {
                $table->dropForeign(['admin_user_id']);
                $table->dropColumn('admin_user_id');
            });
        }

        if (Schema::hasColumn('hotspot_vouchers', 'mitra_id')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table): void {
                $table->dropForeign(['mitra_id']);
                $table->dropColumn('mitra_id');
            });
        }
    }
};
