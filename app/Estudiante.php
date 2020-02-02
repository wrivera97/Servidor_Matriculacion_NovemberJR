<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Estudiante extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'anio_graduacion',
        'apellido1',
        'apellido2',
        'correo_institucional',
        'correo_personal',
        'corte',
        'estado',
        'etnia',
        'fecha_inicio_carrera',
        'fecha_nacimiento',
        'genero',
        'identificacion',
        'nombre1',
        'nombre2',
        'pueblo_nacionalidad',
        'sexo',
        'tipo_bachillerato',
        'tipo_colegio',
        'tipo_identificacion',
        'tipo_sangre',
    ];

    public function matriculas()
    {
        return $this->hasMany('App\Matricula');
    }

    public function canton_nacimiento()
    {
        return $this->belongsTo('App\Ubicacion');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
