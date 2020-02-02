<?php
use App\DocenteAsignatura;
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocenteAsignatura extends Model
{
    protected $fillable =[
    'parelelo',
    'jornada'
    ];



public function docente(){
    return $this->belongsTo('App/Docente') ;
}

public function  asginatura(){
    return $this->belongsTo('App/Asignatura') ;
}

public function  periodolectivo(){
    return $this->belongsTo('App/PeriodoLectivo');
}

}
