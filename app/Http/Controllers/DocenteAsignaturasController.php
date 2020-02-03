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
    public function AsignarDocentesAsignaturas(Request $request)
    {
    $data = $request->json()->all();
    $dataDocenteAsignaturas = $data['docenteasignatura'];
    $dataDocente = $data['docente'];
    $dataPeriodo = $data['periodo'];
    $dataAsignatura = $data['asignatura'];

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

}

public function getDocenteAsignatura( Request $request){

   $docenteAsignatura = DocenteAsignatura::select('docente_id')->with('docente')->get();
   return response()->json(['asignaciones' => $docenteAsignatura], 200);
  /* $datadocentes=$docenteAsignatura=DocenteAsignatura::where('docente_id')->with('docente')->get()->first();
   return response()->json(['asignaciones' => $datadocentes], 200);*/
}


public function deleteasignacionDocentes(){
    //this function is only with url
}
public function updateasignacionDocentes(){

    //this function is the same, what the function create
}
}

