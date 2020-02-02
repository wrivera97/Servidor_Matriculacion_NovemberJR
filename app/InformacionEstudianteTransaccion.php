<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class InformacionEstudianteTransaccion extends Model
{
    protected $table = 'informacion_estudiantes';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'codigo_postal',
        'contacto_emergencia_telefono',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_nombres',
        'habla_idioma_ancestral',
        'idioma_ancestral',
        'categoria_migratoria',
        'posee_titulo_superior',
        'ha_repetido_asignatura',
        'ha_perdido_gratuidad',
        'ha_realizado_practicas',
        'horas_practicas',
        'sector_economico_practica',
        'tipo_institucion_practicas',
        'ha_realizado_vinculacion',
        'horas_vinculacion',
        'alcance_vinculacion',
        'ocupacion',
        'nombre_empresa_labora',
        'area_trabajo_empresa',
        'destino_ingreso',
        'recibe_bono_desarrollo',
        'nivel_formacion_padre',
        'nivel_formacion_madre',
        'ingreso_familiar',
        'numero_miembros_hogar',
        'numero_carnet_conadis',
        'tiene_discapacidad',
        'tipo_discapacidad',
        'porcentaje_discapacidad',
        'telefono_fijo',
        'telefono_celular',
        'direccion',
        'estado_civil',
        'pension_diferenciada',
        'tipo_beca',
        'razon_beca',
        'monto_beca',
        'porciento_beca_cobertura_arancel',
        'porciento_beca_cobertura_manutencion',
        'tipo_financiamiento_beca',
        'monto_ayuda_economica',
        'monto_credito_educativo',
        'estado',
    ];

    public function matricula()
    {
        return $this->belongsTo('App\MatriculaTransaccion');
    }

    public function canton_residencia()
    {
        return $this->belongsTo('App\Ubicacion');
    }
}
