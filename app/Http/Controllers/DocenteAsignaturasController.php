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
    $dataDocenteAsignaturas = $data['docenteasignaturas'];
    $dataDocente = $data['docente'];
    $dataPeriodo = $data['periodo'];
    $dataAsignatura = $data['asignatura'];

    $docenteasignaturas = new DocenteAsignatura([
    'paralelo' => $dataDocenteAsignaturas['paralelo'],
     'jornada' => $dataDocenteAsignaturas['jornada']
    ]);

       $docente = Docente::findOrFail($dataDocente['docente']['id']);
       $docenteasignaturas->docente()->associate($docente);

       $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodo['periodo']['id']);
       $docenteasignaturas->periodolectivo()->associate($periodolectivo);

       $asignatura = Asignatura::findOrFail($dataAsignatura['asignatura']['id']);
       $docenteasignaturas->asignatura()->associate($asignatura);

       $docenteasignaturas->save();

}

public function getAsignacionDocentes( Request $request){

   $data = $request->json()->all();
   $dataDocenteid=$data['cedula_docente'];

$docentesced=db::table('docentes')
->select('nombre1','apellido1','tipo_identificacion','jornada','paralelo',
        'nombre as nombre_asignatura','codigo','asignaturas.estado','identificacion')
->leftJoin('docente_asignaturas','docentes.id','=','docente_asignaturas.docente_id')
->leftJoin('asignaturas','asignaturas.id','=','docente_asignaturas.asignatura_id')
->where('docentes.identificacion','=', $dataDocenteid=$data['cedula_docente'])->get();

if ($docentesced) {
return response()->json([$docentesced], 200);
}
else{
    return response()->json([$docentesced], 500);
}
}


public function deleteasignacionDocentes(){
    //this function is only with url
}
public function updateasignacionDocentes(){

    //this function is the same, what the function create
}
}

