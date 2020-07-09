<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetalleNota extends Model
{
    protected $fillable = [
        'nota1',
        'nota2',
        'nota_final',
        'asistencia1',
        'asistencia2',
        'asistencia_final',
        'estado_academico'
    ];
    public function docente_asignatura()
    {
        return $this->belongsTo('App\DocenteAsignatura');
    }
    public function estudiante()
    {
        return $this->belongsTo('App\Estudiante');
    }
}
