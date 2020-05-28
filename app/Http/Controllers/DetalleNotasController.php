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

      $estudiantesdetalle= DetalleMatricula:: distinct()->select('estudiante_id')
          ->join('matriculas','matriculas.id','detalle_matriculas.matricula_id')
          ->where('detalle_matriculas.asignatura_id',$request->asignatura_id)
          ->where('detalle_matriculas.paralelo',$request->paralelo)
          ->where('detalle_matriculas.jornada',$request->jornada) ->with('estudiante')->orderby('estudiante_id')->get();


return response()->json(['ok'=>$estudiantesdetalle],200);{

}

}

}
