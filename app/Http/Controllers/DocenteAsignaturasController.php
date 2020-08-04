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
    /*Funcion  "metodo get" (obtenemos los datos desde la base de datos, estos datos son enviados en formato JSON al frontend)*/
    public function getDocenteAsignatura(Request $request)
    {
        $docente = Docente::where('id', $request->id)->first();/*buscamos el id docente y seleccionamos el primer registro encontrado*/
        $docenteAsignaturas = DocenteAsignatura::select('docente_asignaturas.*', 'carreras.descripcion')/*hacemos una consulta sql con ORM*/
            ->join('asignaturas', 'asignaturas.id', 'docente_asignaturas.asignatura_id')/*unimos las tablas de la consulta*/
            ->join('mallas', 'mallas.id', 'asignaturas.malla_id')
            ->join('carreras', 'carreras.id', 'mallas.carrera_id')
            ->where('docente_id', $docente->id)->where('periodo_lectivo_id', $request->periodo_lectivo_id)/*seleccionamos los registros donde existe el id docente y periodo */
            ->with('asignatura')->orderby('periodo_academico_id')->get();/*los datos obtenidos, los añadimos con asignatura y ordenamos por periodo_academico_id*/
        if ($docente) {
            return response()->json(['asignacionesDocente' => $docenteAsignaturas], 200);/*si los datos existen retornamos los datos con un estdo 200 ok */
        }
        return response()->json('error', 500);/*si hubo un error al obtener los datos enviamos 'error' con estado 500 */
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

    /*Funcion  "metodo post" (Enviamos los datos a guardar en la base de datos, estos datos son traidos desde frontend "angular" en formato JSON)*/
    public function AsignarDocentesAsignaturas(Request $request)
    {
        $data = $request->json()->all();
        $dataDocenteAsignaturas = $data['docente_asignatura'];/*este es el nombre del objeto principal el cual enviaremos todos los datos*/
        $dataDocente = $dataDocenteAsignaturas['docente']; /*obtenemos el id del docente seleccionado desde frontend*/
        $dataPeriodoLectivo = $dataDocenteAsignaturas['periodo_lectivo'];/*obtenemos el id del periodo lectivo seleccionado desde frontend*/ 
        $dataAsignatura = $dataDocenteAsignaturas['asignatura'];/*obtenemos el id de la asignatura seleccionada desde frontend*/

        $dataAll = [$dataDocenteAsignaturas, $dataDocente, $dataPeriodoLectivo, $dataAsignatura];

        $docenteasignaturas = new DocenteAsignatura([/*llamos al modelo y pasamos los siguientes datos "paralelo,jornada,estado"*/
            'paralelo' => $dataDocenteAsignaturas['paralelo'], 
            'jornada' => $dataDocenteAsignaturas['jornada'],
            'estado' => $dataDocenteAsignaturas['estado']
        ]);

        $docente = Docente::findOrFail($dataDocente['id']);/*verificamos si el docente existe por su id*/
        $docenteasignaturas->docente()->associate($docente);

        $periodolectivo = PeriodoLectivo::findOrFail($dataPeriodoLectivo['id']);/*verificamos si el periodo lectivo este activo o que existe mediante su id*/
        $docenteasignaturas->periodoLectivo()->associate($periodolectivo);

        $asignatura = Asignatura::findOrFail($dataAsignatura['id']);/*verificamos si la asignatura existe o esta activa mendiante su id*/
        $docenteasignaturas->asignatura()->associate($asignatura);
        $docenteasignaturas->save(); /*guarda los datos una vez verificados*/

        if ($dataAll) {
            return response()->json(['' => $dataAll], 200);/*si los datos fueron guardados retornamos '' con estado 200 ok*/
        }

        return response()->json('error', 500);/*si hubo un error al guardar los datos enviamos 'error' con estado 500 */
    }
    /*Funcion  "metodo put" (Enviamos los datos a modificar en la base de datos, estos datos son traidos desde frontend "angular" en formato JSON)*/
    public function updateAsignaturaDocente(Request $request)
    {
        try {/*inicio de try */
            DB::beginTransaction();/*iniciamos la transaccion a la db */
            $data = $request->json()->all();
            $dataDocenteAsignaturas = $data['docente_asignatura'];/*arreglo $data*/
            $docenteAsignatura = DocenteAsignatura::findOrFail($dataDocenteAsignaturas['id']); /*hacemos busqueda si el registro seleccionado existe o no en la db*/
            $docenteAsignatura->update([
                'paralelo' => $dataDocenteAsignaturas['paralelo'],
                'jornada' => $dataDocenteAsignaturas['jornada']
            ]);
            $docente = Docente::findOrFail($dataDocenteAsignaturas['docente_id']);/*se verifica si existe el docente_id*/
            $docenteAsignatura->docente()->associate($docente);

            $periodolectivo = PeriodoLectivo::findOrFail($dataDocenteAsignaturas['periodo_lectivo_id']);/*se verifica si existe el periodo_lectivo_id*/
            $docenteAsignatura->periodoLectivo()->associate($periodolectivo);

            $asignatura = Asignatura::findOrFail($dataDocenteAsignaturas['asignatura']['id']);/*se verifica que existe el id asignatura a ser modificado*/
            $docenteAsignatura->asignatura()->associate($asignatura);

            $docenteAsignatura->save();/* se valida y se guarda los datos*/

            DB::commit();/*si la informacion es correcta se envia a la base de datos*/

            if ($docenteAsignatura) {

                return response()->json(['succesfull' => $docenteAsignatura], 200);/*si la informacion se guardo correctamente enviamos la respuesta con estado 200 ok*/
            }

            return response()->json('error', 500);/*si hubo un error al actualizar los datos enviamos 'error' con estado 500 */
            //cierra el try
        } catch (Exception $e) {
            return response()->json($e, 500);
        }
    }
    /*Funcion "metodo delete" borrado logico(Enviamos el id del detalle seleccionado a la base de datos, estos datos son traidos desde frontend "angular" en formato JSON)*/
    public function deleteAsignacionDocente(Request $request)
    {
        DB::beginTransaction();/*Iniciamos la transaccion a db*/
        $DocenteAsigntura = DocenteAsignatura::findOrFail($request->id);/*Buscamos el id del detalle seleccionado a ser anulado*/
        $DocenteAsigntura->update(['estado' => 'ANULADO']);/*cambiamos de estado a 'ANULADO'*/
        DB::commit();/*si el id fue encontrado la informacion se actualiza*/
        return response()->json(['Succesfull', $DocenteAsigntura], 201);/*si el estado fue cambiado sastifactoriamente enviamos un estado 201 ok*/
    }
}