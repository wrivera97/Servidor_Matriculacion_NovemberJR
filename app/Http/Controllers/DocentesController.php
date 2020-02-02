<?php

namespace App\Http\Controllers;

use App\Docente;
use Illuminate\Http\Request;

class DocentesController extends Controller
{
    public function __construct()
    {
        //
    }

    // function obtener datos all
    public function listD(Request $request)
    {
        $data = $request->json()->all();
        $dataDocente = $data['docente'];
        $docente = Docente::findOrFail($dataDocente['id']);

        if ($docente) {
            return response()->json(['docente' => $docente], 200);
        }
        return response()->json(['error' => $docente], 500);
    }

//function insertar datos
    public function createD(Request $request)
    {
        $data = $request->json()->all();
        //return $data;
        $dataDocente = $data['docente'];
        //return $dataDocente;
        $docente = Docente::create([
            'nombre1' => $dataDocente['nombre1'],
            'nombre2' => $dataDocente['nombre2'],
            'apellido1' => $dataDocente['apellido1'],
            'apellido2' => $dataDocente['apellido2'],
            'tipo_identificacion' => $dataDocente['tipo_identificacion'],
            'identificacion' => $dataDocente['identificacion'],
            'genero' => $dataDocente['genero'],
            'fecha_nacimiento' => $dataDocente['fecha_nacimiento'],
            'correo_personal' => $dataDocente['correo_personal'],
            'correo_institucional' => $dataDocente['correo_institucional'],
            'discapacidad' => $dataDocente['discapacidad'],
            'tipo_sangre' => $dataDocente['tipo_sangre'],
            'direccion' => $dataDocente['direccion'],
            'etnia' => $dataDocente['etnia'],
            'pueblo_nacionalidad' => $dataDocente['pueblo_nacionalidad'],
            'estado' => $dataDocente['estado'],
        ]);
        if ($docente) {
            return response()->json(['docente'=> $docente], 201);
        }
        return response()->json(['error' => $docente], 500);
    }
//     function modificar
    public function updateD(Request $request)
    {
        $data = $request->json()->all();
        $dataDocente = $data['docente'];

        $docente = Docente::findOrFail($dataDocente['id']);
        $docente->update([

            'nombre1' => $dataDocente['nombre1'],
            'nombre2' => $dataDocente['nombre2'],
            'apellido1' => $dataDocente['apellido1'],
            'apellido2' => $dataDocente['apellido2'],
            'tipo_identificacion' => $dataDocente['tipo_identificacion'],
            'identificacion' => $dataDocente['identificacion'],
            'genero' => $dataDocente['genero'],
            'fecha_nacimiento' => $dataDocente['fecha_nacimiento'],
            'correo_personal' => $dataDocente['correo_personal'],
            'correo_institucional' => $dataDocente['correo_institucional'],
            'discapacidad' => $dataDocente['discapacidad'],
            'tipo_sangre' => $dataDocente['tipo_sangre'],
            'direccion' => $dataDocente['direccion'],
            'etnia' => $dataDocente['etnia'],
            'pueblo_nacionalidad' => $dataDocente['pueblo_nacionalidad'],
            'estado' => $dataDocente['estado'],

        ]);
        if ($docente) {
            return response()->json(['docente' => $docente], 201);
        }
        return response()->json(['error' => $docente], 500);

    }

    //function eliminarLogico

    public function deleteD(Request $request){
        $docente = Docente::findOrFail($request->docente_id);
        $docente->update(['estado' => false]);
        if ($docente) {
            return response()->json(['docente' => $docente], 201);
        }
        return response()->json(['error' => $docente], 500);

    }




}
