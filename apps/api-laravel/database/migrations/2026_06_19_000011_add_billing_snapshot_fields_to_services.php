<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->date('invoice_issue_date')->nullable()->after('billing_isolation_date');
            $table->decimal('dpp_amount', 14, 2)->default(0)->after('unit_code');
            $table->decimal('ppn_rate', 5, 2)->default(11)->after('dpp_amount');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_issue_date',
                'dpp_amount',
                'ppn_rate',
            ]);
        });
    }
};
