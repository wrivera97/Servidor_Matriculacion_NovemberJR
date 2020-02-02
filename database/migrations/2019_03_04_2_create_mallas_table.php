<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mallas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('carrera_id');
            $table->foreign('carrera_id')->references('id')->on('carreras');
            $table->string('nombre', 50)->nullable();
            $table->date('fecha_aprobacion');
            $table->date('fecha_finalizacion')->nullable();
            $table->string('numero_resolucion', 50)->nullable();
            $table->integer('numero_semanas')->nullable();
            $table->string('estado', 20)->default('ACTIVO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mallas');
    }
}
