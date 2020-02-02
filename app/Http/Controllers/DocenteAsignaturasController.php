<?php

namespace App\Http\Controllers;
use App\DocenteAsignatura;
use App\PeriodoLectivo;
use App\Docente;
use App\Asignatura;
use Illuminate\Http\Request;

class DocenteAsignaturasController extends Controller
{
    public function asignardocentesasignaturas(Request $request)
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
       $dataDocenteAsignaturas->docente()->associate($docente);

       $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodo['periodo']['id']);
       $dataDocenteAsignaturas->periodolectivo()->associate($periodolectivo);

       $asignatura = Asignatura::findOrFail($dataAsignatura['asignatura']['id']);
       $dataDocenteAsignaturas->asignatura()->associate($asignatura);

       $dataDocenteAsignaturas->save();
}
public function getasignacionDocentes(){}
public function deleteasignacionDocentes(){}
public function updateasignacionDocentes(){}


}

