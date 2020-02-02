<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TipoMatricula extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'nombre', 'estado',
    ];

    public function matriculas()
    {
        return $this->hasMany('App\Matricula');
    }

    public function detalle_matriculas()
    {
        return $this->hasMany('App\DetalleMatricula');
    }
}
