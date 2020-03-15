<?php

namespace App\Http\Controllers;
use App\DocenteAsignatura;
use App\PeriodoLectivo;
use App\Docente;
use App\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DocenteAsignaturasController extends Controller
{
    public function getDocenteAsignatura( Request $request){

$docente=Docente::where('identificacion',$request->identificacion)->first();

    $docenteAsignaturas = DocenteAsignatura::where('docente_id',$docente->id)->where('periodo_lectivo_id',$request->periodo_lectivo_id)->with('asignatura') ->get();

    if($docente){
     return response()->json(['asignacionesDocente' => $docenteAsignaturas], 200);
     }
     return response()->json('error',500);
    }
// testasignaciondocentes
 public function gettest(Request $request) {

      $docenteAsignaturas = DocenteAsignatura::get();



   return response()->json(['test' => $docenteAsignaturas], 200);

 }

    public function AsignarDocentesAsignaturas(Request $request)
    {
    $data = $request->json()->all();
    $dataDocenteAsignaturas = $data['docenteasignatura'];
    $dataDocente = $data['docente'];
    $dataPeriodo = $data['periodo'];
    $dataAsignatura = $data['asignatura'];

    $dataAll=[$dataDocenteAsignaturas,$dataDocente,$dataPeriodo ,$dataAsignatura];

    $docenteasignaturas = new DocenteAsignatura([
    'paralelo' => $dataDocenteAsignaturas['paralelo'],
     'jornada' => $dataDocenteAsignaturas['jornada']
    ]);

       $docente = Docente::findOrFail($dataDocente['id']);
       $docenteasignaturas->docente()->associate($docente);

       $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodo['id']);
       $docenteasignaturas->periodoLectivo()->associate($periodolectivo);

       $asignatura = Asignatura::findOrFail($dataAsignatura['id']);
       $docenteasignaturas->asignatura()->associate($asignatura);
       $docenteasignaturas->save();

 if($dataAll){
        return response()->json([''=>$dataAll],200);

}

            return response()->json('error',500) ;
}

public function updateAsignaturaDocente(Request $request){

    try{
    DB::beginTransaction();
    $data=$request->json()->all();
    $dataDocenteAsignaturas = $data['docenteasignatura'];
    $dataDocente = $data['docente'];
    $dataPeriodo = $data['periodo'];
    $dataAsignatura = $data['asignatura'];

    $dataAll=[$dataDocenteAsignaturas,$dataDocente,$dataPeriodo ,$dataAsignatura];

    $docenteAsignatura = DocenteAsignatura::findOrFail($dataDocenteAsignaturas['id']);

    $docenteAsignatura->update([

    'paralelo' => $dataDocenteAsignaturas['paralelo'],
     'jornada' => $dataDocenteAsignaturas['jornada']
    ]);
    $docente = Docente::findOrFail($dataDocente['id']);
    $docenteAsignatura->docente()->associate($docente);

    $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodo['id']);
    $docenteAsignatura->periodoLectivo()->associate($periodolectivo);

    $asignatura = Asignatura::findOrFail($dataAsignatura['id']);
    $docenteAsignatura->asignatura()->associate($asignatura);

    $docenteAsignatura->save();

DB::commit();

if($dataAll){

    return response()->json(['succesfull'=>$dataAll],200);

}

        return response()->json('error',500) ;
        //cierra el try
    }

    catch (Exception $e) {
        return response()->json($e, 500);
    }

}
  public function deleteAsignacionDocente( Request $request){

    DB::beginTransaction();
    $DocenteAsigntura = DocenteAsignatura::findOrFail($request->id)
    ->delete();
    DB::commit();
    if($DocenteAsigntura){
    return response()->json(['Succesfull',$DocenteAsigntura], 201);
    }
    return response()->json('error', 500);
}
}


