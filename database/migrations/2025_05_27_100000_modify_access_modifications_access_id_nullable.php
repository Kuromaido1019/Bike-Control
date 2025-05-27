<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAccessModificationsAccessIdNullable extends Migration
{
    public function up()
    {
        Schema::table('access_modifications', function (Blueprint $table) {
            $table->dropForeign(['access_id']);
            $table->unsignedBigInteger('access_id')->nullable()->change();
            $table->foreign('access_id')->references('id')->on('accesses')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::table('access_modifications', function (Blueprint $table) {
            $table->dropForeign(['access_id']);
            $table->unsignedBigInteger('access_id')->nullable(false)->change();
            $table->foreign('access_id')->references('id')->on('accesses')->onDelete('cascade');
        });
    }
}
