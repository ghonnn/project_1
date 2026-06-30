<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mitras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('outlet_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('commission_type')->default('nominal');
            $table->decimal('commission_value', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
