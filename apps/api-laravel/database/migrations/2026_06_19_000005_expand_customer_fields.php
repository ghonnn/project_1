<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_number')->nullable()->after('id');
            $table->text('address')->nullable()->after('phone');
            $table->string('identity_number')->nullable()->after('address');
            $table->string('tax_number')->nullable()->after('identity_number');
            $table->decimal('balance', 14, 2)->default(0)->after('tax_number');
            $table->string('partner_name')->nullable()->after('balance');
            $table->string('client_area_url')->nullable()->after('partner_name');
            $table->unique(['tenant_id', 'customer_number']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'customer_number']);
            $table->dropColumn([
                'customer_number',
                'address',
                'identity_number',
                'tax_number',
                'balance',
                'partner_name',
                'client_area_url',
            ]);
        });
    }
};
