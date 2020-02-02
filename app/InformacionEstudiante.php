<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InformacionEstudiante extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected $fillable = [
        'alcance_vinculacion',
        'area_trabajo_empresa',
        'categoria_migratoria',
        'codigo_postal',
        'contacto_emergencia_nombres',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_telefono',
        'destino_ingreso',
        'direccion',
        'estado',
        'estado_civil',
        'ha_perdido_gratuidad',
        'ha_realizado_practicas',
        'ha_realizado_vinculacion',
        'ha_repetido_asignatura',
        'habla_idioma_ancestral',
        'horas_practicas',
        'horas_vinculacion',
        'idioma_ancestral',
        'ingreso_familiar',
        'monto_ayuda_economica',
        'monto_beca',
        'monto_credito_educativo',
        'nivel_formacion_madre',
        'nivel_formacion_padre',
        'nombre_empresa_labora',
        'numero_carnet_conadis',
        'numero_miembros_hogar',
        'ocupacion',
        'pension_diferenciada',
        'porcentaje_discapacidad',
        'porciento_beca_cobertura_arancel',
        'porciento_beca_cobertura_manutencion',
        'posee_titulo_superior',
        'razon_beca',
        'recibe_bono_desarrollo',
        'sector_economico_practica',
        'telefono_celular',
        'telefono_fijo',
        'tiene_discapacidad',
        'tipo_beca',
        'tipo_discapacidad',
        'tipo_financiamiento_beca',
        'tipo_institucion_practicas',
        'titulo_superior_obtenido',
    ];

    public function matricula()
    {
        return $this->belongsTo('App\Matricula');
    }

    public function canton_residencia()
    {
        return $this->belongsTo('App\Ubicacion');
    }
}
