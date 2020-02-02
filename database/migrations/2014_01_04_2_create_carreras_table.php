<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarrerasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('instituto_id');
            $table->foreign('instituto_id')->references('id')->on('institutos');
            $table->string('codigo', 50)->nullable();;
            $table->string('codigo_sniese', 50)->nullable();
            $table->string('nombre', 200);
            $table->string('descripcion', 200);
            $table->string('modalidad', 50);
            $table->string('numero_resolucion', 50)->nullable();
            $table->string('titulo_otorga', 200);
            $table->string('siglas', 50);
            $table->string('tipo_carrera', 50);
            $table->string('estado', 20)->default('ACTIVO');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carreras');
    }
}
