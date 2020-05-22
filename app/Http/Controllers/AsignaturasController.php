<?php

namespace App\Http\Controllers;

use App\Asignatura;
use App\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignaturasController extends Controller
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

    public function get()
    {
        $asignaturas = Asignatura::where('estado', 'ACTIVO')->with('periodo_academico')->get();
        return response()->json(['asignaturas' => $asignaturas], 200);
    }



     public function getCarreraDocenteAsignatura(Request $request)
{ 
  $docente= Docente::where('id',$request->id)->first();

      $asignaturas=db::select('select c.descripcion, c.id from docentes d
      join docente_asignaturas da on d.id = da.docente_id
      join asignaturas a on da.asignatura_id = a.id
      join mallas m on a.malla_id = m.id
      join carreras c on m.carrera_id = c.id
      where d.id= :id',['id'=>$docente->id]);
      
      return response()->json(['exito'=>$asignaturas,200]);
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
    public function getaAsignaturaCarrera(Request $request){





    }
}
