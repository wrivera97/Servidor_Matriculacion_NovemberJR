<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsignaturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asignaturas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('malla_id');
            $table->foreign('malla_id')->references('id')->on('mallas');
            $table->integer('periodo_academico_id');
            $table->foreign('periodo_academico_id')->references('id')->on('periodo_academicos');
            $table->integer('unidad_curricular_id')->nullable();
            $table->foreign('unidad_curricular_id')->references('id')->on('unidad_curriculares')->nullable();
            $table->integer('campo_formacion_id')->nullable();
            $table->foreign('campo_formacion_id')->references('id')->on('campo_formaciones')->nullable();
            $table->integer('codigo_padre_prerequisito')->nullable();
            $table->foreign('codigo_padre_prerequisito')->references('id')->on('asignaturas')->nullable();
            $table->integer('codigo_padre_corequisito')->nullable();
            $table->foreign('codigo_padre_corequisito')->references('id')->on('asignaturas')->nullable();
            $table->string('codigo', 100);
            $table->string('nombre', 200);
            $table->integer('horas_practica');
            $table->integer('horas_docente');
            $table->integer('horas_autonoma');
            $table->string('tipo', 50);
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
        Schema::dropIfExists('asignaturas');
    }
}
