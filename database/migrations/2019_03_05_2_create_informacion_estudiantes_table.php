<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformacionEstudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informacion_estudiantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('matricula_id');
            $table->foreign('matricula_id')->references('id')->on('matriculas')->onDelete('cascade');
            $table->integer('canton_residencia_id')->default(0);
            $table->foreign('canton_residencia_id')->references('id')->on('ubicaciones')->nullable();
            $table->double('ingreso_familiar', 8, 2)->nullable();
            $table->double('monto_ayuda_economica', 8, 2)->nullable();
            $table->double('monto_beca', 8, 2)->nullable();
            $table->double('monto_credito_educativo', 8, 2)->nullable();
            $table->double('porcentaje_discapacidad', 8, 2)->nullable();
            $table->double('porciento_beca_cobertura_arancel', 8, 2)->nullable();
            $table->double('porciento_beca_cobertura_manutencion', 8, 2)->nullable();
            $table->integer('horas_practicas')->nullable();
            $table->integer('horas_vinculacion')->nullable();
            $table->integer('numero_miembros_hogar')->nullable();
            $table->string('alcance_vinculacion', 50)->nullable();
            $table->string('area_trabajo_empresa', 100)->nullable();
            $table->string('categoria_migratoria')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('contacto_emergencia_nombres')->nullable();
            $table->string('contacto_emergencia_parentesco')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();
            $table->string('destino_ingreso', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('estado', 20)->default('ACTIVO');
            $table->string('estado_civil', 20)->default('0');
            $table->string('ha_perdido_gratuidad', 10)->default('0');
            $table->string('ha_realizado_practicas', 10)->default('0');
            $table->string('ha_realizado_vinculacion', 10)->default('0');
            $table->string('ha_repetido_asignatura', 10)->default('0');
            $table->string('habla_idioma_ancestral')->default('0');
            $table->string('idioma_ancestral')->default('');
            $table->string('nivel_formacion_madre', 100)->nullable();
            $table->string('nivel_formacion_padre', 100)->nullable();
            $table->string('nombre_empresa_labora', 100)->nullable();
            $table->string('numero_carnet_conadis', 50)->nullable();
            $table->string('ocupacion', 50)->nullable();
            $table->string('pension_diferenciada', 10)->nullable();
            $table->string('posee_titulo_superior')->default('0');
            $table->string('razon_beca1', 200)->default('2');
            $table->string('razon_beca2', 200)->default('2');
            $table->string('razon_beca3', 200)->default('2');
            $table->string('razon_beca4', 200)->default('2');
            $table->string('razon_beca5', 200)->default('2');
            $table->string('razon_beca6', 200)->default('2');
            $table->string('recibe_bono_desarrollo', 10)->nullable();
            $table->string('sector_economico_practica', 100)->default('22');
            $table->string('telefono_celular', 20)->nullable();
            $table->string('telefono_fijo', 20)->nullable();
            $table->string('tiene_discapacidad', 10)->default('0');
            $table->string('tipo_beca', 50)->nullable();
            $table->string('tipo_discapacidad', 100)->default('7');
            $table->string('tipo_financiamiento_beca', 50)->default('4');
            $table->string('tipo_institucion_practicas', 100)->nullable();
            $table->string('titulo_superior_obtenido')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informacion_estudiantes');
    }
}
