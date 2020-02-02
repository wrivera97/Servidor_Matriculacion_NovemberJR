<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Asignatura extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'codigo',
        'nombre',
        'horas_practica',
        'horas_docente',
        'horas_autonoma',
        'tipo',
        'estado',
    ];

    public function detalle_matriculas()
    {
        return $this->hasMany('App\DetalleMatricula');
    }

    public function periodo_academico()
    {
        return $this->belongsTo('App\PeriodoAcademico');
    }
}
