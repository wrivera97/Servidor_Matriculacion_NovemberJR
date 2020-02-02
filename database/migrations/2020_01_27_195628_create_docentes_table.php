<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // incluir foreing keys con sus referencias
        Schema::create('docentes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullable();



            $table->string('nombre1');
            $table->string('nombre2');
            $table->string('apellido1',50);
            $table->string('apellido2',50);
            $table->string('tipo_identificacion',40);
            $table->string('identificacion',50);
            $table->string('genero',50);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('correo_personal',100)->nullable();
            $table->string('correo_institucional',100)->nullable();
            //$table->string('discapacidad',50)->default("NO");
            $table->string('tipo_sangre', 50)->nullable();
            $table->string('direccion',200);
            $table->string('etnia',50);
            //$table->string('pueblo_nacionalidad',50);
            $table->string('estado',50)->default("ACTIVO");


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docentes');
    }
}
