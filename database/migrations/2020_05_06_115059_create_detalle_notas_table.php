<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleNotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_notas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('estudiante_id');
            $table->foreign('estudiante_id')->references('id')->on('estudiantes');

            $table->integer('docente_asignatura_id');
            $table->foreign('docente_asignatura_id')->references('id')->on('docente_asignaturas');

            $table->decimal('nota1', 5, 2)->nullable();
            $table->decimal('nota2', 5, 2)->nullable();
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->decimal('asistencia1', 5, 2)->nullable();
            $table->decimal('asistencia2', 5, 2)->nullable();
            $table->decimal('asistencia_final', 5, 2)->nullable();
            $table->string('estado_academico', 20)->nullable();
            $table->unique(['estudiante_id', 'docente_asignatura_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_notas');
    }
}
