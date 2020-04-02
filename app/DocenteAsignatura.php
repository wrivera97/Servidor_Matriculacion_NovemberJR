<?php
use App\DocenteAsignatura;
use App\Asignatura;
use App\Docente;
use App\PeriodoLectivo;
use App\Carrera;
use App\Malla;
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocenteAsignatura extends Model
{
    protected $fillable =[
    'paralelo',
    'jornada'
    ];



public function docente(){
    return $this->belongsTo('App\Docente') ;
}

public function  asignatura(){
    return $this->belongsTo('App\Asignatura') ;
}

public function  periodoLectivo(){
    return $this->belongsTo('App\PeriodoLectivo');
}
public function  carrera(){
    return $this->belongsTo('App\Carrera');
}
public function  malla(){
    return $this->belongsTo('App\Malla');
}
public function  periodo_academico(){
    return $this->belongsTo('App\PeriodoAcademico');
}

}
