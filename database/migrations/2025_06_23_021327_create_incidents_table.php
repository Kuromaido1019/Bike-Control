<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guard_id'); // ID del guardia
            $table->string('rut', 20); // RUT de la persona que realiza el reclamo
            $table->string('categoria', 50); // Categoría del reclamo/incidente
            $table->text('detalle'); // Detalle del reclamo
            $table->timestamps();

            $table->foreign('guard_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
