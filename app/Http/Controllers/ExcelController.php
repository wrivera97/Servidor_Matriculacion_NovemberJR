<?php

namespace App\Http\Controllers;

use App\Asignatura;
use App\Carrera;
use App\DetalleMatricula;
use App\DetalleMatriculaTransaccion;
use App\Estudiante;
use App\InformacionEstudianteTransaccion;
use App\Malla;
use App\Matricula;
use App\MatriculaTransaccion;
use App\PeriodoAcademico;
use App\PeriodoLectivo;
use App\Role;
use App\TipoMatricula;
use App\Ubicacion;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ExcelController extends Controller
{

    public function importNotas(Request $request)
    {
        if ($request->file('archivo')) {
            $errors = array();
            $pathFile = $request->file('archivo')->store('public/archivos');
            $path = storage_path() . '/app/' . $pathFile;

            $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();

            $malla = Malla::where('carrera_id', $request->carrera_id)->first();
            $aux = 'sn';
            $response = Excel::load($path, function ($reader)
            use ($request, &$errors, &$aux) {
                $malla = Malla::where('carrera_id', $request->carrera_id)->first();
                $i = 0;

                foreach ($reader->get() as $row) {
                    try {
                        DB::beginTransaction();
                        $estudiante = Estudiante::where('identificacion', trim(strtoupper($row->cedula_estudiante)))->first();

                        $asignatura = Asignatura::where('codigo', trim(strtoupper($row->codigo_asignatura)))
                            ->where('malla_id', $malla->id)
                            ->first();

                        if ($estudiante && $asignatura) {
                            $existeMatricula = MatriculaTransaccion::where('estudiante_id', $estudiante->id)
                                ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                                ->where('estado', 'MATRICULADO')
                                ->first();

                            if ($existeMatricula) {
                                $existeDetalleMatricula = DetalleMatriculaTransaccion::where('asignatura_id', $asignatura->id)
                                    ->where('matricula_id', $existeMatricula->id)
                                    ->where('estado', 'MATRICULADO')
                                    ->first();
                                if ($existeDetalleMatricula) {
                                    $notaFinal = $row->nota1 + $row->nota2;

                                    $asistenciaFinal = ($row->asistencia1 + $row->asistencia2) / 2;

                                    if ($row->nota_final >= 69.5 && $row->asistencia_final > 69.5) {
                                        $estadoAcademico = 'A';
                                    } else {
                                        $estadoAcademico = 'R';
                                    }

                                    $aux = $existeDetalleMatricula->update([
                                        'nota1' => $row->nota1,
                                        'nota2' => $row->nota2,
                                        'nota_final' => $notaFinal,
                                        'asistencia1' => $row->asistencia1,
                                        'asistencia2' => $row->asistencia2,
                                        'asistencia_final' => $asistenciaFinal,
                                        'estado_academico' => $estadoAcademico
                                    ]);
                                }
                            }
                        } else {

                            if (!$estudiante && $row->cedula_estudiante != '') {
                                $errors['cedulas_estudiante'][] = 'cedula_estudiante: '
                                    . $row->cedula_estudiante . ' - fila: ' . ($i + 1) . ' estudiante no encontrado';
                            }
                            if (!$asignatura) {
                                $errors['asignaturas'][] = 'codigo_asignatura: ' . $row->codigo_asignatura . ' - fila: ' . ($i + 1) . ' asignatura no existe';
                            }
                            $i++;
                        }
                        DB::commit();
                    } catch (QueryException $e) {
                        return $e;
                    }
                }
            });
            Storage::delete($pathFile);
            return response()->json(['respuesta' => $response]);
//            return response()->json(['respuesta' => $aux]);

            return response()->json(['errors' => $errors], 201);
        } else {
            return response()->json(['errores' => 'Archivo no valido'], 500);
        }

    }

    public function importParalelos(Request $request)
    {
        if ($request->file('archivo')) {
            $errors = array();
            $pathFile = $request->file('archivo')->store('public/archivos');
            $path = storage_path() . '/app/' . $pathFile;
            $aux1 = 0;
            $aux2 = 0;
            $response = Excel::load($path, function ($reader)
            use ($request, &$errors) {
                $periodoLectivo = PeriodoLectivo::where('id', $request->periodo_lectivo_id)->first();
                $malla = Malla::where('carrera_id', $request->carrera_id)->first();
                $i = 0;

                foreach ($reader->get() as $row) {
                    try {
                        DB::beginTransaction();
                        $estudiante = Estudiante::where('identificacion', trim($row->cedula_estudiante))->first();
                        $asignatura = Asignatura::where('codigo', trim(strtoupper($row->codigo_asignatura)))
                            ->where('malla_id', $malla->id)
                            ->first();

                        if ($estudiante && $asignatura) {
                            $existeMatricula = MatriculaTransaccion::where('estudiante_id', $estudiante->id)
                                ->where('periodo_lectivo_id', $periodoLectivo->id)
                                ->first();
                            if ($existeMatricula) {
                                $existeMatricula->update([
//                                        'jornada' => $this->changeJornada($row->jornada_principal),
                                    'paralelo_principal' => $this->changeParalelo($row->paralelo_principal)
                                ]);
                                $existeDetalleMatricula = DetalleMatriculaTransaccion::where('asignatura_id', $asignatura->id)
                                    ->where('matricula_id', $existeMatricula->id)->first();
                                if ($existeDetalleMatricula) {
                                    $existeDetalleMatricula->update([
                                        'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
//                                        'jornada' => $this->changeJornada($row->jornada_asignatura)
                                    ]);
                                }
                            }
                        } else {

                            if (!$estudiante && $row->cedula_estudiante != '') {
                                $errors['estudiante'][] = 'cedula_estudiante: '
                                    . $row->cedula_estudiante . ' - fila ' . ($i + 1) . ': ' . 'estudiante no encontrado';
                            }
                            if (!$asignatura) {
                                $errors['asignaturas'][] = 'codigo_asignatura: ' . $row->codigo_asignatura . ' - fila ' . ($i + 1) . ': ' . 'asignatura no existe';
                            }
                            $i++;
                        }
                        DB::commit();
                    } catch (QueryException $e) {
                        return $e;
                    }
                }
            });
            Storage::delete($pathFile);
            // return response()->json(['respuesta' => $response]);

            return response()->json(['errores' => $errors], 200);
        } else {
            return response()->json(['errores' => 'Archivo no valido'
            ], 500);
        }

    }

    public function exportMatrizSnieseCarrera(Request $request)
    {
        $now = Carbon::now();
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $carrera = Carrera::findOrFail($request->carrera_id);

        $matriculados = Matricula::selectRaw(
            'estudiantes.tipo_identificacion as "tipoDocumentoId",
                estudiantes.identificacion as "numeroIdentificacion",
                estudiantes.apellido1 as "primerApellido",
                (CASE WHEN estudiantes.apellido2 is null or estudiantes.apellido2  = \'\'
                    THEN \'NA\' ELSE trim(upper(estudiantes.apellido2)) END) as "segundoApellido",
                estudiantes.nombre1 as "primerNombre",
                (CASE WHEN estudiantes.nombre2 is null or estudiantes.nombre2  = \'\'
                    THEN \'NA\' ELSE trim(upper(estudiantes.nombre2)) END) as "segundoNombre",
                estudiantes.sexo as "sexoId",
                estudiantes.genero as "generoId",
                informacion_estudiantes.estado_civil as "estadocivilId",
                estudiantes.etnia as "etniaId",
                informacion_estudiantes.estado_civil as "estadocivilId",
                estudiantes.pueblo_nacionalidad as "pueblonacionalidadId",
                estudiantes.tipo_sangre as "tipoSangre",
                informacion_estudiantes.tiene_discapacidad as "discapacidad",
                (CASE WHEN informacion_estudiantes.porcentaje_discapacidad is null or informacion_estudiantes.porcentaje_discapacidad = 0
                    THEN \'NA\' ELSE trim(to_char(informacion_estudiantes.porcentaje_discapacidad,\'99999\')) END) as "porcentajeDiscapacidad",
                (CASE WHEN informacion_estudiantes.numero_carnet_conadis is null or informacion_estudiantes.numero_carnet_conadis = \'\'
                    THEN \'NA\' ELSE informacion_estudiantes.numero_carnet_conadis END) as "numCarnetConadis",
                informacion_estudiantes.tipo_discapacidad as "tipoDiscapacidad",
                estudiantes.fecha_nacimiento as "fechaNacimiento",
                (CASE WHEN
                    (select codigo from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id =
                    (select id from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id = estudiantes.canton_nacimiento_id
                    )))) is null
                    OR
                    (select codigo from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id =
                    (select id from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id = estudiantes.canton_nacimiento_id
                    )))) = \'\'

                THEN \'NA\'
                ELSE
                    (select codigo from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id =
                    (select id from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id = estudiantes.canton_nacimiento_id
                    ))))
                END) as "paisNacionalidadId",
                (CASE WHEN
                    (select codigo from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id = estudiantes.canton_nacimiento_id
                    )) is null
                    OR
                    (select codigo from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id = estudiantes.canton_nacimiento_id
                    )) = \'\'
                THEN \'NA\'
                ELSE
                (select codigo from ubicaciones where id =
                    (select codigo_padre_id from ubicaciones where id = estudiantes.canton_nacimiento_id
                    ))
                END) as "provinciaNacimientoId",
                (CASE WHEN
                (select codigo from ubicaciones where id = estudiantes.canton_nacimiento_id) is null or (select codigo from ubicaciones where id = estudiantes.canton_nacimiento_id) = \'\'
                or (select codigo from ubicaciones where id = estudiantes.canton_nacimiento_id) = \'43\' or (select codigo from ubicaciones where id = estudiantes.canton_nacimiento_id) = \'0\'
                THEN \'NA\'
                ELSE
                    (select codigo from ubicaciones where id = estudiantes.canton_nacimiento_id)
                END) as "cantonNacimientoId",
                (select codigo from ubicaciones where id =
                (select codigo_padre_id from ubicaciones where id =
                (select id from ubicaciones where id = (select codigo_padre_id from ubicaciones where id =
                informacion_estudiantes.canton_residencia_id)))) as "paisResidenciaId",
                (select codigo from ubicaciones where id = (select codigo_padre_id from ubicaciones where id =
                informacion_estudiantes.canton_residencia_id)) as "provinciaResidenciaId",
                (select codigo from ubicaciones where id = informacion_estudiantes.canton_residencia_id) as "cantonResidenciaId",
                estudiantes.tipo_colegio as "tipoColegioId",
                 (CASE WHEN carreras.modalidad = \'PRESENCIAL\' THEN \'1\'
                 WHEN carreras.modalidad = \'SEMI-PRESENCIAL\' THEN \'2\'
                 WHEN carreras.modalidad = \'DISTANCIA\' THEN \'3\'
                 WHEN carreras.modalidad = \'DUAL\' THEN \'4\'
                 ELSE carreras.modalidad  END) as "modalidadCarrera",
                matriculas.jornada as "jornadaCarrera",
                estudiantes.fecha_inicio_carrera as "fechaInicioCarrera",
                to_char(matriculas.fecha,\'YYYY-MM-DD\') as "fechaMatricula",
                matriculas.tipo_matricula_id as "tipoMatriculaId",
                matriculas.periodo_academico_id as "nivelAcademicoQueCursa",
                mallas.numero_semanas as "duracionPeriodoAcademico",
                informacion_estudiantes.ha_repetido_asignatura as "haRepetidoAlMenosUnaMateria",
                matriculas.paralelo_principal as "paraleloId",
                informacion_estudiantes.ha_perdido_gratuidad as "haPerdidoLaGratuidad",
                informacion_estudiantes.pension_diferenciada as "recibePensionDiferenciada",
                informacion_estudiantes.ocupacion as "estudianteocupacionId",
                informacion_estudiantes.destino_ingreso as "ingresosestudianteId",
                informacion_estudiantes.recibe_bono_desarrollo as "bonodesarrolloId",
                informacion_estudiantes.ha_realizado_practicas as "haRealizadoPracticasPreprofesionales",
                (CASE WHEN informacion_estudiantes.horas_practicas is null or informacion_estudiantes.horas_practicas = 0
                    THEN \'NA\' ELSE trim(to_char(informacion_estudiantes.horas_practicas,\'99999\')) END) as "nroHorasPracticasPreprofesionalesPorPeriodo",
                informacion_estudiantes.tipo_institucion_practicas as "entornoInstitucionalPracticasProfesionales",
                informacion_estudiantes.sector_economico_practica as "sectorEconomicoPracticaProfesional",
                informacion_estudiantes.tipo_beca as "tipoBecaId",
                informacion_estudiantes.razon_beca1 as "primeraRazonBecaId",
                informacion_estudiantes.razon_beca2 as "segundaRazonBecaId",
                informacion_estudiantes.razon_beca3 as "terceraRazonBecaId",
                informacion_estudiantes.razon_beca4 as "cuartaRazonBecaId",
                informacion_estudiantes.razon_beca5 as "quintaRazonBecaId",
                informacion_estudiantes.razon_beca6 as "sextaRazonBecaId",
                (CASE WHEN informacion_estudiantes.monto_beca is null or informacion_estudiantes.monto_beca = \'0\'
                    THEN \'NA\' ELSE to_char(informacion_estudiantes.monto_beca,\'999999999\') END) as "montoBeca",
                    (CASE WHEN informacion_estudiantes.porciento_beca_cobertura_arancel is null or informacion_estudiantes.porciento_beca_cobertura_arancel = \'0\'
                    THEN \'NA\' ELSE to_char(informacion_estudiantes.porciento_beca_cobertura_arancel,\'999999999\') END) as "porcientoBecaCoberturaArancel",

                    (CASE WHEN informacion_estudiantes.porciento_beca_cobertura_manutencion is null or informacion_estudiantes.porciento_beca_cobertura_manutencion = \'0\'
                    THEN \'NA\' ELSE to_char(informacion_estudiantes.porciento_beca_cobertura_manutencion,\'999999999\') END) as "porcientoBecaCoberturaManuntencion",

                informacion_estudiantes.tipo_financiamiento_beca as "financiamientoBeca",

                (CASE WHEN informacion_estudiantes.monto_ayuda_economica is null or informacion_estudiantes.monto_ayuda_economica = \'0\'
                    THEN \'NA\' ELSE to_char(informacion_estudiantes.monto_ayuda_economica,\'999999999\') END) as "montoAyudaEconomica",

                    (CASE WHEN informacion_estudiantes.monto_credito_educativo is null or informacion_estudiantes.monto_credito_educativo = \'0\'
                    THEN \'NA\' ELSE to_char(informacion_estudiantes.monto_credito_educativo,\'999999999\') END) as "montoCreditoEducativo",


                informacion_estudiantes.ha_realizado_vinculacion as "participaEnProyectoVinculacionSocieda",
                informacion_estudiantes.alcance_vinculacion as "tipoAlcanceProyectoVinculacionId",
       			estudiantes.correo_institucional as "correoElectronico",
                informacion_estudiantes.telefono_celular as "numeroCelular",
                informacion_estudiantes.nivel_formacion_padre as "nivelFormacionPadre",
                informacion_estudiantes.nivel_formacion_madre as "nivelFormacionMadre",
                informacion_estudiantes.ingreso_familiar as "ingresoTotalHogar",
                informacion_estudiantes.numero_miembros_hogar as "cantidadMiembrosHogar"')
            ->join('estudiantes', 'estudiantes.id', 'matriculas.estudiante_id')
            ->join('mallas', 'mallas.id', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', 'matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->where('matriculas.estado', 'MATRICULADO')
            ->orWhere('matriculas.estado', 'DESERTOR')
            ->orderBy('matriculas.periodo_academico_id')
            ->orderBy('estudiantes.apellido1')
            ->get();


//        return $matriculados;

        Excel::create('Matriz_' . $carrera->siglas . '_' . $now->format('Y-m-d H:i:s'),
            function ($excel) use ($matriculados) {
                $excel->sheet('Carreras', function ($sheet) use ($matriculados) {
                    $sheet->fromArray($matriculados);
                });
            })->download('xlsx');
    }

    public function exportErroresCupos(Request $request)
    {
        Excel::create('Errores Cupos', function ($excel) use (&$request) {
            $excel->sheet('Errores', function ($sheet) use (&$request) {
                $sheet->fromArray($request->errores);
            });
        })->download('xlsx');
    }

    public function exportNumericoMatriculas(Request $request)
    {
        $now = Carbon::now();
        $consulta = DB::select("select
    c.nombre as carrera,
    c.descripcion as malla,
	sum(case when m.estado = 'MATRICULADO' then 1 else 0 end) total_matriculados,
       sum(case when m.estado = 'EN_PROCESO' then 1 else 0 end) total_en_proceso,
       sum(case when m.estado = 'APROBADO' then 1 else 0 end) total_aprobados,
       sum(case when m.estado = 'DESERTOR' then 1 else 0 end) total_desertores,
       sum(case when m.estado = 'ANULADO' then 1 else 0 end) total_anulados,

    sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 then 1 else 0 end) matriculados_1,
    sum(case when m.estado = 'EN_PROCESO' and m.periodo_academico_id = 1 then 1 else 0 end) en_proceso_1,
    sum(case when m.estado = 'APROBADO' and m.periodo_academico_id = 1 then 1 else 0 end) aprobados_1,
       sum(case when m.estado = 'DESERTOR' and m.periodo_academico_id = 1 then 1 else 0 end) desertores_1,
       sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 1 then 1 else 0 end) anulados_1,
       -- paralelos
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '1' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_matutina_a_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '1' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_matutina_b_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '1' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_matutina_c_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '1' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_matutina_d_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '1' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_matutina_e_1,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '2' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_vespertina_a_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '2' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_vespertina_b_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '2' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_vespertina_c_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '2' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_vespertina_d_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '2' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_vespertina_e_1,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '3' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_nocturna_a_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '3' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_nocturna_b_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '3' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_nocturna_c_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '3' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_nocturna_d_1,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 1 and m.jornada_operativa = '3' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_nocturna_e_1,
	-- fin paralelos

        -- paralelos
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '1' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_matutina_a_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '1' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_matutina_b_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '1' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_matutina_c_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '1' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_matutina_d_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '1' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_matutina_e_2,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '2' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_vespertina_a_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '2' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_vespertina_b_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '2' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_vespertina_c_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '2' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_vespertina_d_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '2' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_vespertina_e_2,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '3' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_nocturna_a_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '3' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_nocturna_b_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '3' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_nocturna_c_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '3' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_nocturna_d_2,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 and m.jornada_operativa = '3' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_nocturna_e_2,
	-- fin paralelos

        -- paralelos
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '1' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_matutina_a_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '1' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_matutina_b_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '1' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_matutina_c_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '1' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_matutina_d_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '1' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_matutina_e_3,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '2' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_vespertina_a_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '2' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_vespertina_b_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '2' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_vespertina_c_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '2' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_vespertina_d_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '2' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_vespertina_e_3,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '3' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_nocturna_a_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '3' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_nocturna_b_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '3' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_nocturna_c_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '3' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_nocturna_d_3,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 and m.jornada_operativa = '3' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_nocturna_e_3,
	-- fin paralelos

	 -- paralelos
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '1' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_matutina_a_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '1' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_matutina_b_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '1' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_matutina_c_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '1' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_matutina_d_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '1' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_matutina_e_4,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '2' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_vespertina_a_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '2' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_vespertina_b_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '2' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_vespertina_c_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '2' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_vespertina_d_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '2' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_vespertina_e_4,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '3' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_nocturna_a_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '3' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_nocturna_b_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '3' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_nocturna_c_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '3' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_nocturna_d_4,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 and m.jornada_operativa = '3' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_nocturna_e_4,
	-- fin paralelos

       -- paralelos
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '1' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_matutina_a_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '1' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_matutina_b_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '1' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_matutina_c_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '1' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_matutina_d_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '1' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_matutina_e_5,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '2' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_vespertina_a_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '2' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_vespertina_b_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '2' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_vespertina_c_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '2' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_vespertina_d_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '2' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_vespertina_e_5,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '3' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_nocturna_a_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '3' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_nocturna_b_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '3' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_nocturna_c_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '3' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_nocturna_d_5,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 and m.jornada_operativa = '3' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_nocturna_e_5,
	-- fin paralelos

       -- paralelos
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '1' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_matutina_a_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '1' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_matutina_b_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '1' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_matutina_c_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '1' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_matutina_d_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '1' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_matutina_e_6,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '2' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_vespertina_a_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '2' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_vespertina_b_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '2' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_vespertina_c_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '2' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_vespertina_d_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '2' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_vespertina_e_6,

       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '3' and m.paralelo_principal = '1'
           then 1 else 0 end) matriculados_nocturna_a_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '3' and m.paralelo_principal = '2'
           then 1 else 0 end) matriculados_nocturna_b_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '3' and m.paralelo_principal = '3'
           then 1 else 0 end) matriculados_nocturna_c_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '3' and m.paralelo_principal = '4'
           then 1 else 0 end) matriculados_nocturna_d_6,
       sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 and m.jornada_operativa = '3' and m.paralelo_principal = '5'
           then 1 else 0 end) matriculados_nocturna_e_6,
	-- fin paralelos

	sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 2 then 1 else 0 end) matriculados_2,
    sum(case when m.estado = 'EN_PROCESO' and m.periodo_academico_id = 2 then 1 else 0 end) en_proceso_2,
    sum(case when m.estado = 'APROBADO' and m.periodo_academico_id = 2 then 1 else 0 end) aprobados_2,
       sum(case when m.estado = 'DESERTOR' and m.periodo_academico_id = 2 then 1 else 0 end) desertores_2,
       sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 2 then 1 else 0 end) anulados_2,

	sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 3 then 1 else 0 end) matriculados_3,
    sum(case when m.estado = 'EN_PROCESO' and m.periodo_academico_id = 3 then 1 else 0 end) en_proceso_3,
    sum(case when m.estado = 'APROBADO' and m.periodo_academico_id = 3 then 1 else 0 end) aprobados_3,
       sum(case when m.estado = 'DESERTOR' and m.periodo_academico_id = 3 then 1 else 0 end) desertores_3,
	sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 3 then 1 else 0 end) anulados_3,

	sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 4 then 1 else 0 end) matriculados_4,
    sum(case when m.estado = 'EN_PROCESO' and m.periodo_academico_id = 4 then 1 else 0 end) en_proceso_4,
    sum(case when m.estado = 'APROBADO' and m.periodo_academico_id = 4 then 1 else 0 end) aprobados_4,
       sum(case when m.estado = 'DESERTOR' and m.periodo_academico_id = 4 then 1 else 0 end) desertores_4,
       sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 4 then 1 else 0 end) anulados_4,

	sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 5 then 1 else 0 end) matriculados_5,
    sum(case when m.estado = 'EN_PROCESO' and m.periodo_academico_id = 5 then 1 else 0 end) en_proceso_5,
    sum(case when m.estado = 'APROBADO' and m.periodo_academico_id  = 5 then 1 else 0 end) aprobados_5,
       sum(case when m.estado = 'DESERTOR' and m.periodo_academico_id = 5 then 1 else 0 end) desertores_5,
       sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 5 then 1 else 0 end) anulados_5,

	sum(case when m.estado = 'MATRICULADO' and m.periodo_academico_id = 6 then 1 else 0 end) matriculados_6,
    sum(case when m.estado = 'EN_PROCESO' and m.periodo_academico_id = 6 then 1 else 0 end) en_proceso_6,
    sum(case when m.estado = 'APROBADO' and m.periodo_academico_id = 6 then 1 else 0 end) aprobados_6,
       sum(case when m.estado = 'DESERTOR' and m.periodo_academico_id = 6 then 1 else 0 end) desertores_6,
       sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 6 then 1 else 0 end) anulados_6

