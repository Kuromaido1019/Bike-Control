<?php
// database/migrations/2025_05_27_000001_create_access_modifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessModificationsTable extends Migration
{
    public function up()
    {
        Schema::create('access_modifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('access_id');
            $table->string('accion'); // 'editado' o 'eliminado'
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->unsignedBigInteger('editado_por');
            $table->timestamp('fecha_edicion');
            $table->timestamps();

            $table->foreign('access_id')->references('id')->on('accesses')->onDelete('cascade');
            $table->foreign('editado_por')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_modifications');
    }
};
