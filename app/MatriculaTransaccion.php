<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatriculaTransaccion extends Model
{

    protected $table = 'matriculas';
    protected $fillable = [
        'codigo', 'codigo_sniese_paralelo', 'folio', 'fecha', 'jornada', 'jornada_operativa', 'paralelo_principal', 'estado',
    ];

    public function tipo_matricula()
    {
        return $this->belongsTo('App\TipoMatricula');
    }

    public function detalle_matriculas()
    {
        return $this->hasMany('App\DetalleMatriculaTransaccion');
    }

    public function informacion_estudiantes()
    {
        return $this->hasOne('App\InformacionEstudianteTransaccion', 'matricula_id');
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
