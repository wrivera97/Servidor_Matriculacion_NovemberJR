<?php

namespace App\Http\Controllers;

use App\DetalleMatricula;
use App\Matricula;
use App\User;
use Illuminate\Http\Request;

class DetalleNotasController extends Controller
{

  public function getDetalleEstudiantes(Request $request)
  {

    $estudiantesdetalle=DetalleMatricula::distinct()->select('*')
    ->join('matriculas','matriculas.id','detalle_matriculas.matricula_id')
    ->join('estudiantes','estudiantes.id','matriculas.estudiante_id')
    ->where('asignatura_id',$request->asignatura_id)
    ->where('detalle_matriculas.paralelo',$request->paralelo)->where('detalle_matriculas.jornada',$request->jornada)
    ->get(['id']);


return response()->json(['ok'=>$estudiantesdetalle],200);{

}

}

}