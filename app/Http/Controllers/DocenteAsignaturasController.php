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

$docente=Docente::where('id',$request->id)->first();
$docenteAsignaturas = DocenteAsignatura::where('docente_id',$docente->id)->where('periodo_lectivo_id',$request->periodo_lectivo_id)
->with('asignatura')->orderby('id')->get();

    if($docente){

     return response()->json(['asignacionesDocente' => $docenteAsignaturas], 200);
     }
     return response()->json('error',500);
    }
 public function getDocentesAsignados(Request $request)
 {

     $DocenteAsignados = DocenteAsignatura::distinct()
     ->with('docente')
     ->get(['docente_id']);
         return response()->json(['docentesAsignados'=>$DocenteAsignados],200);
     }

    public function AsignarDocentesAsignaturas(Request $request)
    {
    $data = $request->json()->all();
    $dataDocenteAsignaturas = $data['docente_asignatura'];
    $dataDocente = $dataDocenteAsignaturas['docente'];
    $dataPeriodoLectivo = $dataDocenteAsignaturas['periodo_lectivo'];
    $dataAsignatura = $dataDocenteAsignaturas['asignatura'];

    $dataAll=[$dataDocenteAsignaturas,$dataDocente,$dataPeriodoLectivo ,$dataAsignatura];

    $docenteasignaturas = new DocenteAsignatura([
    'paralelo' => $dataDocenteAsignaturas['paralelo'],
     'jornada' => $dataDocenteAsignaturas['jornada']
    ]);

       $docente = Docente::findOrFail($dataDocente['id']);
       $docenteasignaturas->docente()->associate($docente);

       $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodoLectivo['id']);
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
    $dataDocenteAsignaturas = $data['docente_asignatura'];
    //$dataDocente = $dataDocenteAsignaturas['docente_id'];
    //$dataPeriodoLectivo = $data['periodo_lectivo'];
    // $dataAsignatura = $dataDocenteAsignaturas['asignatura'];
    // these variables make an update error
    //$dataAll=[$dataDocenteAsignaturas,$dataDocente,$dataPeriodo ,$dataAsignatura];

    $docenteAsignatura = DocenteAsignatura::findOrFail($dataDocenteAsignaturas['id']);

    $docenteAsignatura->update([

    'paralelo' => $dataDocenteAsignaturas['paralelo'],
     'jornada' => $dataDocenteAsignaturas['jornada']
    ]);
    $docente = Docente::findOrFail($dataDocenteAsignaturas['docente_id']);
    $docenteAsignatura->docente()->associate($docente);

    $periodolectivo = PeriodoLectivo::findOrFail($dataDocenteAsignaturas['periodo_lectivo_id']);
  $docenteAsignatura->periodoLectivo()->associate($periodolectivo);

    $asignatura = Asignatura::findOrFail($dataDocenteAsignaturas['asignatura']['id']);
    $docenteAsignatura->asignatura()->associate($asignatura);

    $docenteAsignatura->save();

DB::commit();

if($docenteAsignatura){

    return response()->json(['succesfull'=>$docenteAsignatura],200);

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


