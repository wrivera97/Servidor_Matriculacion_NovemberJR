<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Malla extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'nombre', 'fecha_aprobacion', 'numero_resolucion', 'numero_semanas', 'fecha_finalizacion', 'estado',
    ];

    public function matriculas()
    {
        return $this->hasMany('App\Matricula');
    }

    public function carrera()
    {
        return $this->belongsTo('App\Carrera');
    }
}
