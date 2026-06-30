<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('nas')) {
            Schema::create('nas', function (Blueprint $table) {
                $table->id();
                $table->string('nasname', 128)->unique();
                $table->string('shortname', 32);
                $table->string('type', 30)->default('other');
                $table->integer('ports')->nullable();
                $table->string('secret', 60);
                $table->string('server', 64)->nullable();
                $table->string('community', 50)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('radcheck')) {
            Schema::create('radcheck', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->default('');
                $table->string('attribute', 64)->default('');
                $table->string('op', 2)->default('==');
                $table->string('value', 253)->default('');
                $table->index(['username', 'attribute']);
            });
        }

        if (! Schema::hasTable('radreply')) {
            Schema::create('radreply', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->default('');
                $table->string('attribute', 64)->default('');
                $table->string('op', 2)->default('=');
                $table->string('value', 253)->default('');
                $table->index(['username', 'attribute']);
            });
        }

        if (! Schema::hasTable('radusergroup')) {
            Schema::create('radusergroup', function (Blueprint $table) {
                $table->id();
                $table->string('username', 64)->default('');
                $table->string('groupname', 64)->default('');
                $table->integer('priority')->default(1);
                $table->index('username');
            });
        }

        if (! Schema::hasTable('radgroupcheck')) {
            Schema::create('radgroupcheck', function (Blueprint $table) {
                $table->id();
                $table->string('groupname', 64)->default('');
                $table->string('attribute', 64)->default('');
                $table->string('op', 2)->default('==');
                $table->string('value', 253)->default('');
                $table->index(['groupname', 'attribute']);
            });
        }

        if (! Schema::hasTable('radgroupreply')) {
            Schema::create('radgroupreply', function (Blueprint $table) {
                $table->id();
                $table->string('groupname', 64)->default('');
                $table->string('attribute', 64)->default('');
                $table->string('op', 2)->default('=');
                $table->string('value', 253)->default('');
                $table->index(['groupname', 'attribute']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('radgroupreply');
        Schema::dropIfExists('radgroupcheck');
        Schema::dropIfExists('radusergroup');
        Schema::dropIfExists('radreply');
        Schema::dropIfExists('radcheck');
        Schema::dropIfExists('nas');
    }
};
