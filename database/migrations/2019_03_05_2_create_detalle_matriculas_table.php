<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetalleMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_matriculas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('matricula_id');
            $table->foreign('matricula_id')->references('id')->on('matriculas')->onDelete('cascade');
            $table->integer('asignatura_id');
            $table->foreign('asignatura_id')->references('id')->on('asignaturas');
            $table->integer('tipo_matricula_id');
            $table->foreign('tipo_matricula_id')->references('id')->on('tipo_matriculas');
            $table->string('paralelo', 20)->nullable();
            $table->string('jornada', 20)->nullable();
            $table->string('numero_matricula', 20)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->unique(['matricula_id', 'asignatura_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_matriculas');
    }
}
