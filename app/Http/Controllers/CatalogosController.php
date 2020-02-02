<?php

namespace App\Http\Controllers;

use App\Carrera;
use App\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogosController extends Controller
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

    public function getPaises()
    {
        $paises = Ubicacion::where('tipo', 'PAIS')->where('estado', 'ACTIVO')->orderby('nombre')->get();

        return response()->json(['paises' => $paises], 200);
    }

    public function getProvincias()
    {
        $sql = "SELECT * FROM ubicaciones WHERE tipo='PROVINCIA' AND estado = 'ACTIVO'";
        $provincias = DB::select($sql);

        return response()->json(['provincias' => $provincias], 200);
    }

    public function getCantones(Request $request)
    {

        if ($request->provincia_id) {
            $sql = "SELECT * FROM ubicaciones WHERE tipo='CANTON' AND estado = 'ACTIVO' AND codigo_padre_id=" . $request->provincia_id;
            $cantones = DB::select($sql);
        } else {
            $cantones = array(['id' => 0, 'nombre' => '']);
        }
        return response()->json(['cantones' => $cantones], 200);
    }

    public function getCarreras(Request $request)
    {
        $carreras = Carrera::select('carreras.*')
            ->join('carrera_user', 'carrera_user.carrera_id', 'carreras.id')
            ->join('users', 'users.id', 'carrera_user.user_id')
            ->where('carreras.estado', 'ACTIVO')
            ->where('carrera_user.user_id', $request->user_id)
            ->orderby('descripcion')
            ->orderby('nombre')
            ->get();
        return response()->json(['carreras' => $carreras], 200);
    }

    public function getPeriodoAcademicos()
    {
        //$data = $request->json()->all();
        $sql = "SELECT * FROM periodo_academicos WHERE estado = 'ACTIVO'";
        $periodo_academicos = DB::select($sql);
        return response()->json(['periodo_academicos' => $periodo_academicos], 200);
    }
}
