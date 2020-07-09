<?php

namespace App\Http\Controllers;

use App\DocenteAsignatura;
use App\PeriodoLectivo;
use App\Docente;
use App\Asignatura;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocenteAsignaturasController extends Controller
{
    public function getDocenteAsignatura(Request $request)
    {

        $docente = Docente::where('id', $request->id)->first();
        $docenteAsignaturas = DocenteAsignatura::select('docente_asignaturas.*', 'carreras.descripcion')
            ->join('asignaturas', 'asignaturas.id', 'docente_asignaturas.asignatura_id')
            ->join('mallas', 'mallas.id', 'asignaturas.malla_id')
            ->join('carreras', 'carreras.id', 'mallas.carrera_id')
            ->where('docente_id', $docente->id)->where('periodo_lectivo_id', $request->periodo_lectivo_id)->with('asignatura')->orderby('periodo_academico_id')->get();


        if ($docente) {
            return response()->json(['asignacionesDocente' => $docenteAsignaturas], 200);
        }
        return response()->json('error', 500);
    }


    public function getAsignaturaDocente(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $docente = Docente::where('user_id', $user->id)->first();
        $docenteasignatura = DocenteAsignatura::select('docente_asignaturas.*', 'carreras.descripcion')
            ->join('asignaturas', 'asignaturas.id', 'docente_asignaturas.asignatura_id')
            ->join('mallas', 'mallas.id', 'asignaturas.malla_id')
            ->join('carreras', 'carreras.id', 'mallas.carrera_id')
            ->where('docente_id', $docente->id)->where('periodo_lectivo_id', $request->periodo_lectivo_id)
            ->where('docente_asignaturas.estado', 'ACTIVO')->with('asignatura')->orderby('periodo_academico_id')->get();


        if ($user) {
            return response()->json(['docente_asignaturas' => $docenteasignatura], 200);
        }
        return response()->json('error', 500);
    }


    public function AsignarDocentesAsignaturas(Request $request)
    {
        $data = $request->json()->all();
        $dataDocenteAsignaturas = $data['docente_asignatura'];
        $dataDocente = $dataDocenteAsignaturas['docente'];
        $dataPeriodoLectivo = $dataDocenteAsignaturas['periodo_lectivo'];
        $dataAsignatura = $dataDocenteAsignaturas['asignatura'];

        $dataAll = [$dataDocenteAsignaturas, $dataDocente, $dataPeriodoLectivo, $dataAsignatura];

        $docenteasignaturas = new DocenteAsignatura([
            'paralelo' => $dataDocenteAsignaturas['paralelo'],
            'jornada' => $dataDocenteAsignaturas['jornada'],
            'estado' => $dataDocenteAsignaturas['estado']
        ]);

        $docente = Docente::findOrFail($dataDocente['id']);
        $docenteasignaturas->docente()->associate($docente);

        $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodoLectivo['id']);
        $docenteasignaturas->periodoLectivo()->associate($periodolectivo);

        $asignatura = Asignatura::findOrFail($dataAsignatura['id']);
        $docenteasignaturas->asignatura()->associate($asignatura);
        $docenteasignaturas->save();

        if ($dataAll) {
            return response()->json(['' => $dataAll], 200);
        }

        return response()->json('error', 500);
    }

    public function updateAsignaturaDocente(Request $request)
    {

        try {
            DB::beginTransaction();
            $data = $request->json()->all();
            $dataDocenteAsignaturas = $data['docente_asignatura'];
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

            if ($docenteAsignatura) {

                return response()->json(['succesfull' => $docenteAsignatura], 200);
            }

            return response()->json('error', 500);
            //cierra el try
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
    public function deleteAsignacionDocente(Request $request)
    {
        DB::beginTransaction();
        $DocenteAsigntura = DocenteAsignatura::findOrFail($request->id);
        $DocenteAsigntura->update(['estado' => 'ANULADO']);
        DB::commit();
        return response()->json(['Succesfull', $DocenteAsigntura], 201);
    }
}
