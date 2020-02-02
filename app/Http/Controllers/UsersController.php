<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
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

    public function getLogin(Request $request)
    {
        $user = User::where('email', strtolower($request->email))
            ->with('role')
            ->first();
        return response()->json(['usuario' => $user], 200);
    }

    public function get(Request $request)
    {
        $usuarios = User::where('role_id', '<>', '2')
            ->with('role')
            ->with('carreras')
            ->orderBy('name')
            ->paginate($request->records_per_page);
        return response()->json(['pagination' => [
            'total' => $usuarios->total(),
            'current_page' => $usuarios->currentPage(),
            'per_page' => $usuarios->perPage(),
            'last_page' => $usuarios->lastPage(),
            'from' => $usuarios->firstItem(),
            'to' => $usuarios->lastItem()], 'usuarios' => $usuarios], 200);
    }

    public function filter(Request $request)
    {
        $usuarios = User::where('role_id', '<>', '2')
            ->where(function ($usuarios) use (&$request) {
                $usuarios->orWhere('email', 'like', '%' . $request->email . '%')
                    ->orWhere('name', 'like', '%' . $request->name . '%')
                    ->orWhere('user_name', 'like', '%' . $request->user_name . '%')
                    ->orWhere('estado', $request->estado);
            })
            ->orderBy('name')
            ->with('role')
            ->with('carreras')
            ->get();
        return response()->json(['usuarios' => $usuarios], 200);
    }

    public function create(Request $request)
    {
        $data = $request->json()->all();
        $dataUsuario = $data['usuario'];
        $dataCarreras = $data['usuario']['carreras'];
        $dataRol = $data['usuario']['role'];
        $usuario = User::where('email', $dataUsuario['email'])->first();
        $rol = Role::findOrFail($dataRol['id']);

        if (!$usuario) {
            DB::beginTransaction();
            $usuario = $rol->users()->create([
                'name' => strtoupper(trim($dataUsuario['name'])),
                'user_name' => strtoupper(trim($dataUsuario['user_name'])),
                'email' => strtolower(trim($dataUsuario['email'])),
                'password' => Hash::make(trim($dataUsuario['user_name'])),
            ]);

            for ($i = 0; $i < sizeof($dataCarreras); $i++) {
                $usuario->carreras()->attach($dataCarreras[$i]['id']);
            }
            DB::commit();
        } else {
            return response()->json(['errorInfo' => ['23505']], 400);
        }
        return response()->json(['usuario' => $usuario], 201);
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();
        $dataUsuario = $data['usuario'];
        $dataCarreras = $data['usuario']['carreras'];
        $dataRol = $data['usuario']['role'];
        $usuario = User::findOrFail($dataUsuario['id']);
        $rol = Role::findOrFail($dataRol['id']);

        if ($usuario) {
            DB::beginTransaction();
            $usuario->carreras()->detach();
            for ($i = 0; $i < sizeof($dataCarreras); $i++) {
                $usuario->carreras()->attach($dataCarreras[$i]['id']);
            }
            $usuario->update([
                'name' => $dataUsuario['name'],
                'user_name' => $dataUsuario['user_name'],
                'email' => $dataUsuario['email'],
                'estado' => $dataUsuario['estado'],
            ]);
            $usuario->role()->associate($rol);
            $usuario->save();
            DB::commit();
        } else {
            return response()->json(['errorInfo' => ['23505']], 400);
        }
        return response()->json(['usuario' => '$usuario'], 201);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->json()->all();
        $dataUser = $data['user'];
        $user = User::findOrFail($dataUser['id']);
        $user->update([
            'password' => Hash::make($dataUser['password']),
        ]);
        return $user;
    }

    public function getRoles(Request $request)
    {
        $roles = Role::where('rol', '<>', '2')->get();
        return response()->json(['roles' => $roles], 200);
    }

}
