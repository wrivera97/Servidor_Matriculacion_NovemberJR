<?php
use App\DocenteAsignatura;
use App\Asignatura;
use App\Docente;
use App\PeriodoLectivo;
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

}
