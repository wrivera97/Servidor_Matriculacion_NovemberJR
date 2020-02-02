<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class DetalleMatriculaTransaccion extends Model
{
    protected $table = 'detalle_matriculas';
    protected $fillable = [
        'numero_matricula', 'paralelo', 'jornada', 'nota', 'asistencia', 'estado_acadedmico', 'estado',
        'nota1',
        'nota2',
        'nota_final',
        'asistencia1',
        'asistencia2',
        'asistencia_final',
        'estado_academico',
    ];

    public function matricula()
    {
        return $this->belongsTo('App\MatriculaTransaccion');
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
