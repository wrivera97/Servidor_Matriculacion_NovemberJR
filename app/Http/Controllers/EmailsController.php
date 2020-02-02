<?php

namespace App\Http\Controllers;

use App\Audit;
use App\Carrera;
use App\DetalleMatricula;
use App\Estudiante;
use App\Malla;
use App\Matricula;
use App\Notificacion;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class EmailsController extends Controller
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

    public function sendUploadCupos(Request $request)
    {
        $carrera = Carrera::findOrFail($request->carrera_id);
        $correos = DB::select("select correo from notificacion_correos where estado = 'ACTIVO'");
        foreach ($correos as $correo) {
            $for = array($correo->correo);
        }
        $user = User::findOrFail($request->user_id);
        $notificacion = new Notificacion($request->asunto, $carrera->nombre, $request->body, $user->email);

        $subject = $request->asunto;

        Mail::send('notificacion-cupos-upload', array('notificacion' => $notificacion), function ($msj) use ($subject, $for) {
            $msj->subject($subject);
            $msj->to($for);
        });
    }

    public function sendCupos(Request $request)
    {
        $auditoria = Audit::where('auditable_type', 'App\Matricula')->orderBy('id', 'desc')->first();
        $matricula = Matricula::findOrFail($auditoria->auditable_id);
        $estudiante = Estudiante::findOrFail($matricula->estudiante_id);
        $malla = Malla::findOrFail($matricula->malla_id);
        $carrera = Carrera::findOrFail($malla->carrera_id);
        $correos = DB::select("select correo from notificacion_correos where estado = 'ACTIVO'");
        foreach ($correos as $correo) {
            $for = array($correo->correo);
        }
        $user = User::findOrFail($request->user_id);
        $notificacion = new Notificacion($request->asunto, $carrera->nombre, $request->body, $user->email,
            $auditoria->old_values, $auditoria->new_values, $auditoria->event, $auditoria->auditable_type,
            $auditoria->auditable_id, $estudiante->identificacion . ' ' . $estudiante->apellido1 . ' '
            . $estudiante->apellido2 . ' ' . $estudiante->nombre1 . ' ' . $estudiante->nombre2);
        $subject = $request->asunto;

        Mail::send('notificacion-cupos', array('notificacion' => $notificacion), function ($msj) use ($subject, $for) {
            $msj->subject($subject);
            $msj->to($for);
        });
    }

    public function sendDetalleCupos(Request $request)
    {
        $auditoria = Audit::where('auditable_type', 'App\DetalleMatricula')->orderBy('id', 'desc')->first();
        $detalleCupo = DetalleMatricula::findOrFail($auditoria->auditable_id);
        $matricula = Matricula::findOrFail($detalleCupo->matricula_id);
        $estudiante = Estudiante::findOrFail($matricula->estudiante_id);
        $malla = Malla::findOrFail($matricula->malla_id);
        $carrera = Carrera::findOrFail($malla->carrera_id);
        $correos = DB::select("select correo from notificacion_correos where estado = 'ACTIVO'");
        foreach ($correos as $correo) {
            $for = array($correo->correo);
        }
        $user = User::findOrFail($request->user_id);
        $notificacion = new Notificacion($request->asunto, $carrera->nombre, $request->body, $user->email,
            $auditoria->old_values, $auditoria->new_values, $auditoria->event, $auditoria->auditable_type, $auditoria->auditable_id,
            $estudiante->identificacion . ' ' . $estudiante->apellido1 . ' ' . $estudiante->apellido2 . ' ' . $estudiante->nombre1 . ' ' . $estudiante->nombre2);
        $subject = $request->asunto;

        Mail::send('notificacion-cupos', array('notificacion' => $notificacion), function ($msj) use ($subject, $for) {
            $msj->subject($subject);
            $msj->to($for);
        });
    }

}