from MATRICULAS m
	inner join mallas ma on ma.id = m.malla_id
    inner join carreras c on c.id = ma.carrera_id
    inner join carrera_user cu on cu.carrera_id = c.id
    inner join users u on cu.user_id = u.id
where m.periodo_lectivo_id = " . $request->periodo_lectivo_id . "
  and cu.user_id=" . $request->id . "
	group by c.id,c.nombre,c.descripcion, m.malla_id
    order by malla");

        $consulta = json_decode(json_encode($consulta), true);
//return $consulta;
        Excel::create('Matriculas_' . $now->format('Y-m-d H:i:s'),
            function ($excel) use ($consulta) {
                $excel->sheet('Cupos', function ($sheet) use ($consulta) {
                    $sheet->fromArray($consulta);
                });

//                $excel->sheet('Listas', function ($sheet) use (&$request, &$listas) {
//                    $sheet->fromArray($listas);
//                });
            })->download('xlsx');
    }

    public function exportCuposCarrera(Request $request)
    {
        $now = Carbon::now();
        $periodoLectivo = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $carrera = Carrera::findOrFail($request->carrera_id);
        $cupos = Matricula::select(
            'detalle_matriculas.estado',
            'carreras.nombre as carrera',
            'carreras.descripcion as malla',
            'estudiantes.identificacion as cedula_estudiante',
            'estudiantes.apellido1',
            'estudiantes.apellido2',
            'estudiantes.nombre1',
            'estudiantes.nombre2',
            'estudiantes.correo_institucional',
            'informacion_estudiantes.telefono_celular',
            'informacion_estudiantes.telefono_fijo',
            'asignaturas.codigo as codigo_asignatura',
            'asignaturas.nombre as asignatura',
            'detalle_matriculas.numero_matricula',
            'asignaturas.periodo_academico_id as periodo_academico'
        )
            ->selectRaw("(CASE
                            WHEN detalle_matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN detalle_matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN detalle_matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN detalle_matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN detalle_matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_asignatura,

                            (CASE
                            WHEN detalle_matriculas.paralelo = '1' THEN 'A'
                            WHEN detalle_matriculas.paralelo = '2' THEN 'B'
                            WHEN detalle_matriculas.paralelo = '3' THEN 'C'
                            WHEN detalle_matriculas.paralelo = '4' THEN 'D'
                            WHEN detalle_matriculas.paralelo = '5' THEN 'E'
                            WHEN detalle_matriculas.paralelo = '6' THEN 'F'
                            WHEN detalle_matriculas.paralelo = '7' THEN 'G'
                            WHEN detalle_matriculas.paralelo = '8' THEN 'H'
                            WHEN detalle_matriculas.paralelo = '9' THEN 'I'
                            WHEN detalle_matriculas.paralelo = '10' THEN 'J'
                            WHEN detalle_matriculas.paralelo = '11' THEN 'K'
                            WHEN detalle_matriculas.paralelo = '12' THEN 'L'
                            WHEN detalle_matriculas.paralelo = '13' THEN 'M'
                            WHEN detalle_matriculas.paralelo = '14' THEN 'N' END) AS paralelo_asignatura,

                            (CASE
                            WHEN detalle_matriculas.numero_matricula = '1' THEN 'PRIMERA'
                            WHEN detalle_matriculas.numero_matricula = '2' THEN 'SEGUNDA'
                            WHEN detalle_matriculas.numero_matricula = '3' THEN 'TERCERA' END) AS numero_matricula,

                            tipo_matriculas.nombre as tipo_matricula,

                            (CASE
                            WHEN matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,

                            (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal,

                            matriculas.periodo_academico_id as periodo_academico_principal

                            ")
            ->join('detalle_matriculas', 'detalle_matriculas.matricula_id', '=', 'matriculas.id')
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('asignaturas', 'asignaturas.id', '=', 'detalle_matriculas.asignatura_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'detalle_matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->orderBy('matriculas.estado', 'DESC')
            ->orderBy('matriculas.periodo_academico_id')
            ->orderBy('estudiantes.apellido1')
            ->get();
        $listas = Matricula::select(
            'matriculas.estado',
            'carreras.nombre as carrera',
            'carreras.descripcion as malla',
            'estudiantes.identificacion as cedula_estudiante',
            'estudiantes.apellido1',
            'estudiantes.apellido2',
            'estudiantes.nombre1',
            'estudiantes.nombre2',
            'estudiantes.sexo',
            'estudiantes.correo_institucional',
            'informacion_estudiantes.telefono_celular',
            'informacion_estudiantes.telefono_fijo',
            'tipo_matriculas.nombre as tipo_matricula',
            // 'matriculas.paralelo_principal as paralelo_principal',
            // 'matriculas.jornada as jornada_principal',
            'matriculas.periodo_academico_id as periodo_academico_principal'

        )
            ->selectRaw("(CASE
                            WHEN matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,

                            (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal
                            ")
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->orderBy('matriculas.estado', 'DESC')
            ->orderBy('matriculas.periodo_academico_id')
            ->orderBy('estudiantes.apellido1')
            ->get();

        Excel::create($carrera->descripcion . ' (' . $periodoLectivo->nombre . ') ',
            function ($excel) use (&$cupos, &$listas) {
                $excel->sheet('Listas', function ($sheet) use (&$request, &$listas) {
                    $sheet->fromArray($listas);
                });
                $excel->sheet('Asignaturas', function ($sheet) use (&$request, &$cupos) {
                    $sheet->fromArray($cupos);
                });
            })->download('xlsx');
    }

    public function exportCuposMalla(Request $request)
    {
        $periodoLectivo = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        $malla = Malla::findOrFail($request->malla_id);
        $carrera = Carrera::findOrFail($malla->carrera_id);

        $listas = Matricula::select(
            'matriculas.estado',
            'carreras.nombre as carrera',
            'carreras.descripcion as malla',
            'estudiantes.identificacion as cedula_estudiante',
            'estudiantes.apellido1',
            'estudiantes.apellido2',
            'estudiantes.nombre1',
            'estudiantes.nombre2',
            'estudiantes.sexo',
            'estudiantes.correo_institucional',
            'informacion_estudiantes.telefono_celular',
            'informacion_estudiantes.telefono_fijo',
            'tipo_matriculas.nombre as tipo_matricula',
            // 'matriculas.paralelo_principal as paralelo_principal',
            // 'matriculas.jornada as jornada_principal',
            'matriculas.periodo_academico_id as periodo_academico_principal'

        )
            ->selectRaw("(CASE
                            WHEN matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,
                            (CASE
                            WHEN matriculas.jornada_operativa = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada_operativa = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada_operativa = '3' THEN 'NOCTURNA' END) AS jornada_operativa,

                            (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal

                            ")
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->where('matriculas.estado', $request->estado)
            ->orderBy('matriculas.estado', 'DESC')
            ->orderBy('matriculas.periodo_academico_id')
            ->orderBy('estudiantes.apellido1')
            ->get();

        Excel::create($request->estado . ' - ' . $carrera->descripcion . ' (' . $periodoLectivo->codigo . ') ',
            function ($excel) use (&$cupos, &$listas, $request) {
                $excel->sheet($request->estado, function ($sheet) use (&$request, &$listas) {
                    $sheet->fromArray($listas);
                });
            })->download('xlsx');
    }

    public function exportCuposMallaPeriodoAcademico(Request $request)
    {
        $periodoLectivo = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        $malla = Malla::findOrFail($request->malla_id);
        $carrera = Carrera::findOrFail($malla->carrera_id);

        $listas = Matricula::select(
            'matriculas.estado',
            'carreras.nombre as carrera',
            'carreras.descripcion as malla',
            'estudiantes.identificacion as cedula_estudiante',
            'estudiantes.apellido1',
            'estudiantes.apellido2',
            'estudiantes.nombre1',
            'estudiantes.nombre2',
            'estudiantes.sexo',
            'estudiantes.correo_institucional',
            'informacion_estudiantes.telefono_celular',
            'informacion_estudiantes.telefono_fijo',
            'tipo_matriculas.nombre as tipo_matricula',
            // 'matriculas.paralelo_principal as paralelo_principal',
            // 'matriculas.jornada as jornada_principal',
            'matriculas.periodo_academico_id as periodo_academico_principal'

        )
            ->selectRaw("(CASE
                            WHEN matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,
                             (CASE
                            WHEN matriculas.jornada_operativa = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada_operativa = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada_operativa = '3' THEN 'NOCTURNA' END) AS jornada_operativa,

                            (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal

                            ")
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->where('matriculas.periodo_academico_id', $request->periodo_academico_id)
            ->where('matriculas.estado', $request->estado)
            ->orderBy('matriculas.estado', 'DESC')
            ->orderBy('matriculas.periodo_academico_id')
            ->orderBy('estudiantes.apellido1')
            ->get();

        Excel::create($request->periodo_academico_id . ' ' . $request->estado . ' - ' . $carrera->descripcion . ' (' . $periodoLectivo->codigo . ') ',
            function ($excel) use (&$cupos, &$listas, $request) {
                $excel->sheet($request->estado, function ($sheet) use (&$request, &$listas) {
                    $sheet->fromArray($listas);
                });
            })->download('xlsx');
    }

    public function exportCuposPeriodoAcademico(Request $request)
    {
        $periodoLectivo = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $carrera = Carrera::findOrFail($request->carrera_id);
        $cupos = Matricula::select(
            'detalle_matriculas.estado',
            'carreras.nombre as carrera',
            'carreras.descripcion as malla',
            'estudiantes.identificacion as cedula_estudiante',
            'estudiantes.apellido1',
            'estudiantes.apellido2',
            'estudiantes.nombre1',
            'estudiantes.nombre2',
            'estudiantes.correo_institucional',
            'informacion_estudiantes.telefono_celular',
            'informacion_estudiantes.telefono_fijo',
            'asignaturas.codigo as codigo_asignatura',
            'asignaturas.nombre as asignatura',
            'detalle_matriculas.numero_matricula',
            'asignaturas.periodo_academico_id as periodo_academico'
        // 'detalle_matriculas.jornada as jornada_asignatura',
        // 'detalle_matriculas.paralelo as paralelo_asignatura',
        // 'detalle_matriculas.numero_matricula as numero_matricula',
        // 'tipo_matriculas.nombre as tipo_matricula',
        // 'matriculas.jornada as jornada_principal',
        // 'matriculas.paralelo_principal as paralelo_principal',
        // 'matriculas.periodo_academico_id as periodo_academico_principal',
        // 'matriculas.estado'
        )
            ->selectRaw("(CASE
                            WHEN detalle_matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN detalle_matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN detalle_matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN detalle_matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN detalle_matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,

                            (CASE
                            WHEN detalle_matriculas.paralelo = '1' THEN 'A'
                            WHEN detalle_matriculas.paralelo = '2' THEN 'B'
                            WHEN detalle_matriculas.paralelo = '3' THEN 'C'
                            WHEN detalle_matriculas.paralelo = '4' THEN 'D'
                            WHEN detalle_matriculas.paralelo = '5' THEN 'E'
                            WHEN detalle_matriculas.paralelo = '6' THEN 'F'
                            WHEN detalle_matriculas.paralelo = '7' THEN 'G'
                            WHEN detalle_matriculas.paralelo = '8' THEN 'H'
                            WHEN detalle_matriculas.paralelo = '9' THEN 'I'
                            WHEN detalle_matriculas.paralelo = '10' THEN 'J'
                            WHEN detalle_matriculas.paralelo = '11' THEN 'K'
                            WHEN detalle_matriculas.paralelo = '12' THEN 'L'
                            WHEN detalle_matriculas.paralelo = '13' THEN 'M'
                            WHEN detalle_matriculas.paralelo = '14' THEN 'N' END) AS paralelo_asignatura,

                            (CASE
                            WHEN detalle_matriculas.numero_matricula = '1' THEN 'PRIMERA'
                            WHEN detalle_matriculas.numero_matricula = '2' THEN 'SEGUNDA'
                            WHEN detalle_matriculas.numero_matricula = '3' THEN 'TERCERA' END) AS numero_matricula,

                            tipo_matriculas.nombre as tipo_matricula,

                            (CASE
                            WHEN matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,

                            (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal,

                            matriculas.periodo_academico_id as periodo_academico_principal

                            ")
            ->join('detalle_matriculas', 'detalle_matriculas.matricula_id', '=', 'matriculas.id')
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('asignaturas', 'asignaturas.id', '=', 'detalle_matriculas.asignatura_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'detalle_matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.periodo_academico_id', $request->periodo_academico_id)
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->orderBy('matriculas.estado', 'DESC')
            ->orderby('estudiantes.apellido1')
            ->get();

        $listas = Matricula::select(
            'matriculas.estado',
            'carreras.nombre as carrera',
            'carreras.descripcion as malla',
            'estudiantes.identificacion as cedula_estudiante',
            'estudiantes.apellido1',
            'estudiantes.apellido2',
            'estudiantes.nombre1',
            'estudiantes.nombre2',
            'estudiantes.sexo',
            'estudiantes.correo_institucional',
            'informacion_estudiantes.telefono_celular',
            'informacion_estudiantes.telefono_fijo',
            'tipo_matriculas.nombre as tipo_matricula',
            // 'matriculas.paralelo_principal as paralelo_principal',
            // 'matriculas.jornada as jornada_principal',
            'matriculas.periodo_academico_id as periodo_academico_principal'

        )
            ->selecTRaw("(CASE
                            WHEN matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_principal,

                            (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal

                            ")
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.periodo_academico_id', $request->periodo_academico_id)
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->orderBy('matriculas.estado', 'DESC')
            ->orderby('estudiantes.apellido1')
            ->get();

        Excel::create($request->periodo_academico_id . ' - ' . $carrera->descripcion . ' (' . $periodoLectivo->nombre . ')',
            function ($excel) use (&$cupos, &$listas) {
                $excel->sheet('Listas', function ($sheet) use (&$request, &$listas) {
                    $sheet->fromArray($listas);
                });
                $excel->sheet('Asignaturas', function ($sheet) use (&$request, &$cupos) {
                    $sheet->fromArray($cupos);
                });
            })->download('xlsx');
    }

    public function importCupos(Request $request)
    {
        if ($request->file('archivo')) {
            $aux = 'SN';
            $errors = array();
            $pathFile = $request->file('archivo')->store('public/archivos');
            $path = storage_path() . '/app/' . $pathFile;
            $i = 0;
            $countCuposNuevos = 0;
            $countCuposModificados = 0;

            $response = Excel::load($path, function ($reader)
            use ($request, &$errors, $i, &$countCuposNuevos, &$countCuposModificados, &$aux) {
                $now = Carbon::now();
                $identificacion = '';
                $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();
                $malla = Malla::where('carrera_id', $request->carrera_id)->first();

                foreach ($reader->get() as $row) {
                    try {
                        DB::beginTransaction();
                        $estudiante = Estudiante::where('identificacion', trim($row->cedula_estudiante))->first();

                        $estudianteCorreo = Estudiante::where('identificacion', trim($row->cedula_estudiante))
                            ->where('correo_institucional', trim($row->correo_institucional))->first();
                        $correo = Estudiante::where('correo_institucional', trim($row->correo_institucional))->first();
                        $asignatura = Asignatura::where('codigo', trim(strtoupper($row->codigo_asignatura)))
                            ->where('malla_id', $malla->id)
                            ->first();
                        if ($estudiante && $asignatura && $malla) {
                            $existeMatricula = MatriculaTransaccion::where('estudiante_id', $estudiante->id)
                                ->where('periodo_lectivo_id', $periodoLectivo->id)
                                ->first();

                            if (!$existeMatricula) {
                                $flagCampos = $this->validateCampos(
                                    $row->jornada_principal,
                                    $row->jornada_operativa,
                                    $row->paralelo_principal,
                                    $row->periodo_academico_principal,
                                    $row->tipo_matricula_principal,
                                    $row->paralelo_asignatura,
                                    $row->numero_matricula,
                                    $row->jornada_asignatura,
                                    $row->tipo_matricula_asignatura,
                                    $row->ha_repetido_asignatura,
                                    $row->ha_perdido_gratuidad);

                                if ($flagCampos != "") {
                                    $errors['estudiante'][] =
                                        'Fila: ' . ($i + 1)
                                        . ' Cédula Estudiante: '
                                        . $row->cedula_estudiante . ' Datos Incorrectos: ' . $flagCampos;
                                } else {
                                    $identificacion = strtoupper(trim($row->cedula_estudiante));

                                    $matriculaAnterior = MatriculaTransaccion::where('estudiante_id', $estudiante->id)
                                        ->where('estado', 'MATRICULADO')
                                        ->orderby('fecha', 'DESC')->first();
                                    $matricula = new MatriculaTransaccion([
                                        'fecha' => $now,
                                        'jornada' => $this->changeJornada($row->jornada_principal),
                                        'jornada_operativa' => $this->changeJornada($row->jornada_operativa),
                                        'paralelo_principal' => $this->changeParalelo($row->paralelo_principal),
                                        'estado' => 'EN_PROCESO'
                                    ]);

                                    $periodoAcademico = PeriodoAcademico::
                                    where('id', $this->changePeriodo($row->periodo_academico_principal))
                                        ->first();

                                    $tipoMatricula = TipoMatricula::where('id',
                                        $this->changeTipoMatricula($row->tipo_matricula_principal))->first();

                                    $matricula->estudiante()->associate($estudiante);
                                    $matricula->periodo_lectivo()->associate($periodoLectivo);
                                    $matricula->periodo_academico()->associate($periodoAcademico);
                                    $matricula->malla()->associate($malla);
                                    $matricula->tipo_matricula()->associate($tipoMatricula);
                                    $matricula->save();

                                    if ($matriculaAnterior) {
                                        $informacionEstudiante = InformacionEstudianteTransaccion::
                                        where('matricula_id', $matriculaAnterior->id)->first();

                                        $informacionEstudianteActual = $matricula->informacion_estudiantes()->create([
                                            'monto_ayuda_economica' => $informacionEstudiante->monto_ayuda_economica,
                                            'monto_beca' => $informacionEstudiante->monto_beca,
                                            'monto_credito_educativo' => $informacionEstudiante->monto_credito_educativo,
                                            'porcentaje_discapacidad' => $informacionEstudiante->porcentaje_discapacidad,
                                            'porciento_beca_cobertura_arancel' => $informacionEstudiante->porciento_beca_cobertura_arancel,
                                            'porciento_beca_cobertura_manutencion' => $informacionEstudiante->porciento_beca_cobertura_manutencion,
                                            'horas_practicas' => $informacionEstudiante->horas_practicas,
                                            'numero_miembros_hogar' => $informacionEstudiante->numero_miembros_hogar,
                                            'alcance_vinculacion' => $informacionEstudiante->alcance_vinculacion,
                                            'area_trabajo_empresa' => $informacionEstudiante->area_trabajo_empresa,
                                            'categoria_migratoria' => $informacionEstudiante->categoria_migratoria,
                                            'codigo_postal' => $informacionEstudiante->codigo_postal,
                                            'contacto_emergencia_nombres' => $informacionEstudiante->contacto_emergencia_nombres,
                                            'contacto_emergencia_telefono' => $informacionEstudiante->contacto_emergencia_telefono,
                                            'contacto_emergencia_parentesco' => $informacionEstudiante->contacto_emergencia_parentesco,
                                            'destino_ingreso' => $informacionEstudiante->destino_ingreso,
                                            'direccion' => $informacionEstudiante->direccion,
                                            'estado_civil' => $informacionEstudiante->estado_civil,
                                            'ha_realizado_practicas' => $informacionEstudiante->ha_realizado_practicas,
                                            'ha_realizado_vinculacion' => $informacionEstudiante->ha_realizado_vinculacion,
                                            'habla_idioma_ancestral' => $informacionEstudiante->habla_idioma_ancestral,
                                            'idioma_ancestral' => $informacionEstudiante->idioma_ancestral,
                                            'nivel_formacion_padre' => $informacionEstudiante->nivel_formacion_padre,
                                            'nivel_formacion_madre' => $informacionEstudiante->nivel_formacion_madre,
                                            'nombre_empresa_labora' => $informacionEstudiante->nombre_empresa_labora,
                                            'numero_carnet_conadis' => $informacionEstudiante->numero_carnet_conadis,
                                            'ocupacion' => $informacionEstudiante->ocupacion,
                                            'pension_diferenciada' => $informacionEstudiante->pension_diferenciada,
                                            'posee_titulo_superior' => $informacionEstudiante->posee_titulo_superior,
                                            'razon_beca1' => $informacionEstudiante->razon_beca1,
                                            'razon_beca2' => $informacionEstudiante->razon_beca2,
                                            'razon_beca3' => $informacionEstudiante->razon_beca3,
                                            'razon_beca4' => $informacionEstudiante->razon_beca4,
                                            'razon_beca5' => $informacionEstudiante->razon_beca5,
                                            'razon_beca6' => $informacionEstudiante->razon_beca6,
                                            'sector_economico_practica' => $informacionEstudiante->sector_economico_practica,
                                            'telefono_celular' => $informacionEstudiante->telefono_celular,
                                            'telefono_fijo' => $informacionEstudiante->telefono_fijo,
                                            'tiene_discapacidad' => $informacionEstudiante->tiene_discapacidad,
                                            'tipo_beca' => $informacionEstudiante->tipo_beca,
                                            'tipo_discapacidad' => $informacionEstudiante->tipo_discapacidad,
                                            'tipo_financiamiento_beca' => $informacionEstudiante->tipo_financiamiento_beca,
                                            'tipo_institucion_practicas' => $informacionEstudiante->tipo_institucion_practicas,
                                            'titulo_superior_obtenido' => $informacionEstudiante->titulo_superior_obtenido,
                                            'recibe_bono_desarrollo' => $informacionEstudiante->recibe_bono_desarrollo,
                                            'ingreso_familiar' => $informacionEstudiante->ingreso_familiar
                                        ]);
                                        $matricula->informacion_estudiantes()->update([
                                            'ha_repetido_asignatura' => $this->changeRespuestaSiNo($row->ha_repetido_asignatura),
                                            'ha_perdido_gratuidad' => $this->changeRespuestaSiNo($row->ha_perdido_gratuidad),
                                        ]);
                                        $cantonResidencia = Ubicacion::findOrFail($informacionEstudiante->canton_residencia_id);
                                        $informacionEstudianteActual->canton_residencia()->associate($cantonResidencia);
                                        $informacionEstudianteActual->save();

                                    } else {
                                        $matricula->informacion_estudiantes()->create([
                                            'ha_repetido_asignatura' => $this->changeRespuestaSiNo($row->ha_repetido_asignatura),
                                            'ha_perdido_gratuidad' => $this->changeRespuestaSiNo($row->ha_perdido_gratuidad)
                                        ]);
                                    }

                                    $detalleMatriculas = new DetalleMatriculaTransaccion([
                                        'paralelo' => $this->changeParalelo(trim($row->paralelo_asignatura)),
                                        'numero_matricula' => $this->changeNumeroMatricula(trim($row->numero_matricula)),
                                        'jornada' => $this->changeJornada(trim($row->jornada_asignatura)),
                                        'estado' => 'EN_PROCESO'
                                    ]);

                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))->first();
                                    $detalleMatriculas->matricula()->associate($matricula);
                                    $detalleMatriculas->asignatura()->associate($asignatura);
                                    $detalleMatriculas->tipo_matricula()->associate($tipoMatricula);
                                    $detalleMatriculas->save();
                                    $countCuposNuevos++;
                                }
                            } else if ($existeMatricula->estado == 'EN_PROCESO') {

                                if ($identificacion != $row->cedula_estudiante) {
                                    $countCuposModificados++;
                                    $identificacion = $row->cedula_estudiante;
                                    $existeMatricula->update([
                                        'fecha' => $now,
                                        'jornada' => $this->changeJornada($row->jornada_principal),
                                        'jornada_operativa' => $this->changeJornada($row->jornada_operativa),
                                        'paralelo_principal' => $this->changeParalelo($row->paralelo_principal),
                                        'estado' => 'EN_PROCESO'
                                    ]);
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_principal))->first();
                                    $periodoAcademico = PeriodoAcademico::where('id', $this->changePeriodo($row->periodo_academico_principal))->first();
                                    $existeMatricula->estudiante()->associate($estudiante);
                                    $existeMatricula->periodo_lectivo()->associate($periodoLectivo);
                                    $existeMatricula->tipo_matricula()->associate($tipoMatricula);
                                    $existeMatricula->periodo_academico()->associate($periodoAcademico);
                                    $existeMatricula->malla()->associate($malla);
                                    $existeMatricula->save();
                                }

                                $existeDetalleMatricula = DetalleMatriculaTransaccion::where('asignatura_id', $asignatura->id)
                                    ->where('matricula_id', $existeMatricula->id)->first();

                                if ($existeDetalleMatricula) {
                                    $existeDetalleMatricula->update([
                                        'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
                                        'numero_matricula' => $this->changeNumeroMatricula($row->numero_matricula),
                                        'jornada' => $this->changeJornada($row->jornada_asignatura)
                                    ]);
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))
                                        ->first();
                                    $existeDetalleMatricula->matricula()->associate($existeMatricula);
                                    $existeDetalleMatricula->asignatura()->associate($asignatura);
                                    $existeDetalleMatricula->tipo_matricula()->associate($tipoMatricula);
                                    $existeDetalleMatricula->save();
                                } else {
                                    $detalleMatriculas = new DetalleMatriculaTransaccion([
                                        'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
                                        'numero_matricula' => $this->changeNumeroMatricula($row->numero_matricula),
                                        'jornada' => $this->changeJornada($row->jornada_asignatura),
                                        'estado' => 'EN_PROCESO'
                                    ]);
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))
                                        ->first();
                                    $detalleMatriculas->matricula()->associate($existeMatricula);
                                    $detalleMatriculas->asignatura()->associate($asignatura);
                                    $detalleMatriculas->tipo_matricula()->associate($tipoMatricula);
                                    $detalleMatriculas->save();
                                }

                            }
                        } else {
                            if (!$estudiante && $row->cedula_estudiante != '') {
                                if (!$correo) {
                                    $usuario = new User([
                                        'name' => strtoupper(trim($row->apellido1) . ' ' . trim($row->nombre1)),
                                        'user_name' => strtoupper(trim($row->cedula_estudiante)),
                                        'email' => strtolower(trim($row->correo_institucional)),
                                        'password' => Hash::make(trim($row->cedula_estudiante)),
                                    ]);

                                    $rol = Role::findOrFail(2);// el 2 es el role del estudiante
                                    $usuario->role()->associate($rol);
                                    $usuario->save();

                                    $usuario->carreras()->attach($request->carrera_id);

                                    $estudiante = $usuario->estudiante()->create([
                                        'tipo_identificacion' => trim($row->tipo_identificacion),
                                        'identificacion' => trim($row->cedula_estudiante),
                                        'apellido1' => strtoupper(trim($row->apellido1)),
                                        'apellido2' => strtoupper(trim($row->apellido2)),
                                        'nombre1' => strtoupper(trim($row->nombre1)),
                                        'nombre2' => strtoupper(trim($row->nombre2)),
                                        'correo_institucional' => strtolower(trim($row->correo_institucional)),
                                        'fecha_nacimiento' => trim($row->fecha_nacimiento),
                                        'fecha_inicio_carrera' => trim($row->fecha_inicio_carrera)
                                    ]);


                                    $matricula = new MatriculaTransaccion([
                                        'fecha' => $now,
                                        'jornada' => $this->changeJornada($row->jornada_principal),
                                        'jornada_operativa' => $this->changeJornada($row->jornada_operativa),
                                        'paralelo_principal' => $this->changeParalelo($row->paralelo_principal),
                                        'estado' => 'EN_PROCESO'
                                    ]);


                                    $periodoAcademico = PeriodoAcademico::where('id', $this->changePeriodo($row->periodo_academico_principal))
                                        ->first();
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_principal))->first();

                                    $matricula->estudiante()->associate($estudiante);

                                    $matricula->periodo_lectivo()->associate($periodoLectivo);
                                    $matricula->periodo_academico()->associate($periodoAcademico);
                                    $matricula->malla()->associate($malla);
                                    $matricula->tipo_matricula()->associate($tipoMatricula);
                                    $matricula->save();

                                    $matricula->informacion_estudiantes()->create([
                                        'ha_repetido_asignatura' => $this->changeRespuestaSiNo($row->ha_repetido_asignatura),
                                        'ha_perdido_gratuidad' => $this->changeRespuestaSiNo($row->ha_perdido_gratuidad)
                                    ]);

                                    $detalleMatriculas = new DetalleMatriculaTransaccion([
                                        'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
                                        'numero_matricula' => $this->changeNumeroMatricula($row->numero_matricula),
                                        'jornada' => $this->changeJornada($row->jornada_asignatura),
                                        'estado' => 'EN_PROCESO'
                                    ]);

                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))->first();
                                    $detalleMatriculas->matricula()->associate($matricula);
                                    $detalleMatriculas->asignatura()->associate($asignatura);
                                    $detalleMatriculas->tipo_matricula()->associate($tipoMatricula);
                                    $detalleMatriculas->save();
                                    $countCuposNuevos++;
                                    $errors['estudiante'][] =
                                        'Fila: ' . ($i + 1)
                                        . ' Cédula Estudiante: '
                                        . $row->cedula_estudiante . ' nuevo estudiante agregado '
                                        . $row->apellido1 . ' ' . $row->apellido2 . ' ' . $row->nombre1 . ' ' . $row->nombre2;
                                } else {
                                    $errors['estudiante'][] =
                                        'Fila: ' . ($i + 1) . ' - '
                                        . ' la asignatura: ' . $row->codigo_asignatura . ' no pudo ser cargada, del estudiante: '
                                        . $row->apellido1 . ' ' . $row->apellido2 . ' ' . $row->nombre1 . ' ' . $row->nombre2
                                        . ' (' . $row->cedula_estudiante . ') '
                                        . ' el correo: ' . $row->correo_institucional
                                        . ': ya se encuentra registrado con la cedula: ' . $correo->identificacion;
                                }
                            }
                            if (!$asignatura) {
                                $errors['asignaturas'][] =
                                    'Fila: ' . ($i + 1)
                                    . ' codigo_asignatura: ' . $row->codigo_asignatura
                                    . ': no existe';
                            }
                        }
                        $i++;
                        DB::commit();
                    } catch (QueryException $e) {
                        return $e;
                    }
                }
            });
            Storage::delete($pathFile);
//            return response()->json(['respuesta' => $response]);
//            return response()->json($aux);
            return response()->json([
                'errores' => $errors,
                'registros' => $i,
                'total_cupos_nuevos' => $countCuposNuevos,
                'total_cupos_modificados' => $countCuposModificados
            ], 200);
        } else {
            return response()->json([
                'errores' => 'Archivo no valido',
                'registros' => 0,
                'total_estudiantes' => 0,
                'total_asignaturas' => 0
            ], 500);
        }

    }

    public function importCuposR(Request $request)
    {
        if ($request->file('archivo')) {
            $aux = 'SN';
            $errors = array();
            $pathFile = $request->file('archivo')->store('public/archivos');
            $path = storage_path() . '/app/' . $pathFile;
            $i = 0;
            $countCuposNuevos = 0;
            $countCuposModificados = 0;

            $response = Excel::load($path, function ($reader)
            use ($request, &$errors, $i, &$countCuposNuevos, &$countCuposModificados, &$aux) {
                $now = Carbon::now();
                $identificacion = '';
                $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();
                $malla = Malla::where('carrera_id', $request->carrera_id)->first();

                foreach ($reader->get() as $row) {
                    try {
                        DB::beginTransaction();
                        $estudiante = Estudiante::where('identificacion', trim($row->cedula_estudiante))->first();

                        $estudianteCorreo = Estudiante::where('identificacion', trim($row->cedula_estudiante))
                            ->where('correo_institucional', trim($row->correo_institucional))->first();
                        $correo = Estudiante::where('correo_institucional', trim($row->correo_institucional))->first();
                        $asignatura = Asignatura::where('codigo', trim(strtoupper($row->codigo_asignatura)))
                            ->where('malla_id', $malla->id)
                            ->first();
                        if ($estudiante && $asignatura && $malla) {
                            $existeMatricula = MatriculaTransaccion::where('estudiante_id', $estudiante->id)
                                ->where('periodo_lectivo_id', $periodoLectivo->id)
                                ->first();

                            if (!$existeMatricula) {
                                $flagCampos = $this->validateCampos(
                                    $row->jornada_principal,
                                    $row->jornada_operativa,
                                    $row->paralelo_principal,
                                    $row->periodo_academico_principal,
                                    $row->tipo_matricula_principal,
                                    $row->paralelo_asignatura,
                                    $row->numero_matricula,
                                    $row->jornada_asignatura,
                                    $row->tipo_matricula_asignatura,
                                    $row->ha_repetido_asignatura,
                                    $row->ha_perdido_gratuidad);

                                if ($flagCampos != "") {
                                    $errors['estudiante'][] =
                                        'Fila: ' . ($i + 1)
                                        . ' Cédula Estudiante: '
                                        . $row->cedula_estudiante . ' Datos Incorrectos: ' . $flagCampos;
                                } else {
                                    $identificacion = strtoupper(trim($row->cedula_estudiante));

                                    $matriculaAnterior = MatriculaTransaccion::where('estudiante_id', $estudiante->id)
                                        ->where('estado', 'MATRICULADO')
                                        ->orderby('fecha', 'DESC')->first();

                                    $matricula = $this->matricularAntiguos($row, $estudiante, $asignatura, $periodoLectivo, $malla);
                                    if ($matriculaAnterior) {
                                        $informacionEstudiante = InformacionEstudianteTransaccion::
                                        where('matricula_id', $matriculaAnterior->id)->first();

                                        $informacionEstudianteActual = $matricula->informacion_estudiantes()->create([
                                            'monto_ayuda_economica' => $informacionEstudiante->monto_ayuda_economica,
                                            'monto_beca' => $informacionEstudiante->monto_beca,
                                            'monto_credito_educativo' => $informacionEstudiante->monto_credito_educativo,
                                            'porcentaje_discapacidad' => $informacionEstudiante->porcentaje_discapacidad,
                                            'porciento_beca_cobertura_arancel' => $informacionEstudiante->porciento_beca_cobertura_arancel,
                                            'porciento_beca_cobertura_manutencion' => $informacionEstudiante->porciento_beca_cobertura_manutencion,
                                            'horas_practicas' => $informacionEstudiante->horas_practicas,
                                            'numero_miembros_hogar' => $informacionEstudiante->numero_miembros_hogar,
                                            'alcance_vinculacion' => $informacionEstudiante->alcance_vinculacion,
                                            'area_trabajo_empresa' => $informacionEstudiante->area_trabajo_empresa,
                                            'categoria_migratoria' => $informacionEstudiante->categoria_migratoria,
                                            'codigo_postal' => $informacionEstudiante->codigo_postal,
                                            'contacto_emergencia_nombres' => $informacionEstudiante->contacto_emergencia_nombres,
                                            'contacto_emergencia_telefono' => $informacionEstudiante->contacto_emergencia_telefono,
                                            'contacto_emergencia_parentesco' => $informacionEstudiante->contacto_emergencia_parentesco,
                                            'destino_ingreso' => $informacionEstudiante->destino_ingreso,
                                            'direccion' => $informacionEstudiante->direccion,
                                            'estado_civil' => $informacionEstudiante->estado_civil,
                                            'ha_realizado_practicas' => $informacionEstudiante->ha_realizado_practicas,
                                            'ha_realizado_vinculacion' => $informacionEstudiante->ha_realizado_vinculacion,
                                            'habla_idioma_ancestral' => $informacionEstudiante->habla_idioma_ancestral,
                                            'idioma_ancestral' => $informacionEstudiante->idioma_ancestral,
                                            'nivel_formacion_padre' => $informacionEstudiante->nivel_formacion_padre,
                                            'nivel_formacion_madre' => $informacionEstudiante->nivel_formacion_madre,
                                            'nombre_empresa_labora' => $informacionEstudiante->nombre_empresa_labora,
                                            'numero_carnet_conadis' => $informacionEstudiante->numero_carnet_conadis,
                                            'ocupacion' => $informacionEstudiante->ocupacion,
                                            'pension_diferenciada' => $informacionEstudiante->pension_diferenciada,
                                            'posee_titulo_superior' => $informacionEstudiante->posee_titulo_superior,
                                            'razon_beca1' => $informacionEstudiante->razon_beca1,
                                            'razon_beca2' => $informacionEstudiante->razon_beca2,
                                            'razon_beca3' => $informacionEstudiante->razon_beca3,
                                            'razon_beca4' => $informacionEstudiante->razon_beca4,
                                            'razon_beca5' => $informacionEstudiante->razon_beca5,
                                            'razon_beca6' => $informacionEstudiante->razon_beca6,
                                            'sector_economico_practica' => $informacionEstudiante->sector_economico_practica,
                                            'telefono_celular' => $informacionEstudiante->telefono_celular,
                                            'telefono_fijo' => $informacionEstudiante->telefono_fijo,
                                            'tiene_discapacidad' => $informacionEstudiante->tiene_discapacidad,
                                            'tipo_beca' => $informacionEstudiante->tipo_beca,
                                            'tipo_discapacidad' => $informacionEstudiante->tipo_discapacidad,
                                            'tipo_financiamiento_beca' => $informacionEstudiante->tipo_financiamiento_beca,
                                            'tipo_institucion_practicas' => $informacionEstudiante->tipo_institucion_practicas,
                                            'titulo_superior_obtenido' => $informacionEstudiante->titulo_superior_obtenido,
                                            'recibe_bono_desarrollo' => $informacionEstudiante->recibe_bono_desarrollo,
                                            'ingreso_familiar' => $informacionEstudiante->ingreso_familiar
                                        ]);
                                        $matricula->informacion_estudiantes()->update([
                                            'ha_repetido_asignatura' => $this->changeRespuestaSiNo($row->ha_repetido_asignatura),
                                            'ha_perdido_gratuidad' => $this->changeRespuestaSiNo($row->ha_perdido_gratuidad),
                                        ]);
                                        $cantonResidencia = Ubicacion::findOrFail($informacionEstudiante->canton_residencia_id);
                                        $informacionEstudianteActual->canton_residencia()->associate($cantonResidencia);
                                        $informacionEstudianteActual->save();

                                    } else {
                                        $matricula->informacion_estudiantes()->create([
                                            'ha_repetido_asignatura' => $this->changeRespuestaSiNo($row->ha_repetido_asignatura),
                                            'ha_perdido_gratuidad' => $this->changeRespuestaSiNo($row->ha_perdido_gratuidad)
                                        ]);
                                    }
                                    $countCuposNuevos++;
                                }
                            } else if ($existeMatricula->estado == 'EN_PROCESO') {
                                if ($identificacion != $row->cedula_estudiante) {
                                    $identificacion = $row->cedula_estudiante;
                                    $existeMatricula->update([
                                        'fecha' => $now,
                                        'jornada' => $this->changeJornada($row->jornada_principal),
                                        'jornada_operativa' => $this->changeJornada($row->jornada_operativa),
                                        'paralelo_principal' => $this->changeParalelo($row->paralelo_principal),
                                        'estado' => 'EN_PROCESO'
                                    ]);
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_principal))->first();
                                    $periodoAcademico = PeriodoAcademico::where('id', $this->changePeriodo($row->periodo_academico_principal))->first();
                                    $existeMatricula->estudiante()->associate($estudiante);
                                    $existeMatricula->periodo_lectivo()->associate($periodoLectivo);
                                    $existeMatricula->tipo_matricula()->associate($tipoMatricula);
                                    $existeMatricula->periodo_academico()->associate($periodoAcademico);
                                    $existeMatricula->malla()->associate($malla);
                                    $existeMatricula->save();
                                    $countCuposModificados++;
                                }

                                $existeDetalleMatricula = DetalleMatriculaTransaccion::where('asignatura_id', $asignatura->id)
                                    ->where('matricula_id', $existeMatricula->id)->first();

                                if ($existeDetalleMatricula) {
                                    $existeDetalleMatricula->update([
                                        'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
                                        'numero_matricula' => $this->changeNumeroMatricula($row->numero_matricula),
                                        'jornada' => $this->changeJornada($row->jornada_asignatura)
                                    ]);
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))
                                        ->first();
                                    $existeDetalleMatricula->matricula()->associate($existeMatricula);
                                    $existeDetalleMatricula->asignatura()->associate($asignatura);
                                    $existeDetalleMatricula->tipo_matricula()->associate($tipoMatricula);
                                    $existeDetalleMatricula->save();
                                } else {
                                    $detalleMatriculas = new DetalleMatriculaTransaccion([
                                        'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
                                        'numero_matricula' => $this->changeNumeroMatricula($row->numero_matricula),
                                        'jornada' => $this->changeJornada($row->jornada_asignatura),
                                        'estado' => 'EN_PROCESO'
                                    ]);
                                    $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))
                                        ->first();
                                    $detalleMatriculas->matricula()->associate($existeMatricula);
                                    $detalleMatriculas->asignatura()->associate($asignatura);
                                    $detalleMatriculas->tipo_matricula()->associate($tipoMatricula);
                                    $detalleMatriculas->save();
                                }

                            }
                        } else {
                            if ($row->cedula_estudiante != '') {
                                if (!$correo) {
                                    $usuario = new User([
                                        'name' => strtoupper(trim($row->apellido1) . ' ' . trim($row->nombre1)),
                                        'user_name' => strtoupper(trim($row->cedula_estudiante)),
                                        'email' => strtolower(trim($row->correo_institucional)),
                                        'password' => Hash::make(trim($row->cedula_estudiante)),
                                    ]);

                                    $rol = Role::findOrFail(2);// el 2 es el role del estudiante
                                    $usuario->role()->associate($rol);
                                    $usuario->save();

                                    $usuario->carreras()->attach($request->carrera_id);

                                    $estudiante = $usuario->estudiante()->create([
                                        'tipo_identificacion' => trim($row->tipo_identificacion),
                                        'identificacion' => trim($row->cedula_estudiante),
                                        'apellido1' => strtoupper(trim($row->apellido1)),
                                        'apellido2' => strtoupper(trim($row->apellido2)),
                                        'nombre1' => strtoupper(trim($row->nombre1)),
                                        'nombre2' => strtoupper(trim($row->nombre2)),
                                        'correo_institucional' => strtolower(trim($row->correo_institucional)),
                                        'fecha_nacimiento' => trim($row->fecha_nacimiento),
                                        'fecha_inicio_carrera' => trim($row->fecha_inicio_carrera)
                                    ]);

                                    $this->matricularNuevos($row, $estudiante, $asignatura, $periodoLectivo, $malla);
                                    $countCuposNuevos++;
                                    $errors['estudiante'][] =
                                        'Fila: ' . ($i + 1)
                                        . ' Cédula Estudiante: '
                                        . $row->cedula_estudiante . ' nuevo estudiante agregado '
                                        . $row->apellido1 . ' ' . $row->apellido2 . ' ' . $row->nombre1 . ' ' . $row->nombre2;
                                } else {
                                    $errors['estudiante'][] =
                                        'Fila: ' . ($i + 1) . ' - '
                                        . ' la asignatura: ' . $row->codigo_asignatura . ' no pudo ser cargada, del estudiante: '
                                        . $row->apellido1 . ' ' . $row->apellido2 . ' ' . $row->nombre1 . ' ' . $row->nombre2
                                        . ' (' . $row->cedula_estudiante . ') '
                                        . ' el correo: ' . $row->correo_institucional
                                        . ': ya se encuentra registrado con la cedula: ' . $correo->identificacion;
                                }
                            }
                            if (!$asignatura) {
                                $errors['asignaturas'][] =
                                    'Fila: ' . ($i + 1)
                                    . ' codigo_asignatura: ' . $row->codigo_asignatura
                                    . ': no existe';
                            }
                        }
                        $i++;
                        DB::commit();
                    } catch (QueryException $e) {
                        return $e;
                    }
                }
            });
            Storage::delete($pathFile);
