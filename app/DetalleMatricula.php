<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DetalleMatricula extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'numero_matricula', 'paralelo', 'jornada', 'nota', 'asistencia', 'estado_acadedmico', 'estado',
        'estado_academico',
        'nota1',
        'nota2',
        'nota_final',
        'asistencia1',
        'asistencia2',
        'asistencia_final',
    ];

    public function matricula()
    {
        return $this->belongsTo('App\Matricula');
    }

    public function tipo_matricula()
    {
        return $this->belongsTo('App\TipoMatricula');
    }

    public function asignatura()
    {
        return $this->belongsTo('App\Asignatura');
    }

}
