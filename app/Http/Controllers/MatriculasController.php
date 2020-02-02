<?php

namespace App\Http\Controllers;

use App\Asignatura;
use App\Carrera;
use App\DetalleMatricula;
use App\Estudiante;
use App\InformacionEstudiante;
use App\Instituto;
use App\Malla;
use App\Matricula;
use App\PeriodoAcademico;
use App\PeriodoLectivo;
use App\TipoMatricula;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;


class MatriculasController extends Controller
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

    public function getCountMatriculas(Request $request)
    {
        $matriculadosCount = DB::select
        ("select
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
       sum(case when m.estado = 'ANULADO' and m.periodo_academico_id = 6 then 1 else 0 end) anulados_6,
    c.id as carrera_id,
    c.nombre as carrera,
    c.descripcion as malla,
    m.malla_id
from MATRICULAS m
	inner join mallas ma on ma.id = m.malla_id
    inner join carreras c on c.id = ma.carrera_id
    inner join carrera_user cu on cu.carrera_id = c.id
    inner join users u on cu.user_id = u.id
where m.periodo_lectivo_id = " . $request->periodo_lectivo_id . "
  and cu.user_id=" . $request->id . "
	group by c.id,c.nombre,c.descripcion, m.malla_id
    order by malla");

        $matriculadosInstitutoCount = DB::select
        ("select
	sum(case when m.estado = 'MATRICULADO' then 1 else 0 end) total_matriculados,
       sum(case when m.estado = 'EN_PROCESO' then 1 else 0 end) total_en_proceso,
       sum(case when m.estado = 'APROBADO' then 1 else 0 end) total_aprobados,
       sum(case when m.estado = 'DESERTOR' then 1 else 0 end) total_desertores,
       sum(case when m.estado = 'ANULADO' then 1 else 0 end) total_anulados,
        i.id as instituto_id,
        i.nombre as instituto
        from MATRICULAS m
	inner join mallas ma on ma.id = m.malla_id
    inner join carreras c on c.id = ma.carrera_id
    inner join institutos i on i.id = c.instituto_id
    inner join carrera_user cu on cu.carrera_id = c.id
    inner join users u on cu.user_id = u.id
where m.periodo_lectivo_id = " . $request->periodo_lectivo_id . "
  and cu.user_id=" . $request->id . "
	group by i.id
    order by instituto");
        return response()->json(['matriculados_institutos_count' => $matriculadosInstitutoCount,
            'matriculados_carreras_count' => $matriculadosCount], 200);
    }

    public function getSolicitudMatricula(Request $request)
    {
        $estudiante = Estudiante::where('identificacion', $request->identificacion)->first();
        $periodoLectivoActual = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        if ($estudiante) {
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
        } else {
            return response()->json(['errorInfo' => ['404']], 404);
        }
        return response()->json(['solicitud' => $certificadoMatricula], 200);
    }

    public function getCertificadoMatricula(Request $request)
    {
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
            'detalle_matriculas.jornada as jornada_asignatura'

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
            ->where('matriculas.id', $request->matricula_id)
            ->where('matriculas.estado', 'MATRICULADO')
            ->where('detalle_matriculas.estado', 'MATRICULADO')
            ->orderby('asignaturas.periodo_academico_id')
            ->orderby('asignaturas.nombre')
            ->get();

        return response()->json(['certificado' => $certificadoMatricula], 200);
    }

    public function getCertificadoMatriculaPublic(Request $request)
    {
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
            'detalle_matriculas.numero_matricula as numero_matricula'
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
            ->where('matriculas.id', $request->matricula_id)
            ->orderBy('asignaturas.nombre')
            ->get();

        return view('certificado-matricula', ['certificado' => $certificadoMatricula]);
//        return $certificadoMatricula;
    }

    public function getAprobados(Request $request)
    {
        $carrera = Carrera::where('id', $request->carrera_id)->first();
        $malla = Malla::where('carrera_id', $carrera->id)->first();
        if ($request->periodo_academico_id) {
            $cupos = Matricula::select('estudiantes.apellido1', 'matriculas.*')
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where(function ($cupos) use (&$malla, &$request) {
                    $cupos->where('malla_id', $malla->id)
                        ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                        ->where('periodo_academico_id', $request->periodo_academico_id);
                })
                ->where(function ($cupos) {
                    $cupos->orWhere('matriculas.estado', 'APROBADO')
                        ->orWhere('matriculas.estado', 'MATRICULADO')
                        ->orWhere('matriculas.estado', 'NO_MATRICULADO')
                        ->orWhere('matriculas.estado', 'EN_PROCESO')
                        ->orWhere('matriculas.estado', 'DESERTOR')
                        ->orWhere('matriculas.estado', 'ANULADO');
                })
                //->orderby('matriculas.estado', 'ASC')
                ->orderby('apellido1')
                ->orderby('apellido1')
                ->paginate($request->records_per_page);
        } else {
            $cupos = Matricula::select('estudiantes.apellido1', 'matriculas.*')
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where(function ($cupos) use (&$malla, &$request) {
                    $cupos->where('malla_id', $malla->id)
                        ->where('periodo_lectivo_id', $request->periodo_lectivo_id);
                })
                ->where(function ($cupos) {
                    $cupos->where('matriculas.estado', 'APROBADO')
                        ->orWhere('matriculas.estado', 'MATRICULADO')
                        ->orWhere('matriculas.estado', 'NO_MATRICULADO')
                        ->orWhere('matriculas.estado', 'EN_PROCESO')
                        ->orWhere('matriculas.estado', 'DESERTOR')
                        ->orWhere('matriculas.estado', 'ANULADO');
                })
                //->orderby('matriculas.estado', 'ASC')
                ->orderby('apellido1')
                ->paginate($request->records_per_page);
        }

        return response()->json(['pagination' => [
            'total' => $cupos->total(),
            'current_page' => $cupos->currentPage(),
            'per_page' => $cupos->perPage(),
            'last_page' => $cupos->lastPage(),
            'from' => $cupos->firstItem(),
            'to' => $cupos->lastItem()
        ], 'cupos' => $cupos], 200);
    }

    public function getAprobado(Request $request)
    {
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        $cupo = Matricula::select('matriculas.*', 'carreras.nombre')
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
                $cupo->where('matriculas.malla_id', '=', $malla->id)
                    ->where('matriculas.periodo_lectivo_id', '=', $request->periodo_lectivo_id);
            })
            ->orderBy('apellido1')
            ->get();

        return response()->json(['cupo' => $cupo], 200);
    }

    public function getCupos(Request $request)
    {
        $carrera = Carrera::where('id', $request->carrera_id)->first();
        $malla = Malla::where('carrera_id', $carrera->id)->first();
        if ($request->periodo_academico_id) {
            $cupos = Matricula::select('estudiantes.apellido1', 'matriculas.*')
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where(function ($cupos) use (&$malla, &$request) {
                    $cupos->where('malla_id', $malla->id)
                        ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                        ->where('periodo_academico_id', $request->periodo_academico_id);
                })
                ->where(function ($cupos) use (&$request) {
                    $cupos->where('matriculas.estado', 'APROBADO')
                        ->orWhere('matriculas.estado', 'MATRICULADO')
                        ->orWhere('matriculas.estado', 'NO_MATRICULADO')
                        ->orWhere('matriculas.estado', 'EN_PROCESO')
                        ->orWhere('matriculas.estado', 'DESERTOR')
                        ->orWhere('matriculas.estado', 'ANULADO');
                })
                //->orderby('matriculas.estado', 'ASC')
                ->orderby('apellido1')
                ->paginate($request->records_per_page);
        } else {
            $cupos = Matricula::select('estudiantes.apellido1', 'matriculas.*')
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where(function ($cupos) use (&$malla, &$request) {
                    $cupos->where('malla_id', $malla->id)
                        ->where('periodo_lectivo_id', $request->periodo_lectivo_id);
                })
                ->where(function ($cupos) {
                    $cupos->where('matriculas.estado', 'APROBADO')
                        ->orWhere('matriculas.estado', 'MATRICULADO')
                        ->orWhere('matriculas.estado', 'NO_MATRICULADO')
                        ->orWhere('matriculas.estado', 'EN_PROCESO')
                        ->orWhere('matriculas.estado', 'DESERTOR')
                        ->orWhere('matriculas.estado', 'ANULADO');
                })
                //->orderby('matriculas.estado', 'ASC')
                ->orderby('apellido1')
                ->paginate($request->records_per_page);
        }

        return response()->json(['pagination' => [
            'total' => $cupos->total(),
            'current_page' => $cupos->currentPage(),
            'per_page' => $cupos->perPage(),
            'last_page' => $cupos->lastPage(),
            'from' => $cupos->firstItem(),
            'to' => $cupos->lastItem()
        ], 'cupos' => $cupos], 200);
    }

    public function getCuposPorEstado(Request $request)
    {
        $carrera = Carrera::where('id', $request->carrera_id)->first();
        $malla = Malla::where('carrera_id', $carrera->id)->first();
        if ($request->periodo_academico_id) {
            $cupos = Matricula::select('estudiantes.apellido1', 'matriculas.*')
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where(function ($cupos) use (&$malla, &$request) {
                    $cupos->where('malla_id', $malla->id)
                        ->where('periodo_lectivo_id', $request->periodo_lectivo_id)
                        ->where('periodo_academico_id', $request->periodo_academico_id);
                })
                ->where(function ($cupos) use (&$request) {
                    $cupos->where('matriculas.estado', $request->estado);
                })
                //->orderby('matriculas.estado', 'ASC')
                ->orderby('apellido1')
                ->paginate($request->records_per_page);
        } else {
            $cupos = Matricula::select('estudiantes.apellido1', 'matriculas.*')
                ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
                ->with('estudiante')
                ->with('periodo_academico')
                ->with('periodo_lectivo')
                ->with('tipo_matricula')
                ->where(function ($cupos) use (&$malla, &$request) {
                    $cupos->where('malla_id', $malla->id)
                        ->where('periodo_lectivo_id', $request->periodo_lectivo_id);
                })
                ->where(function ($cupos) use ($request) {
                    $cupos->where('matriculas.estado', $request->estado);
                })
                //->orderby('matriculas.estado', 'ASC')
                ->orderby('apellido1')
                ->paginate($request->records_per_page);
        }

        return response()->json(['pagination' => [
            'total' => $cupos->total(),
            'current_page' => $cupos->currentPage(),
            'per_page' => $cupos->perPage(),
            'last_page' => $cupos->lastPage(),
            'from' => $cupos->firstItem(),
            'to' => $cupos->lastItem()
        ], 'cupos' => $cupos], 200);
    }

    public function deleteCuposCarrera(Request $request)
    {
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $periodoLectioActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        if ($periodoLectioActual) {
            $cupos = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $periodoLectioActual->id)
                ->where('estado', 'EN_PROCESO')
                ->delete();
        }
        return response()->json(['cupos' => $cupos], 200);
    }

    public function deleteCuposPeriodo(Request $request)
    {
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $periodoLectioActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        if ($periodoLectioActual) {
            $cupos = Matricula::where('malla_id', $malla->id)
                ->where('periodo_lectivo_id', $periodoLectioActual->id)
                ->where('periodo_academico_id', $request->periodo_academico_id)
                ->where('estado', 'EN_PROCESO')
                ->delete();
        }
        return response()->json(['cupos' => $cupos], 200);
    }

    public function getCupo(Request $request)
    {
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $cupo = Matricula::select('matriculas.*')
            ->join('estudiantes', 'estudiantes.id', '=', 'matriculas.estudiante_id')
            ->with('estudiante')
            ->with('periodo_academico')
            ->with('periodo_lectivo')
            ->with('tipo_matricula')
            ->where(function ($cupo) use ($request) {
                $cupo->orWhere('apellido1', 'like', '%' . $request->apellido1 . '%')
                    ->orWhere('apellido2', 'like', '%' . $request->apellido2 . '%')
                    ->orWhere('nombre1', 'like', '%' . $request->nombre1 . '%')
                    ->orWhere('nombre2', 'like', '%' . $request->nombre2 . '%')
                    ->orWhere('identificacion', 'like', '%' . $request->identificacion . '%');
            })
            ->where(function ($cupo) use ($malla, $request) {
                $cupo->orwhere('matriculas.estado', '=', 'EN_PROCESO')
                    ->orwhere('matriculas.estado', '=', 'APROBADO')
                    ->orwhere('matriculas.estado', '=', 'MATRICULADO');
            })
            ->where(function ($cupo) use ($malla, $request) {
                $cupo->where('matriculas.malla_id', '=', $malla->id)
                    ->where('matriculas.periodo_lectivo_id', '=', $request->periodo_lectivo_id);
            })
            ->get();

        return response()->json(['cupo' => $cupo], 200);
    }

    public function getAsignaturasMalla(Request $request)
    {
        if ($request->carrera_id == null || $request->carrera_id == '0' || $request->carrera_id == '') {
            $matricula = Matricula::findOrFail($request->matricula_id);
            $malla = Malla::findOrFail($matricula->malla_id);
            $asignaturas = Asignatura::where('malla_id', $malla->id)->with('periodo_academico')->get();
            return response()->json(['asignaturas' => $asignaturas], 200);
        }
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $asignaturas = Asignatura::where('malla_id', $malla->id)->with('periodo_academico')
            ->orderBy('periodo_academico_id')->get();
        return response()->json(['asignaturas' => $asignaturas], 200);
    }

    public function updateMatricula(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->json()->all();
            $dataMatricula = $data['matricula'];
            $matricula = Matricula::findOrFail($dataMatricula['id']);
            if ($matricula->estado != 'EN_PROCESO' && $matricula->estado != 'ANULADO'
                && $matricula->estado != 'DESERTOR' && $matricula->estado != 'NO_MATRICULADO') {
                $matricula->update([
                    'jornada' => $dataMatricula['jornada'],
                    'jornada_operativa' => $dataMatricula['jornada_operativa'],
                ]);
                $matricula->detalle_matriculas()->update(['jornada' => $dataMatricula['jornada']]);
                $periodoAcademico = PeriodoAcademico::findOrFail($dataMatricula['periodo_academico']['id']);
                $tipoMatricula = TipoMatricula::findOrFail($dataMatricula['tipo_matricula']['id']);

                $matricula->periodo_academico()->associate($periodoAcademico);
                $matricula->tipo_matricula()->associate($tipoMatricula);
                $matricula->save();

                DB::commit();
                return response()->json(['matriculas' => $matricula], 201);
            } else {
                return response()->json(['matriculas' => $matricula], 500);
            }


        } catch (ModelNotFoundException $e) {
            return response()->json($e, 405);
        } catch (NotFoundHttpException  $e) {
            return response()->json($e, 405);
        } catch (PDOException $e) {
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
            $dataMatricula = $data['matricula'];
            $matricula = Matricula::findOrFail($dataMatricula['id']);
            if ($matricula->estado != 'MATRICULADO' && $matricula->estado != 'ANULADO'
                && $matricula->estado != 'DESERTOR') {
                $matricula->update([
                    'jornada' => $dataMatricula['jornada'],
                    'jornada_operativa' => $dataMatricula['jornada_operativa'],
                ]);
                $matricula->detalle_matriculas()->update(['jornada' => $dataMatricula['jornada']]);
                $periodoAcademico = PeriodoAcademico::findOrFail($dataMatricula['periodo_academico']['id']);
                $tipoMatricula = TipoMatricula::findOrFail($dataMatricula['tipo_matricula']['id']);

                $matricula->periodo_academico()->associate($periodoAcademico);
                $matricula->tipo_matricula()->associate($tipoMatricula);
                $matricula->save();

                DB::commit();
                return response()->json(['matriculas' => $matricula], 201);
            } else {
                return response()->json(['matriculas' => $matricula], 500);
            }


        } catch (ModelNotFoundException $e) {
            return response()->json($e, 405);
        } catch (NotFoundHttpException  $e) {
            return response()->json($e, 405);
        } catch (PDOException $e) {
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

    public function deleteDetalleCupo(Request $request)
    {
        $detalleMatricula = DetalleMatricula::findOrFail($request->id);
        if ($detalleMatricula->estado != 'MATRICULADO' && $detalleMatricula->estado != 'ANULADO'
            && $detalleMatricula->estado != 'DESERTOR') {
            $detalleMatricula->matricula()->update(['estado' => 'EN_PROCESO']);
            $detalleMatricula->delete();
        } else {
            return response()->json(['detalle_matricula' => $detalleMatricula], 500);
        }
    }

    public function deleteDetalleMatricula(Request $request)
    {
        DB::beginTransaction();
        $detalleMatricula = DetalleMatricula::findOrFail($request->id);
        $detalleMatricula->update(['estado' => 'ANULADO']);
        // $detalleMatricula->matricula()->update(['estado' => 'APROBADO']);
        DB::commit();
        return response()->json(['detalle_matricula' => $detalleMatricula], 201);
    }

    public function getMatriculasCarreras(Request $request)
    {
        //$data = $request->json()->all();
        $sql = "SELECT * FROM mallas WHERE estado <> 'INACTIVO' AND carrera_id = " . $request->carrera_id;
        $malla = DB::select($sql);
        $sql = "SELECT * FROM matriculas WHERE estado <> 'INACTIVO' AND malla_id = " . $malla[0]->id;
        $matriculas = DB::select($sql);
        return response()->json(['matriculas' => $matriculas], 200);
    }

    public function getMatriculasPeriodoAcademicos(Request $request)
    {
        //$data = $request->json()->all();
        $sql = "SELECT * FROM mallas WHERE estado <> 'INACTIVO' AND carrera_id = " . $request->carrera_id;
        $malla = DB::select($sql);
        $sql = "SELECT * FROM matriculas WHERE estado <> 'INACTIVO' AND malla_id =" . $malla[0]->id .
            "AND periodo_academico_id =" . $request->periodo_academico_id;
        $matriculas = DB::select($sql);
        return response()->json(['matriculas' => $matriculas], 200);
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();
        $dataEstudiante = $data['estudiante'];
        $dataInformacionEstudiante = $data['estudiante'];
        $parameters = [
            $dataEstudiante['pais_nacionalidad_id'],
            $dataEstudiante['pais_residencia_id'],
            $dataEstudiante['identificacion'],
            $dataEstudiante['nombre1'],
            $dataEstudiante['nombre2'],
            $dataEstudiante['apellido1'],
            $dataEstudiante['apellido2'],
            $dataEstudiante['fecha_nacimiento'],
            $dataEstudiante['correo_personal'],
            $dataEstudiante['correo_institucional'],
            $dataEstudiante['sexo'],
            $dataEstudiante['etnia'],
            $dataEstudiante['tipo_sangre'],
            $dataEstudiante['tipo_documento'],
            $dataEstudiante['tipo_colegio'],
        ];
        $sql = 'SELECT estudiantes.*
                FROM
                  matriculas inner join informacion_estudiantes on matriculas.id = informacion_estudiantes.matricula_id
	              inner join estudiantes on matriculas.estudiante_id = estudiantes.id
	            WHERE matriculas.periodo_lectivo_id = 1 and matriculas.estudiante_id =1';
        $estudiante = DB::select($sql, null);

        $sql = 'SELECT informacion_estudiantes.*
                FROM
                  matriculas inner join informacion_estudiantes on matriculas.id = informacion_estudiantes.matricula_id
	              inner join estudiantes on matriculas.estudiante_id = estudiantes.id
	            WHERE matriculas.periodo_lectivo_id = 1 and matriculas.estudiante_id =1';
        $informacionEstudiante = DB::select($sql, null);
        return response()->json([
            'estudiante' => $estudiante,
            'informacion_estudiante' => $informacionEstudiante
        ]);
    }

    public function deleteCupo(Request $request)
    {
        $matricula = Matricula::findOrFail($request->id);
        if ($matricula->estado != 'MATRICULADO' && $matricula->estado != 'ANULADO'
            && $matricula->estado != 'DESERTOR') {
            $matricula->delete();
            return response()->json(['detalle_matricula' => $matricula], 201);
        } else {
            return response()->json(['detalle_matricula' => $matricula], 500);
        }

    }

    public function desertMatricula(Request $request)
    {
        $matricula = Matricula::findOrFail($request->id);
        $matricula->update(['estado' => 'DESERTOR']);
        $matricula->detalle_matriculas()->update(['estado' => 'DESERTOR']);
        return response()->json(['detalle_matricula' => $matricula], 201);
    }

    public function unregisterMatricula(Request $request)
    {
        $matricula = Matricula::findOrFail($request->id);
        $matricula->update(['estado' => 'APROBADO']);
        $matricula->detalle_matriculas()->where('estado', 'MATRICULADO')->orWhere('estado', 'ANULADO')->update(['estado' => 'APROBADO']);
        return response()->json(['detalle_matricula' => $matricula], 201);
    }

    public function deleteMatricula(Request $request)
    {
        $matricula = Matricula::findOrFail($request->id);
        $matricula->update(['estado' => 'ANULADO']);
        $matricula->detalle_matriculas()->update(['estado' => 'ANULADO']);

        return response()->json(['detalle_matricula' => $matricula], 201);
    }

    public function validateCupo(Request $request)
    {
        $now = new Carbon();
        //date('F', strtotime($matricula->fecha))
        DB::beginTransaction();
        $matricula = Matricula::findOrFail($request->matricula_id);
        $periodoLectivo = PeriodoLectivo::findOrFail($matricula->periodo_lectivo_id);
        $estudiante = Estudiante::findOrFail($matricula->estudiante_id);
        $malla = Malla::findOrFail($matricula->malla_id);
        $carrera = $malla->carrera()->first();
        //esto es para matricula
        if ($matricula && $request->estado == 'MATRICULADO' && $request->estado != 'ANULADO') {
            $matricula->update([
                'fecha' => $now,
                'estado' => $request->estado,
                'codigo' => $periodoLectivo->codigo . '-' . $carrera->siglas . '-' . $estudiante->identificacion,
                'folio' => $periodoLectivo->codigo . '-' . $carrera->siglas
            ]);
            $matricula->detalle_matriculas()->where('estado', 'APROBADO')->update(['estado' => $request->estado]);
        } //esto es para aprobar el cupo
        else if ($request->estado != 'ANULADO') {
            $matricula->update([
                'codigo' => $periodoLectivo->codigo . '-' . $carrera->siglas . '-' . $estudiante->identificacion,
                'folio' => $periodoLectivo->codigo . '-' . $carrera->siglas,
                'estado' => $request->estado,
                'fecha' => $now
            ]);
            $matricula->detalle_matriculas()->update(['estado' => $request->estado]);
        }
        DB::commit();
        return response()->json(['matricula' => $matricula], 201);
    }

    public function validateCupoAsignatura(Request $request)
    {
        DB::beginTransaction();
        $detalleMatricula = DetalleMatricula::findOrFail($request->detalle_matricula_id);
        $matricula = Matricula::findOrFail($detalleMatricula->matricula_id);
        //esto es para aprobar el pago
        if ($detalleMatricula && $request->estado != 'ANULADO') {
            $detalleMatricula->update([
                'estado' => 'APROBADO',
            ]);

            $matricula->update([
                'estado' => 'APROBADO',
            ]);
        }
        DB::commit();
        return response()->json(['matricula' => $detalleMatricula], 201);
    }

    public function validateCuposCarrera(Request $request)
    {
        $now = new Carbon();
        DB::beginTransaction();
        $carrera = Carrera::findOrFail($request->carrera_id);
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        $matriculas = $malla->matriculas()->get();
        $i = 0;
        foreach ($matriculas as $matricula) {
            if ($matricula->estado == 'EN_PROCESO') {
                $estudiante = Estudiante::findOrFail($matricula->estudiante_id);
                $matricula->update([
                    'estado' => 'APROBADO',
                    'fecha' => $now,
                    'codigo' => $periodoLectivo->codigo . '-' . $carrera->siglas . '-' . $estudiante->identificacion,
                    'folio' => $periodoLectivo->codigo . '-' . $carrera->siglas]);
                $matricula->detalle_matriculas()->update(['estado' => 'APROBADO']);
            }

        }
        DB::commit();
        return response()->json(['matricula' => $matriculas], 201);
    }

    public function validateCuposPeriodoAcademico(Request $request)
    {
        $now = new Carbon();
        DB::beginTransaction();
        $carrera = Carrera::findOrFail($request->carrera_id);
        $malla = Malla::where('carrera_id', $request->carrera_id)->first();
        $periodoLectivo = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        $matriculas = $malla->matriculas()->where('periodo_academico_id', $request->periodo_academico_id)->get();
        foreach ($matriculas as $matricula) {
            if ($matricula->estado == 'EN_PROCESO') {
                $estudiante = Estudiante::findOrFail($matricula->estudiante_id);
                $matricula->update([
                    'estado' => 'APROBADO',
                    'fecha' => $now,
                    'codigo' => $periodoLectivo->codigo . '-' . $carrera->siglas . '-' . $estudiante->identificacion,
                    'folio' => $periodoLectivo->codigo . '-' . $carrera->siglas]);
                $matricula->detalle_matriculas()->update(['estado' => 'APROBADO']);
            }
        }
        DB::commit();
        return response()->json(['matricula' => $matriculas], 201);
    }

    public function updateFechaFormulario(Request $request)
    {
        $now = new Carbon();
        $data = $request->json()->all();
        $dataUsuario = $data['usuario'];
        $estudiante = Estudiante::where('user_id', $dataUsuario)->first();
        $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();

        $matricula = Matricula::where('periodo_lectivo_id', $periodoLectivoActual->id)->where('estudiante_id', $estudiante->id)->first();
        if ($matricula->estado == 'EN_PROCESO' || $matricula->estado == 'APROBADO') {
            $matricula->update([
                'fecha_formulario' => $now->format('Y-m-d'),
            ]);
        }

        return response()->json(['estudiante' => $matricula], 201);
    }

    public function updateFechaSolicitud(Request $request)
    {
        $now = new Carbon();
        $data = $request->json()->all();
        $dataUsuario = $data['usuario'];
        $estudiante = Estudiante::where('user_id', $dataUsuario)->first();
        $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();

        $matricula = Matricula::where('periodo_lectivo_id', $periodoLectivoActual->id)
            ->where('estudiante_id', $estudiante->id)
            ->first();
        if ($matricula->estado == 'EN_PROCESO' || $matricula->estado == 'APROBADO') {
            $matricula->update([
                'fecha_solicitud' => $now->format('Y-m-d'),
            ]);
        }

        return response()->json(['estudiante' => $matricula], 201);
    }

    public function openPeriodoLectivo(Request $request)
    {
        $periodoLectivoEnProceso = PeriodoLectivo::where('estado', 'EN_PROCESO')->first();

        $malla = Malla::where('carrera_id', $request->carrera_id)->first();

        $matriculas = Matricula::where('periodo_lectivo_id', $periodoLectivoEnProceso->id)
            ->where('malla_id', $malla->id)
            ->where('periodo_academico_id', $request->periodo_academico_id)
            ->orderBy('periodo_academico_id')
            ->with('detalle_matriculas')
            ->get();

        foreach ($matriculas as $matricula) {
            foreach ($matricula->detalle_matriculas as $detalleMatricula) {
                return $detalleMatricula;
                return $this->generateCupo($detalleMatricula);
            }
        }
    }

    public function generateCupo($detalleMatricula)
    {
        $now = Carbon::now();
        $periodoLectivoActual = PeriodoLectivo::where('estado', 'ACTUAL')->first();
        $matriculaEnProceso = Matricula::findOrFail($detalleMatricula->matricula_id);
        $estudiante = Estudiante::findOrFail($matriculaEnProceso->estudiante_id);

        $matriculaActual = Matricula::where('periodo_lectivo_id', $periodoLectivoActual)
            ->where('estudiante_id', $estudiante->id)->get();

        if ($matriculaActual) {
            $matricula = $matriculaActual;
        } else {
            $matricula = new Matricula([
                'fecha' => $now,
                'jornada' => $matriculaEnProceso->jornada,
                'paralelo_principal' => $matriculaEnProceso->paralelo_principal,
                'estado' => 'EN_PROCESO'
            ]);
            $periodoAcademico = PeriodoAcademico::findOrFail($matriculaEnProceso->periodo_academico_id);
            $malla = Malla::findOrFail($matriculaEnProceso->malla_id);
            $tipoMatricula = TipoMatricula::where('nombre', 'ORDINARIA')->first();

            $matricula->estudiante()->associate($estudiante);
            $matricula->periodo_lectivo()->associate($periodoLectivoActual);
            $matricula->periodo_academico()->associate($periodoAcademico);
            $matricula->malla()->associate($malla);
            $matricula->tipo_matricula()->associate($tipoMatricula);
            $matricula->save();
        }

        $detalleMatricula = new DetalleMatricula([
            'paralelo' => $detalleMatricula->paralelo,
            'numero_matricula' => 'PRIMERA',
            'jornada' => $detalleMatricula->jornada,
            'estado' => 'EN_PROCESO'
        ]);

        $asignatura = $this->validatePreyCoRequisitos($detalleMatricula->asignatura_id, $malla->id,
            $periodoAcademico->id, $estudiante->id);

        $detalleMatricula->matricula()->associate($matricula);
        $detalleMatricula->asignatura()->associate($asignatura);
        $detalleMatricula->tipo_matricula()->associate($tipoMatricula);
        $detalleMatricula->save();
    }

    public function validatePreyCoRequisitos($asignaturaActual, $malla, $periodoAcademico, $estudiante)
    {
        $asignaturasMalla = Asignatura::where('malla_id', $malla)
            ->where('periodo_academico_id', '<=', $periodoAcademico)->get();
        $matriculas = Matricula::where('estudiante_id', $estudiante)->where('estado', 'MATRICULADO')->get();
        $matriculas->detalle_matriculas();
        $asignaturasMatricula = Asignatura::where('estudiante_id', $malla)
            ->where('periodo_academico_id', '<=', $periodoAcademico)->get();
        $asignatura = Asignatura::findOrFail($asignaturaActual);

    }

    public function getEstudiante(Request $request)
    {
        try {
            $estudiante = Estudiante::where('identificacion', $request->identificacion)->with('canton_nacimiento')->first();
            $periodoLectivoActual = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
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

            $informacionEstudiante = InformacionEstudiante::where('matricula_id', $matricula->id)
                ->with('canton_residencia')->first();
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

    public function getFormulario(Request $request)
    {
        $estudiante = Estudiante::where('identificacion', $request->identificacion)->first();

        $periodoLectivoActual = PeriodoLectivo::findOrFail($request->periodo_lectivo_id);
        if ($estudiante) {
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
        } else {
            return response()->json(['errorInfo' => ['404']], 404);
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
}
