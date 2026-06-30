<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('radpostauth')) {
            Schema::create('radpostauth', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->default('');
                $table->string('pass', 64)->nullable();
                $table->string('reply', 32)->nullable();
                $table->timestamp('authdate')->useCurrent();
                $table->index(['username', 'authdate']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('radpostauth');
    }
};
