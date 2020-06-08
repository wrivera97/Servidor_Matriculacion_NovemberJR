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

            $table->integer('detalle_matricula_id');
            $table->foreign('detalle_matricula_id')->references('id')->on('detalle_matriculas');

            $table->integer('docente_asignatura_id');
            $table->foreign('docente_asignatura_id')->references('id')->on('docente_asignaturas');

            $table->decimal('nota1',5);
            $table->decimal('nota2',5);
            $table->decimal('nota_final',5);
            $table->decimal('asistencia1',5);
            $table->decimal('asistencia2',5);
            $table->decimal('asistencia_final',5);
            $table->string('estado_academico',20)->default('PENDIENTE');
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
