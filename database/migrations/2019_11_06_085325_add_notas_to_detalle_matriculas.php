<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotasToDetalleMatriculas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('detalle_matriculas', function (Blueprint $table) {
            $table->decimal('nota1', 5,2)->nullable()->after('numero_matricula');
            $table->decimal('nota2', 5,2)->nullable()->after('numero_matricula');
            $table->decimal('nota_final', 5,2)->nullable()->after('numero_matricula');
            $table->decimal('asistencia1', 5,2)->nullable()->after('numero_matricula');;
            $table->decimal('asistencia2', 5,2)->nullable()->after('numero_matricula');
            $table->decimal('asistencia_final', 5,2)->nullable()->after('numero_matricula');
            $table->string('estado_academico', 20)->nullable()->after('numero_matricula');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detalle_matriculas', function (Blueprint $table) {
            $table->dropColumn('nota1');
            $table->dropColumn('nota2');
            $table->dropColumn('nota_final');
            $table->dropColumn('asistencia1');
            $table->dropColumn('asistencia2');
            $table->dropColumn('asistencia_final');
            $table->dropColumn('estado_academico');
        });
    }
}
