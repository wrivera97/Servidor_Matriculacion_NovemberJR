<?php
namespace App\Http\Controllers;
use App\DetalleMatricula;
use App\DetalleNota;
use App\DocenteAsignatura;
use App\Estudiante;
use App\Matricula;
use App\User;
use Dompdf\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DetalleNotasController extends Controller
{
    /*Funcion  "metodo get" (obtenemos los datos desde la base de datos, estos datos son enviados en formato JSON al frontend)*/
    public function getDetalleEstudiantes(Request $request)
    {
        $detalleEstudiante = DetalleMatricula::select('estudiante_id') /*hacemos una consulta sql con ORM*/
            ->join('matriculas', 'matriculas.id', 'detalle_matriculas.matricula_id')
            ->join('estudiantes', 'estudiantes.id', 'matriculas.estudiante_id') /*unimos las tablas de la consulta*/
            ->where('detalle_matriculas.estado', 'MATRICULADO')
            ->where('detalle_matriculas.asignatura_id', $request->asignatura_id)
            ->where('detalle_matriculas.paralelo', $request->paralelo)
            ->where('detalle_matriculas.jornada', $request->jornada)
            ->where('matriculas.periodo_lectivo_id', $request->periodo_lectivo_id)->with('estudiante')->orderby('apellido1')->get();/*seleccionamos los registros y ordenamos */

        return response()->json(['detalle_estudiante' => $detalleEstudiante], 200);/*si los datos existen retornamos los datos con un estdo 200 ok */
    }

    /*Funcion  "metodo post" (Enviamos los datos a guardar en la base de datos, estos datos son traidos desde frontend "angular" en formato JSON)*/
    public function createDetalleNotas(Request $request)
    {
        $data = $request->json()->all();
        $dataDetalleNotas = $data['detalle_nota'];/*este es el nombre del objeto principal el cual enviaremos todos los datos*/
        $dataDocentAsignatura = $dataDetalleNotas['docente_asignatura'];/*obtenemos el id del docente_asignatura seleccionado desde frontend*/
        $dataEstudiante = $dataDetalleNotas['estudiante'];/*obtenemos el id del estudiante seleccionado desde frontend*/
        $detalleNota = new DetalleNota([/*llamos al modelo y pasamos los datos*/
            'nota1' => $dataDetalleNotas['nota1'],
            'nota2' => $dataDetalleNotas['nota2'],
            'nota_final' => $dataDetalleNotas['nota_final'],
            'asistencia1' => $dataDetalleNotas['asistencia1'],
            'asistencia2' => $dataDetalleNotas['asistencia2'],
            'asistencia_final' => $dataDetalleNotas['asistencia_final'],
            'estado_academico' => $dataDetalleNotas['estado_academico']

        ]);
        $docenteAsignatura = DocenteAsignatura::findOrFail($dataDocentAsignatura['id']);/*verificamos si el docente_asignatura existe por su id*/
        $detalleNota->docente_asignatura()->associate($docenteAsignatura);

        $estudiante = Estudiante::findOrFail($dataEstudiante['id']);/*verificamos si el estudiante existe por su id*/
        $detalleNota->estudiante()->associate($estudiante);

        $detalleNota->save(); /*guarda los datos una vez verificados*/

        if ($detalleNota == true) {

            return response()->json(['success' => $detalleNota], 201); /*si los datos fueron guardados retornamos 'success' con estado 200 ok*/
        } else {
            return  response()->json('error', 500); /*si hubo un error al guardar los datos enviamos 'error' con estado 500 */
        }
    }
    /*Funcion  "metodo put" (Enviamos los datos a modificar en la base de datos, estos datos son traidos desde frontend "angular" en formato JSON)*/
    public function updateDetalleNotas(Request $request)
    {
        try {/*inicio de try */
            DB::beginTransaction(); /*iniciamos la transaccion a la db */
            $data = $request->json()->all();
            $dataDetalleNotas = $data['detalle_nota']; /*arreglo $data*/
            $detalleNota = DetalleNota::findOrFail($dataDetalleNotas['id']);/*hacemos busqueda si el registro seleccionado existe o no en la db*/
            $detalleNota->update([/*enviamos los datos a actualizar */
                'nota1' => $dataDetalleNotas['nota1'],
                'nota2' => $dataDetalleNotas['nota2'],
                'nota_final' => $dataDetalleNotas['nota_final'],
                'asistencia1' => $dataDetalleNotas['asistencia1'],
                'asistencia2' => $dataDetalleNotas['asistencia2'],
                'asistencia_final' => $dataDetalleNotas['asistencia_final'],
                'estado_academico' => $dataDetalleNotas['estado_academico']
            ]);
            $docenteAsignatura = DocenteAsignatura::findOrFail($dataDetalleNotas['docente_asignatura_id']);/*se verifica si existe el docente_asignatura_id*/
            $detalleNota->docente_asignatura()->associate($docenteAsignatura);

            $estudiante = Estudiante::findOrFail($dataDetalleNotas['estudiante_id']);/*se verifica si existe el estudiante_id*/
            $detalleNota->estudiante()->associate($estudiante);
            $detalleNota->save();/* se valida y se guarda los datos*/

            DB::commit();/*si la informacion es correcta se envia a la base de datos*/
            if ($detalleNota) {

                return response()->json(['success' => $detalleNota], 201); /*si la informacion se guardo correctamente enviamos la respuesta con estado 200 ok*/
            } else {
                return response()->json('error', 500);/*si hubo un error al actualizar los datos enviamos 'error' con estado 500 */
            }
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }

    public function getDetalleAsignaturaEstudianteUser(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $estudiante = Estudiante::where('user_id', $user->id)->first();
        $matricula = Matricula::where('estudiante_id', $estudiante->id)->where('periodo_lectivo_id', $request->periodo_lectivo_id)->first();
        $detalle_matricula = DetalleMatricula::where('matricula_id', $matricula->id)->with('asignatura')->get();

        if ($user) {
            return response()->json(['asignatura_estudiante' => $detalle_matricula], 200);
        } else {
            return response()->json('error', 500);
        }
    }
/*Funcion  "metodo get" (obtenemos los datos desde la base de datos, estos datos son enviados en formato JSON al frontend)*/
    public function getdetalleNota(Request $request)
    {
        $user = User::where('id', $request->id)->first(); /*hacemos una consulta con eloquent*/
        $estudiante = Estudiante::where('user_id', $user->id)->first();/*buscamos el primer estudiante con el id de user */
        $docenteAsignatura = DocenteAsignatura::where('asignatura_id', $request->asignatura_id)->where('periodo_lectivo_id', $request->periodo_lectivo_id)->first();
        $detalleNota = DetalleNota::where('docente_asignatura_id', $docenteAsignatura->id)->where('estudiante_id', $estudiante->id)->first();
        if ($detalleNota) {
            return response()->json(['detalleNota' => $detalleNota], 200);/*si los datos existen retornamos los datos con un estdo 200 ok */
        } else {

            return response()->json('error', 500);/*si hubo un error enviamos 'error' con estado 500 */
        }
    }


    public function getdetalleNotaDocente(Request $request)
    {
        $detalleNota = DetalleNota::where('estudiante_id', $request->estudiante_id)
            ->where('docente_asignatura_id', $request->docente_asignatura_id)->first();
        if ($detalleNota) {
            return response()->json(['detalle_nota' => $detalleNota], 200);
        } else {
            return response()->json('error', 500);
        }
    }
}
