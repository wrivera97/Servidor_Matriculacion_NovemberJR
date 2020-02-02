<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Matricula extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'codigo',
        'codigo_sniese_paralelo',
        'folio', 'fecha', 'fecha_formulario', 'fecha_solicitud',
        'jornada','jornada_operativa', 'paralelo_principal', 'estado',
    ];


    public function tipo_matricula()
    {
        return $this->belongsTo('App\TipoMatricula');
    }

    public function detalle_matriculas()
    {
        return $this->hasMany('App\DetalleMatricula');
    }

    public function informacion_estudiantes()
    {
        return $this->hasMany('App\InformacionEstudiante');
    }

    public function estudiante()
    {
        return $this->belongsTo('App\Estudiante');
    }

    public function periodo_lectivo()
    {
        return $this->belongsTo('App\PeriodoLectivo');
    }

    public function periodo_academico()
    {
        return $this->belongsTo('App\PeriodoAcademico')->orderBy('nombre');
    }

    public function malla()
    {
        return $this->belongsTo('App\PeriodoAcademico');
    }


}
