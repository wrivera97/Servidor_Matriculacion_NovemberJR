<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PeriodoAcademico extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'nombre', 'estado',
    ];

    public function matriculas()
    {
        return $this->hasMany('App\Matricula');
    }

    public function asignaturas()
    {
        return $this->hasMany('App\Asignatura');
    }
}
