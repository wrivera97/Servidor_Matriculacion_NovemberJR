<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('malla_id');
            $table->foreign('malla_id')->references('id')->on('mallas');
            $table->integer('estudiante_id');
            $table->foreign('estudiante_id')->references('id')->on('estudiantes');
            $table->integer('periodo_lectivo_id');
            $table->foreign('periodo_lectivo_id')->references('id')->on('periodo_lectivos');
            $table->integer('periodo_academico_id');
            $table->foreign('periodo_academico_id')->references('id')->on('periodo_academicos');
            $table->integer('tipo_matricula_id');
            $table->foreign('tipo_matricula_id')->references('id')->on('tipo_matriculas');
            $table->string('codigo', 50)->nullable();
            $table->dateTime('fecha')->nullable();
            $table->dateTime('fecha_solicitud')->nullable();
            $table->dateTime('fecha_formulario')->nullable();
            $table->string('folio', 50)->nullable();
            $table->string('jornada', 50)->nullable();
            $table->string('jornada_operativa', 50)->nullable();
            $table->string('paralelo_principal', 50)->nullable();
            $table->string('codigo_sniese_paralelo', 50)->nullable();
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
        Schema::dropIfExists('matriculas');
    }
}