//            return response()->json(['respuesta' => $response]);
            // return response()->json($aux);
            return response()->json([
                'errores' => $errors,
                'registros' => $i,
                'total_cupos_nuevos' => $countCuposNuevos,
                'total_cupos_modificados' => $countCuposModificados
            ], 200);
        } else {
            return response()->json([
                'errores' => 'Archivo no valido',
                'registros' => 0,
                'total_estudiantes' => 0,
                'total_asignaturas' => 0
            ], 500);
        }

    }

    private function changeJornada($jornada)
    {
        $jornada = strtoupper(trim($jornada));
        $jornadas = array("MATUTINA", "VESPERTINA", "NOCTURNA", "INTENSIVA", "POR_DEFINIR");
        $indice = array_search($jornada, $jornadas, false);
        if ($indice === false) {
            return '';
        } else {
            return $indice + 1;
        }
    }

    private function changePeriodo($periodo)
    {
        $periodo = strtoupper(trim($periodo));
        $periodosNumeros = array("1", "2", "3", "4", "5", "6");
        $periodosLetras = array("PRIMERO", "SEGUNDO", "TERCERO", "CUARTO", "QUINTO", "SEXTO");
        $indiceNumeros = array_search($periodo, $periodosNumeros, false);
        $indiceLetras = array_search($periodo, $periodosLetras, false);
        if ($indiceNumeros !== false) {
            return $indiceNumeros + 1;
        } else if ($indiceLetras !== false) {
            return $indiceLetras + 1;
        } else {
            return '';
        }
    }

    private function changeNumeroMatricula($numeroMatricula)
    {
        $numeroMatricula = strtoupper(trim($numeroMatricula));
        $numerosMatricula = array('PRIMERA', 'SEGUNDA', 'TERCERA');
        $indice = array_search($numeroMatricula, $numerosMatricula, false);
        if ($indice === false) {
            return '';
        } else {
            return $indice + 1;
        }
    }

    private function changeParalelo($paralelo)
    {
        $paralelo = strtoupper(trim($paralelo));
        $paralelos = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T');
        $indice = array_search($paralelo, $paralelos, false);
        if ($indice === false) {
            return '';
        } else {
            return $indice + 1;
        }
    }

    private function changeParaleloToLetra($paralelo)
    {
        $paralelo = strtoupper(trim($paralelo));
        $paralelosLetras = array('1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'F', '6' => 'G', '7' => 'H', '8' => 'I');
        return $paralelosLetras[$paralelo];

    }

    private function changeTipoMatricula($tipoMatricula)
    {
        $tipoMatricula = strtoupper(trim($tipoMatricula));
        $tiposMatricula = array('ORDINARIA', 'EXTRAORDINARIA', 'ESPECIAL');
        $indice = array_search($tipoMatricula, $tiposMatricula, false);

        if ($indice === false) {
            return '';
        } else {
            return $indice + 1;
        }
    }

    private function changeRespuestaSiNo($respuesta)
    {
        $periodo = strtoupper(trim($respuesta));
        $respuestasNumeros = array("1", "2", "3", "4", "5", "6");
        $respuestasLetras = array("SI", "NO");
        $indiceNumeros = array_search($periodo, $respuestasNumeros, false);
        $indiceLetras = array_search($periodo, $respuestasLetras, false);
        if ($indiceNumeros !== false) {
            return $indiceNumeros + 1;
        } else if ($indiceLetras !== false) {
            return $indiceLetras + 1;
        } else {
            return '';
        }
    }

    /**
     * @param $jornada_principal
     * @param $jornada_operativa
     * @param $paralelo_principal
     * @param $periodo_academico_principal
     * @param $tipo_matricula_principal
     * @param $paralelo_asignatura
     * @param $numero_matricula
     * @param $jornada_asignatura
     * @param $tipo_matricula_asignatura
     * @param $ha_repetido_asignatura
     * @param $ha_perdido_gratuidad
     * @return string
     */
    private function validateCampos($jornada_principal, $jornada_operativa, $paralelo_principal
        , $periodo_academico_principal, $tipo_matricula_principal, $paralelo_asignatura, $numero_matricula
        , $jornada_asignatura, $tipo_matricula_asignatura, $ha_repetido_asignatura, $ha_perdido_gratuidad)
    {
        $errores = '';
        if ($this->changeJornada($jornada_principal) == '') {
            $errores = " - jornada_principal";
        }

        if ($this->changeJornada($jornada_operativa) == '') {
            $errores .= " - jornada_operativa";
        }

        if ($this->changeParalelo($paralelo_principal) == '') {
            $errores .= " - paralelo_principal";
        }

        if ($this->changePeriodo($periodo_academico_principal) == '') {
            $errores .= " - periodo_academico_principal";
        }

        if ($this->changeTipoMatricula($tipo_matricula_principal) == '') {
            $errores .= " - tipo_matricula_principal";
        }

        if ($this->changeParalelo($paralelo_asignatura) == '') {
            $errores .= " - paralelo_asignatura";
        }

        if ($this->changeNumeroMatricula($numero_matricula) == '') {
            $errores .= " - numero_matricula";
        }

        if ($this->changeJornada($jornada_asignatura) == '') {
            $errores .= " - jornada_asignatura";
        }

        if ($this->changeTipoMatricula($tipo_matricula_asignatura) == '') {
            $errores .= " - tipo_matricula_asignatura";
        }

        if ($this->changeRespuestaSiNo($ha_repetido_asignatura) == '') {
            $errores .= " - ha_repetido_asignatura";
        }

        if ($this->changeRespuestaSiNo($ha_perdido_gratuidad) == '') {
            $errores .= " - ha_perdido_gratuidad";
        }
        return $errores;
    }

    private function matricularNuevos($row, $estudiante, $asignatura, $periodoLectivo, $malla)
    {
        $now = Carbon::now();
        $matricula = new MatriculaTransaccion([
            'fecha' => $now,
            'jornada' => $this->changeJornada($row->jornada_principal),
            'jornada_operativa' => $this->changeJornada($row->jornada_operativa),
            'paralelo_principal' => $this->changeParalelo($row->paralelo_principal),
            'estado' => 'EN_PROCESO'
        ]);

        $periodoAcademico = PeriodoAcademico::where('id', $this->changePeriodo($row->periodo_academico_principal))->first();
        $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_principal))->first();

        $matricula->estudiante()->associate($estudiante);
        $matricula->periodo_lectivo()->associate($periodoLectivo);
        $matricula->periodo_academico()->associate($periodoAcademico);
        $matricula->malla()->associate($malla);
        $matricula->tipo_matricula()->associate($tipoMatricula);
        $matricula->save();

        $matricula->informacion_estudiantes()->create([
            'ha_repetido_asignatura' => $this->changeRespuestaSiNo($row->ha_repetido_asignatura),
            'ha_perdido_gratuidad' => $this->changeRespuestaSiNo($row->ha_perdido_gratuidad)
        ]);

        $detalleMatriculas = new DetalleMatriculaTransaccion([
            'paralelo' => $this->changeParalelo($row->paralelo_asignatura),
            'numero_matricula' => $this->changeNumeroMatricula($row->numero_matricula),
            'jornada' => $this->changeJornada($row->jornada_asignatura),
            'estado' => 'EN_PROCESO'
        ]);

        $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))->first();
        $detalleMatriculas->matricula()->associate($matricula);
        $detalleMatriculas->asignatura()->associate($asignatura);
        $detalleMatriculas->tipo_matricula()->associate($tipoMatricula);
        $detalleMatriculas->save();
    }

    private function matricularAntiguos($row, $estudiante, $asignatura, $periodoLectivo, $malla)
    {
        $now = Carbon::now();
        $matricula = new MatriculaTransaccion([
            'fecha' => $now,
            'jornada' => $this->changeJornada($row->jornada_principal),
            'jornada_operativa' => $this->changeJornada($row->jornada_operativa),
            'paralelo_principal' => $this->changeParalelo($row->paralelo_principal),
            'estado' => 'EN_PROCESO'
        ]);

        $periodoAcademico = PeriodoAcademico::where('id', $this->changePeriodo($row->periodo_academico_principal))->first();
        $tipoMatricula = TipoMatricula::where('id',
            $this->changeTipoMatricula($row->tipo_matricula_principal))->first();

        $matricula->estudiante()->associate($estudiante);
        $matricula->periodo_lectivo()->associate($periodoLectivo);
        $matricula->periodo_academico()->associate($periodoAcademico);
        $matricula->malla()->associate($malla);
        $matricula->tipo_matricula()->associate($tipoMatricula);
        $matricula->save();

        $detalleMatriculas = new DetalleMatriculaTransaccion([
            'paralelo' => $this->changeParalelo(trim($row->paralelo_asignatura)),
            'numero_matricula' => $this->changeNumeroMatricula(trim($row->numero_matricula)),
            'jornada' => $this->changeJornada(trim($row->jornada_asignatura)),
            'estado' => 'EN_PROCESO'
        ]);

        $tipoMatricula = TipoMatricula::where('id', $this->changeTipoMatricula($row->tipo_matricula_asignatura))->first();
        $detalleMatriculas->matricula()->associate($matricula);
        $detalleMatriculas->asignatura()->associate($asignatura);
        $detalleMatriculas->tipo_matricula()->associate($tipoMatricula);
        $detalleMatriculas->save();

        return $matricula;
    }

    public function exportListasPeriodo(Request $request)
    {
        $periodoLectivo = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $carrera = Carrera::findOrFail($request->carrera_id);
        $asignaturas = Asignatura::where('malla_id', $malla->id)
            ->where('periodo_academico_id', $request->periodo_academico_id)
            ->where('tipo', 'ASIGNATURA')
            ->orderBy('nombre')
            ->get();
        $paralelos = Matricula::selectRaw('distinct(detalle_matriculas.paralelo)')
            ->join('detalle_matriculas', 'detalle_matriculas.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->where('matriculas.periodo_academico_id', $request->periodo_academico_id)
            ->where('matriculas.estado', 'MATRICULADO')
            ->orderBy('detalle_matriculas.paralelo')
            ->get();
        $lista = Matricula::selectRaw("
                        asignaturas.codigo as codigo_asignatura,
                        asignaturas.nombre as nombre_asignatura,
                         (CASE
                            WHEN detalle_matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN detalle_matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN detalle_matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN detalle_matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN detalle_matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_asignatura,
                             (CASE
                            WHEN matriculas.jornada_operativa = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada_operativa = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada_operativa = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada_operativa = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada_operativa = '5' THEN 'POR_DEFINIR' END) AS jornada_operativa,
                         (CASE
                            WHEN detalle_matriculas.numero_matricula = '1' THEN 'PRIMERA'
                            WHEN detalle_matriculas.numero_matricula = '2' THEN 'SEGUNDA'
                            WHEN detalle_matriculas.numero_matricula = '3' THEN 'TERCERA' END) AS numero_matricula,

                        estudiantes.apellido1||' '||estudiantes.apellido2||' ' ||estudiantes.nombre1 || ' ' ||estudiantes.nombre2
                         AS ESTUDIANTE,
                            estudiantes.identificacion as cedula_estudiante,
                            informacion_estudiantes.telefono_celular,
                            estudiantes.correo_institucional,
                            (CASE
                            WHEN estudiantes.sexo = '1' THEN 'HOMBRE'
                            WHEN estudiantes.sexo = '2' THEN 'MUJER' END) AS sexo,
                             (CASE
                            WHEN detalle_matriculas.paralelo = '1' THEN 'A'
                            WHEN detalle_matriculas.paralelo = '2' THEN 'B'
                            WHEN detalle_matriculas.paralelo = '3' THEN 'C'
                            WHEN detalle_matriculas.paralelo = '4' THEN 'D'
                            WHEN detalle_matriculas.paralelo = '5' THEN 'E'
                            WHEN detalle_matriculas.paralelo = '6' THEN 'F'
                            WHEN detalle_matriculas.paralelo = '7' THEN 'G'
                            WHEN detalle_matriculas.paralelo = '8' THEN 'H'
                            WHEN detalle_matriculas.paralelo = '9' THEN 'I'
                            WHEN detalle_matriculas.paralelo = '10' THEN 'J'
                            WHEN detalle_matriculas.paralelo = '11' THEN 'K'
                            WHEN detalle_matriculas.paralelo = '12' THEN 'L'
                            WHEN detalle_matriculas.paralelo = '13' THEN 'M'
                            WHEN detalle_matriculas.paralelo = '14' THEN 'N' END) AS paralelo_asignatura,
                             (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal
                            ")
            ->join('detalle_matriculas', 'detalle_matriculas.matricula_id', '=', 'matriculas.id')
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->join('asignaturas', 'asignaturas.id', '=', 'detalle_matriculas.asignatura_id')
            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'detalle_matriculas.tipo_matricula_id')
            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.malla_id', $malla->id)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
            ->where('matriculas.periodo_academico_id', $request->periodo_academico_id)
            ->where('detalle_matriculas.asignatura_id', 670)
            ->where('detalle_matriculas.paralelo', '1')
            ->where('matriculas.estado', 'MATRICULADO')
            ->orderBy('estudiantes.apellido1')
            ->get();

        Excel::create($request->periodo_academico_id . ' LISTAS - ' . $carrera->descripcion . ' (' . $periodoLectivo->codigo . ') ',
            function ($excel) use ($request, $malla, $asignaturas, $paralelos) {
                foreach ($asignaturas as $asignatura) {
                    foreach ($paralelos as $paralelo) {
                        $lista = Matricula::selectRaw("
                        asignaturas.codigo as codigo_asignatura,
                        asignaturas.nombre as nombre_asignatura,
                         (CASE
                            WHEN detalle_matriculas.jornada = '1' THEN 'MATUTINA'
                            WHEN detalle_matriculas.jornada = '2' THEN 'VESPERTINA'
                            WHEN detalle_matriculas.jornada = '3' THEN 'NOCTURNA'
                            WHEN detalle_matriculas.jornada = '4' THEN 'INTENSIVA'
                            WHEN detalle_matriculas.jornada = '5' THEN 'POR_DEFINIR' END) AS jornada_asignatura,
                             (CASE
                            WHEN matriculas.jornada_operativa = '1' THEN 'MATUTINA'
                            WHEN matriculas.jornada_operativa = '2' THEN 'VESPERTINA'
                            WHEN matriculas.jornada_operativa = '3' THEN 'NOCTURNA'
                            WHEN matriculas.jornada_operativa = '4' THEN 'INTENSIVA'
                            WHEN matriculas.jornada_operativa = '5' THEN 'POR_DEFINIR' END) AS jornada_operativa,
                         (CASE
                            WHEN detalle_matriculas.numero_matricula = '1' THEN 'PRIMERA'
                            WHEN detalle_matriculas.numero_matricula = '2' THEN 'SEGUNDA'
                            WHEN detalle_matriculas.numero_matricula = '3' THEN 'TERCERA' END) AS numero_matricula,

                        estudiantes.apellido1||' '||estudiantes.apellido2||' ' ||estudiantes.nombre1 || ' ' ||estudiantes.nombre2
                         AS ESTUDIANTE,
                            estudiantes.identificacion as cedula_estudiante,
                            informacion_estudiantes.telefono_celular,
                            estudiantes.correo_institucional,
                            (CASE
                            WHEN estudiantes.sexo = '1' THEN 'HOMBRE'
                            WHEN estudiantes.sexo = '2' THEN 'MUJER' END) AS sexo,
                             (CASE
                            WHEN detalle_matriculas.paralelo = '1' THEN 'A'
                            WHEN detalle_matriculas.paralelo = '2' THEN 'B'
                            WHEN detalle_matriculas.paralelo = '3' THEN 'C'
                            WHEN detalle_matriculas.paralelo = '4' THEN 'D'
                            WHEN detalle_matriculas.paralelo = '5' THEN 'E'
                            WHEN detalle_matriculas.paralelo = '6' THEN 'F'
                            WHEN detalle_matriculas.paralelo = '7' THEN 'G'
                            WHEN detalle_matriculas.paralelo = '8' THEN 'H'
                            WHEN detalle_matriculas.paralelo = '9' THEN 'I'
                            WHEN detalle_matriculas.paralelo = '10' THEN 'J'
                            WHEN detalle_matriculas.paralelo = '11' THEN 'K'
                            WHEN detalle_matriculas.paralelo = '12' THEN 'L'
                            WHEN detalle_matriculas.paralelo = '13' THEN 'M'
                            WHEN detalle_matriculas.paralelo = '14' THEN 'N' END) AS paralelo_asignatura,
                             (CASE
                            WHEN matriculas.paralelo_principal = '1' THEN 'A'
                            WHEN matriculas.paralelo_principal = '2' THEN 'B'
                            WHEN matriculas.paralelo_principal = '3' THEN 'C'
                            WHEN matriculas.paralelo_principal = '4' THEN 'D'
                            WHEN matriculas.paralelo_principal = '5' THEN 'E'
                            WHEN matriculas.paralelo_principal = '6' THEN 'F'
                            WHEN matriculas.paralelo_principal = '7' THEN 'G'
                            WHEN matriculas.paralelo_principal = '8' THEN 'H'
                            WHEN matriculas.paralelo_principal = '9' THEN 'I'
                            WHEN matriculas.paralelo_principal = '10' THEN 'J'
                            WHEN matriculas.paralelo_principal = '11' THEN 'K'
                            WHEN matriculas.paralelo_principal = '12' THEN 'L'
                            WHEN matriculas.paralelo_principal = '13' THEN 'M'
                            WHEN matriculas.paralelo_principal = '14' THEN 'N' END) AS paralelo_principal
                            ")
                            ->join('detalle_matriculas', 'detalle_matriculas.matricula_id', '=', 'matriculas.id')
                            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                            ->join('asignaturas', 'asignaturas.id', '=', 'detalle_matriculas.asignatura_id')
                            ->join('mallas', 'mallas.id', '=', 'matriculas.malla_id')
                            ->join('carreras', 'carreras.id', '=', 'mallas.carrera_id')
                            ->join('tipo_matriculas', 'tipo_matriculas.id', '=', 'detalle_matriculas.tipo_matricula_id')
                            ->join('informacion_estudiantes', 'informacion_estudiantes.matricula_id', '=', 'matriculas.id')
                            ->where('matriculas.malla_id', $malla->id)
                            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)
                            ->where('matriculas.periodo_academico_id', $request->periodo_academico_id)
                            ->where('detalle_matriculas.asignatura_id', $asignatura['id'])
                            ->where('detalle_matriculas.paralelo', $paralelo['paralelo'])
                            ->where('matriculas.estado', 'MATRICULADO')
                            ->orderBy('estudiantes.apellido1')
                            ->get();
                        if (sizeof($lista) != 0) {
                            $excel->sheet($this->changeParaleloToLetra($paralelo['paralelo']) . ' '
                                . substr($asignatura['nombre'], 0, 25), function ($sheet)
                            use ($lista) {
                                $sheet->fromArray($lista);
                            });
                        }
                    }
                }
            })->download('xlsx');
    }
}

