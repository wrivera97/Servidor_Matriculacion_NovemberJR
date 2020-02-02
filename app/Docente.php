<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $fillable =
        [
        'nombre1','nombre2','apellido1','apellido2','tipo_identificacion',
        'identificacion','genero','fecha_nacimiento','correo_personal','correo_institucional',
        'discapacidad','tipo_sangre','direccion','etnia','pueblo_nacionalidad','estado'
        ];
}
