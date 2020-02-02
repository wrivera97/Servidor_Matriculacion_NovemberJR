<?php

namespace App\Http\Controllers;

use App\Asignatura;
use App\DetalleMatricula;
use App\Malla;
use App\Matricula;
use App\TipoMatricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetalleMatriculasController extends Controller
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
        $detalleMatricula = DetalleMatricula::select('detalle_matriculas.*')
            ->join('asignaturas', 'asignaturas.id', 'detalle_matriculas.asignatura_id')
            ->where('matricula_id', $request->id)
            ->with('asignatura')->with('tipo_matricula')
            ->orderby('asignaturas.periodo_academico_id')
            ->orderby('asignaturas.nombre')
            ->get();
        return response()->json(['detalle_matricula' => $detalleMatricula], 200);
    }

    public function getCountDetalleCuposCarrera(Request $request)
    {
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        if ($request->periodo_academico_id) {
            $enProcesoCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('periodo_academico_id', $request->periodo_academico_id)
                ->where('matriculas.estado', 'EN_PROCESO')
                ->count();
            $aprobadosCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('periodo_academico_id', $request->periodo_academico_id)
                ->where('matriculas.estado', 'APROBADO')
                ->count();

            $matriculadosCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('periodo_academico_id', $request->periodo_academico_id)
                ->where('matriculas.estado', 'MATRICULADO')
                ->count();

            $anuladosCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('periodo_academico_id', $request->periodo_academico_id)
                ->where('matriculas.estado', 'ANULADO')
                ->count();

            $desertoresCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('periodo_academico_id', $request->periodo_academico_id)
                ->where('matriculas.estado', 'DESERTOR')
                ->count();
        } else {
            $enProcesoCount = Matricula::
            where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('matriculas.estado', 'EN_PROCESO')
                ->count();
            $aprobadosCount = Matricula::
            where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('matriculas.estado', 'APROBADO')
                ->count();

            $matriculadosCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('matriculas.estado', 'MATRICULADO')
                ->count();

            $anuladosCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('matriculas.estado', 'ANULADO')
                ->count();

            $desertoresCount = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                ->where('matriculas.estado', 'DESERTOR')
                ->count();
        }
        return response()->json([
            'en_proceso_count' => $enProcesoCount,
            'aprobados_count' => $aprobadosCount,
            'matriculados_count' => $matriculadosCount,
            'anulados_count' => $anuladosCount,
            'desertores_count' => $desertoresCount
        ], 200);
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
        try {
            DB::beginTransaction();
            $data = $request->json()->all();
            $dataDetalleMatricula = $data['detalle_matricula'];

            $detalleMatricula = new DetalleMatricula([
                'paralelo' => $dataDetalleMatricula['paralelo'],
                'numero_matricula' => $dataDetalleMatricula['numero_matricula'],
                'jornada' => $dataDetalleMatricula['jornada'],
                'estado' => $dataDetalleMatricula['estado']
            ]);
            $matricula = Matricula::findOrFail($data['detalle_matricula']['matricula']['id']);
            $tipoMatricula = TipoMatricula::findOrFail($dataDetalleMatricula['tipo_matricula']['id']);
            $asignatura = Asignatura::findOrFail($dataDetalleMatricula['asignatura']['id']);

            $detalleMatricula->matricula()->associate($matricula);
            $detalleMatricula->asignatura()->associate($asignatura);
            $detalleMatricula->tipo_matricula()->associate($tipoMatricula);
            $detalleMatricula->save();

            if ($dataDetalleMatricula['estado'] == 'EN_PROCESO') {
                $matricula->update(['estado' => 'EN_PROCESO']);
            }

            if ($dataDetalleMatricula['estado'] == 'MATRICULADO' || $dataDetalleMatricula['estado'] == 'APROBADO') {
                // $matricula->update(['estado' => 'APROBADO']);
            }
            DB::commit();
            return response()->json(['detalle_matricula' => $detalleMatricula], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json($e, 405);
        } catch (NotFoundHttpException  $e) {
            return response()->json($e, 405);
        } catch (\PDOException $e) {
            return response()->json($e, 409);
        } catch (QueryException $e) {
            return response()->json('asdasd', 200);
        } catch (Exception $e) {
            return response()->json($e, 500);
        } catch (Error $e) {
            return response()->json($e, 500);
        } catch (ErrorException $e) {
            return response()->json($e, 500);
        }
    }

    public function updateCupo(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->json()->all();
            $dataDetalleMatricula = $data['detalle_matricula'];
            $detalleMatricula = DetalleMatricula::findOrFail($dataDetalleMatricula['id']);
            if ($detalleMatricula->estado != 'MATRICULADO' && $detalleMatricula->estado != 'ANULADO'
                && $detalleMatricula->estado != 'DESERTOR' && $detalleMatricula->estado != 'NO_MATRICULADO') {
                $detalleMatricula->update([
                    'paralelo' => $dataDetalleMatricula['paralelo'],
                    'jornada' => $dataDetalleMatricula['jornada'],
                    'numero_matricula' => $dataDetalleMatricula['numero_matricula']
                ]);
                $asignatura = Asignatura::findOrFail($dataDetalleMatricula['asignatura']['id']);
                $tipoMatricula = TipoMatricula::findOrFail($dataDetalleMatricula['tipo_matricula']['id']);
                $detalleMatricula->asignatura()->associate($asignatura);
                $detalleMatricula->tipo_matricula()->associate($tipoMatricula);
                $detalleMatricula->save();
                if ($detalleMatricula->estado == 'MATRICULADO') {

                } else {
                    $detalleMatricula->matricula()->update(['estado' => 'EN_PROCESO']);
                }

                DB::commit();
                return response()->json(['detalle_matricula' => $detalleMatricula], 201);
            } else {
                return response()->json(['matriculas' => $detalleMatricula], 500);
            }


        } catch (ModelNotFoundException $e) {
            return response()->json($e, 405);
        } catch (NotFoundHttpException  $e) {
            return response()->json($e, 405);
        } catch (\PDOException $e) {
            return response()->json($e, 409);
        } catch (QueryException $e) {
            return response()->json('asdasd', 200);
        } catch (Exception $e) {
            return response()->json($e, 500);
        } catch (Error $e) {
            return response()->json($e, 500);
        } catch (ErrorException $e) {
            return response()->json($e, 500);
        }
    }

    public function updateMatricula(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->json()->all();
            $dataDetalleMatricula = $data['detalle_matricula'];
            $detalleMatricula = DetalleMatricula::findOrFail($dataDetalleMatricula['id']);
            if ($detalleMatricula->estado != 'NO_MATRICULADO' && $detalleMatricula->estado != 'ANULADO'
                && $detalleMatricula->estado != 'DESERTOR' && $detalleMatricula->estado != 'EN_PROCESO'
                && $detalleMatricula->estado != 'APROBADO') {
                $detalleMatricula->update([
                    'paralelo' => $dataDetalleMatricula['paralelo'],
                    'jornada' => $dataDetalleMatricula['jornada'],
                    'numero_matricula' => $dataDetalleMatricula['numero_matricula']
                ]);
                $asignatura = Asignatura::findOrFail($dataDetalleMatricula['asignatura']['id']);
                $tipoMatricula = TipoMatricula::findOrFail($dataDetalleMatricula['tipo_matricula']['id']);
                $detalleMatricula->asignatura()->associate($asignatura);
                $detalleMatricula->tipo_matricula()->associate($tipoMatricula);
                $detalleMatricula->save();
                if ($detalleMatricula->estado == 'MATRICULADO') {

                } else {
                    $detalleMatricula->matricula()->update(['estado' => 'EN_PROCESO']);
                }

                DB::commit();
                return response()->json(['detalle_matricula' => $detalleMatricula], 201);
            } else {
                return response()->json(['matriculas' => $detalleMatricula], 500);
            }


        } catch (ModelNotFoundException $e) {
            return response()->json($e, 405);
        } catch (NotFoundHttpException  $e) {
            return response()->json($e, 405);
        } catch (\PDOException $e) {
            return response()->json($e, 409);
        } catch (QueryException $e) {
            return response()->json('asdasd', 200);
        } catch (Exception $e) {
            return response()->json($e, 500);
        } catch (Error $e) {
            return response()->json($e, 500);
        } catch (ErrorException $e) {
            return response()->json($e, 500);
        }
    }
}
