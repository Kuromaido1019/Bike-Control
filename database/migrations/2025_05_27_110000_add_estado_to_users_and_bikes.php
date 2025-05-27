<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoToUsersAndBikes extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('estado')->default('activo')->after('role');
        });
        Schema::table('bikes', function (Blueprint $table) {
            $table->string('estado')->default('activo')->after('color');
        });
    }
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
        Schema::table('bikes', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
}
