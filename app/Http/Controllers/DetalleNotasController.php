<?php

namespace App\Http\Controllers;

use App\DetalleMatricula;
use App\DetalleNota;
use App\DocenteAsignatura;
use App\Estudiante;
use Illuminate\Http\Request;


class DetalleNotasController extends Controller
{

  public function getDetalleEstudiantes(Request $request)
  {


      $estudiantesdetalle= DetalleMatricula:: distinct()->select('estudiante_id')
          ->join('matriculas','matriculas.id','detalle_matriculas.matricula_id')
          ->where('detalle_matriculas.estado','MATRICULADO')
          ->where('detalle_matriculas.asignatura_id',$request->asignatura_id)
          ->where('detalle_matriculas.paralelo',$request->paralelo)
          ->where('detalle_matriculas.jornada',$request->jornada)->with('estudiante')->orderby('estudiante_id')->get();
    return response()->json(['detalle_estudiante'=>$estudiantesdetalle],200);

}
    public function getdetalleNotas (Request $request){
      $notasdetalle=DetalleNota::where('docente_asignaturas_id',$request->docente_asignatura_id)
      ->where('detalle_matricula_id',$request->detalle_matricula_id)->get();

      if ($notasdetalle!==true) {
          return response()->json(['detalle_notas' => $notasdetalle], 200);
      }
      else
      {
          return response()->json('error',500);

    }
}

    public function createDetalleNotas(Request $request) {
        $data= $request->json()->all();
        $dataDetalleNotas=$data['detalle_nota'];
        $dataDocentAsignatura=$dataDetalleNotas['docente_asignatura'];
        $dataEstudiante=$dataDetalleNotas['estudiante'];
        $detalleNota=new DetalleNota([
            'nota1'=>$dataDetalleNotas  ['nota1'],
            'nota2'=>$dataDetalleNotas  ['nota2'],
            'nota_final'=>$dataDetalleNotas  ['nota_final'],
            'asistencia1'=>$dataDetalleNotas  ['asistencia1'],
            'asistencia2'=>$dataDetalleNotas  ['asistencia2'],
            'asistencia_final'=>$dataDetalleNotas  ['asistencia_final'],
            'estado_academico'=>$dataDetalleNotas  ['estado_academico']

        ]);
        $docenteAsignatura=DocenteAsignatura::findOrFail($dataDocentAsignatura['id']);
        $detalleNota->docente_asignatura()->associate($docenteAsignatura);

       $estudiante=Estudiante::findOrFail($dataEstudiante['id']);
        $detalleNota->estudiante()->associate($estudiante);

        $detalleNota->save();


        return response()->json(['ok'=>$detalleNota],200);
    }



}
