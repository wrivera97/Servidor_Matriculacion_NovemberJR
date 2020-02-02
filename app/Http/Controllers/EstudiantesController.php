<?php

namespace App\Http\Controllers;

use App\Carrera;
use App\User;
use App\Estudiante;
use App\InformacionEstudiante;
use App\Instituto;
use App\Matricula;
use App\PeriodoLectivo;
use App\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudiantesController extends Controller
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

    public function getHistoricos(Request $request)
    {
        $cupo = Matricula::select('matriculas.*', 'carreras.descripcion as carrera')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->with('estudiante')
            ->with('periodo_academico')
            ->with('periodo_lectivo')
            ->with('tipo_matricula')
            ->where(function ($cupo) use (&$request) {
                $cupo->orWhere('apellido1', 'like', '%' . $request->apellido1 . '%')
                    ->orWhere('apellido2', 'like', '%' . $request->apellido2 . '%')
                    ->orWhere('nombre1', 'like', '%' . $request->nombre1 . '%')
                    ->orWhere('nombre2', 'like', '%' . $request->nombre2 . '%')
                    ->orWhere('identificacion', 'like', '%' . $request->identificacion . '%');
            })
            ->where(function ($cupo) use (&$malla, &$request) {
                $cupo->where('matriculas.periodo_lectivo_id', '=', $request->periodo_lectivo_id);
            })
            ->limit(100)
            ->get();

        return response()->json(['cupo' => $cupo], 200);
    }

    public function getOne(Request $request)
    {
        try {
            $estudiante = Estudiante::where('user_id', $request->id)->with('canton_nacimiento')->first();
            $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
            $matricula = Matricula::select(
                'matriculas.*',
                'carreras.id as carrera_id'
            )
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
                ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
                ->join('institutos', 'institutos.id', '=', 'carreras.instituto_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where('matriculas.estudiante_id', $estudiante->id)
                ->where('matriculas.periodo_lectivo_id', $periodoLectivoActual->id)
                ->first();

            $informacionEstudiante = InformacionEstudiante::where('matricula_id', $matricula->id)->with('canton_residencia')->first();
            $carrera = Carrera::findOrFail($matricula->carrera_id);
            $instituto = Instituto::findOrFail($carrera->instituto_id);

            if ($estudiante->canton_nacimiento->tipo == 'PAIS') {
                $ubicacionNacimiento = array(['canton_id' => 0, 'canton_nombre' => '', 'provincia_id' => '0'
                    , 'provincia_nombre' => '', 'pais_id' => $estudiante->canton_nacimiento_id, 'pais_nombre' => '']);
            } else {
                $ubicacionNacimiento = DB::select('select
                canton.id as canton_id,
                canton.nombre as canton_nombre,
                provincia.id as provincia_id,
                provincia.nombre as provincia_nombre,
                pais.id as pais_id,
                pais.nombre as pais_nombre
            from
                (select canton.* from ubicaciones as canton inner join estudiantes on canton.id = estudiantes.canton_nacimiento_id
                    where estudiantes.id =' . $matricula->estudiante->id . ' limit 1) as canton,
                (select provincia.* from ubicaciones as provincia where provincia.id =
                 (select codigo_padre_id from ubicaciones cantones_n inner join estudiantes on cantones_n.id = estudiantes.canton_nacimiento_id
                    where estudiantes.id = ' . $matricula->estudiante->id . ' limit 1)) as provincia,
                (select pais.* from ubicaciones as pais where pais.id =
                (select codigo_padre_id from ubicaciones  provincia  where provincia.id =
                 (select codigo_padre_id from ubicaciones cantones_n inner join estudiantes on cantones_n.id = estudiantes.canton_nacimiento_id
                    where estudiantes.id = ' . $matricula->estudiante->id . ' limit 1))
                ) as pais');
            }

            if ($informacionEstudiante->canton_residencia->tipo == 'PAIS') {
                $ubicacionResidencia = array(['canton_id' => 0, 'canton_nombre' => '', 'provincia_id' => '0'
                    , 'provincia_nombre' => '', 'pais_id' => $informacionEstudiante->canton_residencia_id, 'pais_nombre' => '']);
            } else {
                $ubicacionResidencia = DB::select('select
	        canton.id as canton_id,
    canton.nombre as canton_nombre,
    provincia.id as provincia_id,
    provincia.nombre as provincia_nombre,
    pais.id as pais_id,
    pais.nombre as pais_nombre
from
(select canton.* from ubicaciones as canton inner join informacion_estudiantes on canton.id = informacion_estudiantes.canton_residencia_id
	where informacion_estudiantes.id =' . $informacionEstudiante->id . 'limit 1) as canton,
(select provincia.* from ubicaciones  provincia  where provincia.id =
 (select codigo_padre_id from ubicaciones cantones_r inner join informacion_estudiantes on cantones_r.id = informacion_estudiantes.canton_residencia_id
	where informacion_estudiantes.id = ' . $informacionEstudiante->id . 'limit 1)) as provincia,
(select pais.* from ubicaciones pais where pais.id =
(select codigo_padre_id from ubicaciones  provincia  where provincia.id =
 (select codigo_padre_id from ubicaciones cantones_r inner join informacion_estudiantes on cantones_r.id = informacion_estudiantes.canton_residencia_id
	where informacion_estudiantes.id = ' . $informacionEstudiante->id . ' limit 1))
) as pais');
            }
        } catch (\ErrorException $e) {
            return response()->json(['errorInfo' => ['001']], 404);
        }
        return response()->json([
            'matricula' => $matricula,
            'estudiante' => $estudiante,
            'informacion_estudiante' => $informacionEstudiante,
            'ubicacion_nacimiento' => $ubicacionNacimiento,
            'ubicacion_residencia' => $ubicacionResidencia,
        ], 200);
    }

    public function getEnProceso(Request $request)
    {

        $estudiantes = Estudiante::where('estado', 'EN_PROCESO')->paginate($request->records_per_page);;

        return response()->json(['pagination' => [
            'total' => $estudiantes->total(),
            'current_page' => $estudiantes->currentPage(),
            'per_page' => $estudiantes->perPage(),
            'last_page' => $estudiantes->lastPage(),
            'from' => $estudiantes->firstItem(),
            'to' => $estudiantes->lastItem()],
            'estudiantes' => $estudiantes], 200);
    }

    public function updatePerfil(Request $request)
    {
        $data = $request->json()->all();
        $dataEstudiante = $data['estudiante'];
        $dataInformacionEstudiante = $data['informacion_estudiante'];

        $informacionEstudiante = InformacionEstudiante::findOrFail($dataInformacionEstudiante['id']);
        $estudiante = Estudiante::findOrFail($dataEstudiante['id']);

        $ubicacionNacimiento = Ubicacion::findOrFail($dataEstudiante['canton_nacimiento']['id']);
        $estudiante->canton_nacimiento()->associate($ubicacionNacimiento);
        $estudiante->save();

        $ubicacionResidencia = Ubicacion::findOrFail($dataInformacionEstudiante['canton_residencia']['id']);
        $informacionEstudiante->canton_residencia()->associate($ubicacionResidencia);
        $informacionEstudiante->save();

        $estudiante->update([
            'correo_personal' => strtolower($dataEstudiante['correo_personal']),
            'etnia' => $dataEstudiante['etnia'],
            'genero' => $dataEstudiante['genero'],
            'pueblo_nacionalidad' => $dataEstudiante['pueblo_nacionalidad'],
            'sexo' => $dataEstudiante['sexo'],
            'tipo_colegio' => $dataEstudiante['tipo_colegio'],
            'tipo_bachillerato' => $dataEstudiante['tipo_bachillerato'],
            'anio_graduacion' => $dataEstudiante['anio_graduacion'],
            'tipo_sangre' => $dataEstudiante['tipo_sangre']
        ]);

        $informacionEstudiante->update([
            'alcance_vinculacion' => $dataInformacionEstudiante ['alcance_vinculacion'],
            'area_trabajo_empresa' => $dataInformacionEstudiante ['area_trabajo_empresa'],
            'categoria_migratoria' => $dataInformacionEstudiante ['categoria_migratoria'],
            'codigo_postal' => $dataInformacionEstudiante ['codigo_postal'],
            'contacto_emergencia_nombres' => strtoupper($dataInformacionEstudiante ['contacto_emergencia_nombres']),
            'contacto_emergencia_parentesco' => strtoupper($dataInformacionEstudiante ['contacto_emergencia_parentesco']),
            'contacto_emergencia_telefono' => $dataInformacionEstudiante ['contacto_emergencia_telefono'],
            'destino_ingreso' => $dataInformacionEstudiante ['destino_ingreso'],
            'direccion' => strtoupper($dataInformacionEstudiante ['direccion']),
            'estado_civil' => $dataInformacionEstudiante ['estado_civil'],
            'ha_realizado_practicas' => $dataInformacionEstudiante ['ha_realizado_practicas'],
            'ha_realizado_vinculacion' => $dataInformacionEstudiante ['ha_realizado_vinculacion'],
            'horas_practicas' => $dataInformacionEstudiante ['horas_practicas'],
            'horas_vinculacion' => $dataInformacionEstudiante ['horas_vinculacion'],
            'habla_idioma_ancestral' => $dataInformacionEstudiante ['habla_idioma_ancestral'],
            'idioma_ancestral' => $dataInformacionEstudiante ['idioma_ancestral'],
            'ingreso_familiar' => $dataInformacionEstudiante ['ingreso_familiar'],
            'nivel_formacion_madre' => $dataInformacionEstudiante ['nivel_formacion_madre'],
            'nivel_formacion_padre' => $dataInformacionEstudiante ['nivel_formacion_padre'],
            'nombre_empresa_labora' => strtoupper($dataInformacionEstudiante ['nombre_empresa_labora']),
            'numero_carnet_conadis' => $dataInformacionEstudiante ['numero_carnet_conadis'],
            'numero_miembros_hogar' => $dataInformacionEstudiante ['numero_miembros_hogar'],
            'ocupacion' => $dataInformacionEstudiante ['ocupacion'],
            'porcentaje_discapacidad' => $dataInformacionEstudiante ['porcentaje_discapacidad'],
            'posee_titulo_superior' => $dataInformacionEstudiante ['posee_titulo_superior'],
            'recibe_bono_desarrollo' => $dataInformacionEstudiante ['recibe_bono_desarrollo'],
            'sector_economico_practica' => $dataInformacionEstudiante ['sector_economico_practica'],
            'telefono_celular' => $dataInformacionEstudiante ['telefono_celular'],
            'telefono_fijo' => $dataInformacionEstudiante ['telefono_fijo'],
            'tiene_discapacidad' => $dataInformacionEstudiante ['tiene_discapacidad'],
            'tipo_discapacidad' => $dataInformacionEstudiante ['tipo_discapacidad'],
            'tipo_institucion_practicas' => $dataInformacionEstudiante ['tipo_institucion_practicas'],
            'titulo_superior_obtenido' => $dataInformacionEstudiante ['titulo_superior_obtenido']
        ]);

        $ubicacionNacimiento = DB::select(
            'SELECT canton.id AS canton_id, canton.nombre AS canton_nombre, provincia.id AS provincia_id,
                    provincia.nombre AS provincia_nombre, pais.id AS pais_id, pais.nombre AS pais_nombre
                    FROM
                    (select canton.* FROM ubicaciones as canton inner join estudiantes on canton.id = estudiantes.canton_nacimiento_id
	                WHERE estudiantes.id =' . $estudiante->id . ' limit 1) as canton,
                    (select provincia.* from ubicaciones as provincia where provincia.id =
                    (select codigo_padre_id from ubicaciones cantones_n inner join estudiantes on cantones_n.id = estudiantes.canton_nacimiento_id
	                where estudiantes.id = ' . $estudiante->id . ' limit 1)) as provincia,
                    (select pais.* from ubicaciones as pais where pais.id =
                    (select codigo_padre_id from ubicaciones  provincia  where provincia.id =
                    (select codigo_padre_id from ubicaciones cantones_n inner join estudiantes on cantones_n.id = estudiantes.canton_nacimiento_id
	                where estudiantes.id = ' . $estudiante->id . ' limit 1))) as pais');
        $ubicacionResidencia = DB::select(
            'SELECT canton.id as canton_id, canton.nombre as canton_nombre, provincia.id as provincia_id,
                    provincia.nombre as provincia_nombre, pais.id as pais_id, pais.nombre as pais_nombre
                    FROM (select canton.* from ubicaciones as canton inner join informacion_estudiantes on canton.id = informacion_estudiantes.canton_residencia_id
	                where informacion_estudiantes.id =' . $informacionEstudiante->id . 'limit 1) as canton,
                    (select provincia.* from ubicaciones  provincia  where provincia.id =
                    (select codigo_padre_id from ubicaciones cantones_r inner join informacion_estudiantes on cantones_r.id = informacion_estudiantes.canton_residencia_id
	                where informacion_estudiantes.id = ' . $informacionEstudiante->id . 'limit 1)) as provincia,
                    (select pais.* from ubicaciones pais where pais.id =
                    (select codigo_padre_id from ubicaciones  provincia  where provincia.id =
                    (select codigo_padre_id from ubicaciones cantones_r inner join informacion_estudiantes on cantones_r.id = informacion_estudiantes.canton_residencia_id
	                where informacion_estudiantes.id = ' . $informacionEstudiante->id . ' limit 1))) as pais');
        return response()->json([
            'estudiante' => $estudiante,
            'informacion_estudiante' => $informacionEstudiante,
            'ubicacion_nacimiento' => $ubicacionNacimiento,
            'ubicacion_residencia' => $ubicacionResidencia
        ]);
    }

    public function getFormulario(Request $request)
    {
        $estudiante = Estudiante::where('user_id', $request->id)->first();
        $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        $matricula = Matricula::select(
            'matriculas.*',
            'institutos.id as instituto_id',
            'institutos.codigo_sniese as instituto_codigo_sniese',
            'institutos.nombre as instituto',
            'carreras.id as carrera_id'
        )
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('institutos', 'institutos.id', '=', 'carreras.instituto_id')
            ->with('estudiante')
            ->with('periodo_academico')
            ->with('periodo_lectivo')
            ->with('tipo_matricula')
            ->where('matriculas.estudiante_id', $estudiante->id)
            ->where('matriculas.periodo_lectivo_id', $periodoLectivoActual->id)
            ->first();
        $informacionEstudiante = InformacionEstudiante::where('matricula_id', $matricula->id)->first();
        $carrera = Carrera::findOrFail($matricula->carrera_id);
        $instituto = Instituto::findOrFail($carrera->instituto_id);
        if ($estudiante->canton_nacimiento->tipo == 'PAIS') {
            $ubicacionNacimiento = array(['canton_id' => 0, 'canton_nombre' => 'N/A', 'provincia_id' => '0'
                , 'provincia_nombre' => 'N/A', 'pais_id' => $estudiante->canton_nacimiento_id, 'pais_nombre' => $estudiante->canton_nacimiento->nombre]);
        } else {
            $ubicacionNacimiento = DB::select('select
    canton.id as canton_id,
    canton.nombre as canton_nombre,
    provincia.id as provincia_id,
    provincia.nombre as provincia_nombre,
    pais.id as pais_id,
    pais.nombre as pais_nombre
from
(select canton.* from ubicaciones as canton inner join estudiantes on canton.id = estudiantes.canton_nacimiento_id
	where estudiantes.id =' . $matricula->estudiante->id . ' limit 1) as canton,
(select provincia.* from ubicaciones as provincia where provincia.id =
 (select codigo_padre_id from ubicaciones cantones_n inner join estudiantes on cantones_n.id = estudiantes.canton_nacimiento_id
	where estudiantes.id = ' . $matricula->estudiante->id . ' limit 1)) as provincia,
(select pais.* from ubicaciones as pais where pais.id =
(select codigo_padre_id from ubicaciones  provincia  where provincia.id =
 (select codigo_padre_id from ubicaciones cantones_n inner join estudiantes on cantones_n.id = estudiantes.canton_nacimiento_id
	where estudiantes.id = ' . $matricula->estudiante->id . ' limit 1))
) as pais');
        }

        if ($informacionEstudiante->canton_residencia->tipo == 'PAIS') {
            $ubicacionResidencia = array(['canton_id' => 0, 'canton_nombre' => 'N/A', 'provincia_id' => '0'
                , 'provincia_nombre' => 'N/A', 'pais_id' => $informacionEstudiante->canton_residencia_id, 'pais_nombre' => $informacionEstudiante->canton_residencia->nombre]);
        } else {
            $ubicacionResidencia = DB::select('select
	        canton.id as canton_id,
    canton.nombre as canton_nombre,
    provincia.id as provincia_id,
    provincia.nombre as provincia_nombre,
    pais.id as pais_id,
    pais.nombre as pais_nombre
from
(select canton.* from ubicaciones as canton inner join informacion_estudiantes on canton.id = informacion_estudiantes.canton_residencia_id
	where informacion_estudiantes.id =' . $informacionEstudiante->id . 'limit 1) as canton,
(select provincia.* from ubicaciones  provincia  where provincia.id =
 (select codigo_padre_id from ubicaciones cantones_r inner join informacion_estudiantes on cantones_r.id = informacion_estudiantes.canton_residencia_id
	where informacion_estudiantes.id = ' . $informacionEstudiante->id . 'limit 1)) as provincia,
(select pais.* from ubicaciones pais where pais.id =
(select codigo_padre_id from ubicaciones  provincia  where provincia.id =
 (select codigo_padre_id from ubicaciones cantones_r inner join informacion_estudiantes on cantones_r.id = informacion_estudiantes.canton_residencia_id
	where informacion_estudiantes.id = ' . $informacionEstudiante->id . ' limit 1))
) as pais');
        }

        return response()->json([
            'matricula' => $matricula,
            'informacion_estudiante' => $informacionEstudiante,
            'instituto' => $instituto,
            'carrera' => $carrera,
            'ubicacion_nacimiento' => $ubicacionNacimiento,
            'ubicacion_residencia' => $ubicacionResidencia,
        ], 200);
    }

    public function invoice()
    {
        $pdf = \PDF::loadView('invoice');
        return $pdf->download('ejemplo.pdf');
        return view('invoice');
        $data = $this->getData();
        $date = date('Y-m-d');
        $invoice = "2222";
        $view = \View::make('invoice', compact('data', 'date', 'invoice'))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($view);
        return $pdf->stream('invoice');
    }

    public function getData()
    {
        $data = [
            'quantity' => '1',
            'description' => 'some ramdom text',
            'price' => '500',
            'total' => '500'
        ];
        return $data;
    }

    public function getInformacionEstudianteCupo(Request $request)
    {
        try {
            $estudiante = Estudiante::findOrFail($request->id);

        } catch (\ErrorException $e) {
            return response()->json(['errorInfo' => ['001']], 404);
        }
        return response()->json([
            'estudiante' => $estudiante
        ], 200);
    }

    public function updateInformacionEstudianteCupo(Request $request)
    {
        try {
            $data = $request->json()->all();
            $dataEstudiante = $data['estudiante'];
            $estudiante = Estudiante::findOrFail($dataEstudiante['id']);
            $user = User::findOrFail($estudiante->user_id);
            $user->update([
                'email' => $dataEstudiante['correo_institucional'],
            ]);
            $estudiante->update([
                'tipo_identificacion' => $dataEstudiante['tipo_identificacion'],
                'identificacion' => strtoupper(trim($dataEstudiante['identificacion'])),
                'apellido1' => strtoupper(trim($dataEstudiante['apellido1'])),
                'apellido2' => strtoupper(trim($dataEstudiante['apellido2'])),
                'nombre1' => strtoupper(trim($dataEstudiante['nombre1'])),
                'nombre2' => strtoupper(trim($dataEstudiante['nombre2'])),
                'fecha_nacimiento' => $dataEstudiante['fecha_nacimiento'],
                'correo_institucional' => strtolower(trim($dataEstudiante['correo_institucional'])),
            ]);

        } catch (\ErrorException $e) {
            return response()->json(['errorInfo' => ['001']], 404);
        }
        return response()->json([
            'estudiante' => $estudiante
        ], 200);
    }

    public function getSolicitudMatricula(Request $request)
    {
        $estudiante = Estudiante::where('user_id', $request->user_id)->first();
        $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        $certificadoMatricula = Matricula::select(
            'matriculas.*',
            'institutos.id as instituto_id',
            'institutos.nombre as instituto',
            'carreras.nombre as carrera',
            'asignaturas.nombre as asignatura',
            'asignaturas.horas_docente as horas_docente',
            'asignaturas.horas_practica as horas_practica',
            'asignaturas.horas_autonoma as horas_autonoma',
            'asignaturas.codigo as asignatura_codigo',
            'asignaturas.periodo_academico_id as periodo',
            'detalle_matriculas.numero_matricula as numero_matricula',
            'detalle_matriculas.jornada as jornada'
        )
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('detalle_matriculas', 'detalle_matriculas.matricula_id', '=', 'matriculas.id')
            ->join('asignaturas', 'asignaturas.id', '=', 'detalle_matriculas.asignatura_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('institutos', 'institutos.id', '=', 'carreras.instituto_id')
            ->with('estudiante')
            ->with('periodo_academico')
            ->with('periodo_lectivo')
            ->where('matriculas.periodo_lectivo_id', $periodoLectivoActual->id)
            ->where('matriculas.estudiante_id', $estudiante->id)
            ->where('detalle_matriculas.estado', '<>', 'ANULADO')
            // ->where('detalle_matriculas.estado', '=', 'APROBADO')
            // ->where('detalle_matriculas.estado', '<>', 'EN_PROCESO')
            ->orderby('asignaturas.periodo_academico_id')
            ->orderby('asignaturas.nombre')
            ->get();

        return response()->json(['solicitud' => $certificadoMatricula], 200);
    }
}
