<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rut')->nullable()->after('email');
        });
        // Migrar datos de rut desde profiles a users
        DB::statement('UPDATE users u JOIN profiles p ON u.id = p.user_id SET u.rut = p.rut WHERE p.rut IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rut');
        });
    }
};
