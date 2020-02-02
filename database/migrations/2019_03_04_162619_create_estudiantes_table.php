<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullable();
            $table->integer('canton_nacimiento_id')->default(0);
            $table->foreign('canton_nacimiento_id')->references('id')->on('ubicaciones');
            $table->string('tipo_identificacion', 50)->default(0);
            $table->string('identificacion', 50);
            $table->string('apellido1', 50);
            $table->string('apellido2', 50)->default('NA');
            $table->string('nombre1', 50);
            $table->string('nombre2', 50)->default('NA');
            $table->string('sexo', 50)->default(0);
            $table->string('genero', 50)->default(0);
            $table->string('etnia', 50)->default(0);
            $table->string('pueblo_nacionalidad', 50)->default(34);
            $table->string('tipo_sangre', 50)->default(0);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('tipo_colegio', 50)->default(0);
            $table->date('fecha_inicio_carrera')->nullable();
            $table->string('correo_personal', 100)->nullable();
            $table->string('correo_institucional', 100)->nullable();
            $table->string('tipo_bachillerato', 50)->default(0);
            $table->string('anio_graduacion')->nullable();
            $table->string('corte')->nullable();
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
        Schema::dropIfExists('estudiantes');
    }
}
