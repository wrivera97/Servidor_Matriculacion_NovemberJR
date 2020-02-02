<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocenteAsignaturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docente_asignaturas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
                    $table->integer('asignatura_id');
                    $table->foreign('asignatura_id')->references('id')->on('asignaturas');

                    $table->integer('docente_id');
                    $table->foreign('docente_id')->references('id')->on('docentes');

                    $table->integer('periodo_lectivo_id');
                    $table->foreign('periodo_lectivo_id')->references('id')->on('periodo_lectivos');

                    $table->String('paralelo')->nullable();
                    $table->String('jornada')->nullable();
                });
            }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docente_asignaturas');
    }
}
