<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Instituto extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'codigo', 'codigo_sniese', 'nombre', 'estado',
    ];

    public function carreras()
    {
        return $this->hasMany('App\Carrera');
    }
}
