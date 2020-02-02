<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Ubicacion extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'ubicaciones';
    protected $fillable = [
        'codigo',
        'nombre',
        'codigo_padre',
        'tipo',
        'estado',
    ];

    public function informacion_estudiantes()
    {
        return $this->hasMany('App\InformacionEstudiante');
    }

    public function estudiantes()
    {
        return $this->hasMany('App\Estudiante');
    }

}
