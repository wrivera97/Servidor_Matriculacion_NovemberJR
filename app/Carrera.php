<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Carrera extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'codigo', 'codigo_sniese', 'nombre', 'descripcion', 'numero_resolucion', 'titulo_otorga', 'siglas',
        'tipo_carrera', 'estado',
    ];

    public function mallas()
    {
        return $this->hasMany('App\Malla');
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function matriculas()
    {
        return $this->hasManyThrough('App\Matricucla', 'App\Malla');
    }
}
