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
        /*$docenteAsignatura = DocenteAsignatura::select('id as id_asignacion','jornada','paralelo','docente_id','asignatura_id')
          ->with('docente')->where('docente_id',$request->docente_id)->get();
        return response()->json($docenteAsignatura, 200);*/



     //$test=DocenteAsignatura::with('docente');
     //$test1=$test->where('docente_id','1')->get();
     //return response()->json($test1, 200);

     $test0 = DocenteAsignatura::select('docente_asignaturas.id as #asignacion','jornada','docentes.id as docente_id',
                                        'paralelo','identificacion','asignatura_id')
                                 ->join('docentes', 'docente_asignaturas.docente_id', 'docentes.id');

     $test1=$test0->where('identificacion',$request->cedula)->get();
if($test1){
     return response()->json(['asignacionesDocente' => $test1], 200);
     }
     return response()->json('error',500);
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

public function updateasignacionDocentes(Request $request){
    $data = $request->json()->all();
    $dataDocenteAsignaturas = $data['docenteasignatura'];
    $dataDocente = $data['docente'];
    $dataPeriodo = $data['periodo'];
    $dataAsignatura = $data['asignatura'];

    $dataAll=[$dataDocenteAsignaturas,$dataDocente,$dataPeriodo ,$dataAsignatura];

    $docenteId=Docente::findOrFail($ocenteId['id']);
    $docenteId->update([  /**/  ]);

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



    public function deleteasignacionDocentes(){
        //this function is only with url
    }
}


