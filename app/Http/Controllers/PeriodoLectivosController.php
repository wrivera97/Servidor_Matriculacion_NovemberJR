<?php

namespace App\Http\Controllers;

use App\Matricula;
use App\PeriodoLectivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodoLectivosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function get(Request $request)
    {
        $periodoLectivos = PeriodoLectivo::where('estado', '<>', 'INACTIVO')->orderBy('fecha_inicio_periodo', 'desc')
            ->paginate($request->records_per_page);
        return response()->json(['pagination' => [
            'total' => $periodoLectivos->total(),
            'current_page' => $periodoLectivos->currentPage(),
            'per_page' => $periodoLectivos->perPage(),
            'last_page' => $periodoLectivos->lastPage(),
            'from' => $periodoLectivos->firstItem(),
            'to' => $periodoLectivos->lastItem()],
            'periodos_lectivos' => $periodoLectivos], 200);
    }

    public function getHistoricos()
    {
        $periodoLectivosHistoricos = PeriodoLectivo::where('estado', '<>', 'INACTIVO')
            ->orderBy('codigo', 'desc')->get();
        return response()->json(['periodos_lectivos_historicos' => $periodoLectivosHistoricos], 200);
    }

    public function getActual()
    {
        $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        return response()->json(['periodo_lectivo_actual' => $periodoLectivo], 200);
    }

    public function getOne(Request $request)
    {
        //$data = $request->json()->all();

        $sql = 'SELECT estudiantes.* 
                FROM 
                  matriculas inner join informacion_estudiantes on matriculas.id = informacion_estudiantes.matricula_id 
	              inner join estudiantes on matriculas.estudiante_id = estudiantes.id 
	            WHERE matriculas.periodo_lectivo_id = 1 and matriculas.estudiante_id =1';
        $estudiante = DB::select($sql);

        $sql = 'SELECT informacion_estudiantes.* 
                FROM 
                  matriculas inner join informacion_estudiantes on matriculas.id = informacion_estudiantes.matricula_id 
	              inner join estudiantes on matriculas.estudiante_id = estudiantes.id 
	            WHERE matriculas.periodo_lectivo_id = 1 and matriculas.estudiante_id =1';
        $informacionEstudiante = DB::select($sql);
        return response()->json([
            'estudiante' => $estudiante[0],
            'informacion_estudiante' => $informacionEstudiante[0]
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->json()->all();
        $dataPeriodoLectivo = $data['periodo_lectivo'];

        $periodoLectivo = PeriodoLectivo::create([
            'codigo' => $dataPeriodoLectivo['codigo'],
            'nombre' => strtoupper($dataPeriodoLectivo['nombre']),
            'fecha_inicio_periodo' => $dataPeriodoLectivo['fecha_inicio_periodo'],
            'fecha_fin_periodo' => $dataPeriodoLectivo['fecha_fin_periodo'],
            'fecha_inicio_cupo' => $dataPeriodoLectivo['fecha_inicio_cupo'],
            'fecha_fin_cupo' => $dataPeriodoLectivo['fecha_fin_cupo'],
            'fecha_inicio_ordinaria' => $dataPeriodoLectivo['fecha_inicio_ordinaria'],
            'fecha_inicio_ordinaria' => $dataPeriodoLectivo['fecha_inicio_ordinaria'],
            'fecha_inicio_extraordinaria' => $dataPeriodoLectivo['fecha_inicio_extraordinaria'],
            'fecha_fin_extraordinaria' => $dataPeriodoLectivo['fecha_fin_extraordinaria'],
            'fecha_inicio_especial' => $dataPeriodoLectivo['fecha_inicio_especial'],
            'fecha_inicio_especial' => $dataPeriodoLectivo['fecha_inicio_especial'],
            'fecha_fin_anulacion' => $dataPeriodoLectivo['fecha_fin_anulacion'],
        ]);
        return response()->json(['pagination' => $periodoLectivo], 200);
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();
        $dataPeriodoLectivo = $data['periodo_lectivo'];
        $periodoLectivo = PeriodoLectivo::findOrFail($dataPeriodoLectivo['id'])->update([
            'codigo' => $dataPeriodoLectivo['codigo'],
            'nombre' => strtoupper($dataPeriodoLectivo['nombre']),
            'fecha_inicio_periodo' => $dataPeriodoLectivo['fecha_inicio_periodo'],
            'fecha_fin_periodo' => $dataPeriodoLectivo['fecha_fin_periodo'],
            'fecha_inicio_cupo' => $dataPeriodoLectivo['fecha_inicio_cupo'],
            'fecha_fin_cupo' => $dataPeriodoLectivo['fecha_fin_cupo'],
            'fecha_inicio_ordinaria' => $dataPeriodoLectivo['fecha_inicio_ordinaria'],
            'fecha_inicio_ordinaria' => $dataPeriodoLectivo['fecha_inicio_ordinaria'],
            'fecha_inicio_extraordinaria' => $dataPeriodoLectivo['fecha_inicio_extraordinaria'],
            'fecha_fin_extraordinaria' => $dataPeriodoLectivo['fecha_fin_extraordinaria'],
            'fecha_inicio_especial' => $dataPeriodoLectivo['fecha_inicio_especial'],
            'fecha_inicio_especial' => $dataPeriodoLectivo['fecha_inicio_especial'],
            'fecha_fin_anulacion' => $dataPeriodoLectivo['fecha_fin_anulacion'],
        ]);
        return response()->json(['pagination' => $periodoLectivo], 200);
    }

    public function close(Request $request)
    {
        $data = $request->json()->all();
        $dataPeriodoLectivo = $data['periodo_lectivo'];
        // DB::beginTransaction();
        $periodoLectivo = PeriodoLectivo::findOrFail($dataPeriodoLectivo['id'])
            ->update([
                'estado' => 'HISTORICO',
            ]);
//        $matriculas = Matricula::where(function ($matriculas) {
//            $matriculas->orWhere('matriculas.estado', 'APROBADO')
//                ->orWhere('matriculas.estado', 'EN_PROCESO');
//        })->where(function ($matriculas) use (&$periodoLectivo) {
//            $matriculas->where('matriculas.periodo_lectivo_id', $periodoLectivo['id']);
//        })->get();

        $matriculas = Matricula::where('matriculas.estado', 'APROBADO')
            ->orWhere('matriculas.estado', 'EN_PROCESO')
            ->get();
//        return $matriculas;
        foreach ($matriculas as $matricula) {
            $matricula->update(['estado' => 'NO_MATRICULADO']);
            $matricula->detalle_matriculas()->update(['estado' => 'NO_MATRICULADO']);
        }
        // DB::commit();
        return response()->json(['periodo_lectivo' => $periodoLectivo], 200);
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        $periodoLectivo = PeriodoLectivo::findOrFail($request->id)->delete();
        DB::commit();
        return response()->json($periodoLectivo, 201);
    }

    public function activate(Request $request)
    {
        $data = $request->json()->all();
        $dataPeriodoLectivo = $data['periodo_lectivo'];
        DB::beginTransaction();
        $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        if ($periodoLectivo) {
            $periodoLectivo->update([
                'estado' => 'HISTORICO',
            ]);
        }

        $periodoLectivo = PeriodoLectivo::findOrFail($dataPeriodoLectivo['id'])
            ->update([
                'estado' => 'ACTUAL',
            ]);
        DB::commit();
        return response()->json(['periodo_lectivo' => $periodoLectivo], 200);
    }
}
