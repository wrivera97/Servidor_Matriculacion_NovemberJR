<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 22/3/2019
 * Time: 16:16
 */

namespace App;


class Notificacion
{
    public $asunto;
    public $estudiante;
    public $carrera = '';
    public $body = '';
    public $valor_anterior = '';
    public $valor_nuevo = '';
    public $accion = '';
    public $tabla = '';
    public $tabla_id = '';
    public $user = '';

    function __construct()
    {
        $params = func_get_args();
        $num_params = func_num_args();
        $funcion_constructor = '__construct' . $num_params;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $params);
        }
    }

    public function __construct10($asunto, $carrera, $body, $user, $valor_anterior, $valor_nuevo, $accion, $tabla, $tabla_id, $estudiante)
    {
        $this->asunto = $asunto;
        $this->carrera = $carrera;
        $this->body = $body;
        $this->accion = $accion;
        $this->tabla = $tabla;
        $this->tabla_id = $tabla_id;
        $this->valor_anterior = $valor_anterior;
        $this->valor_nuevo = $valor_nuevo;
        $this->user = $user;
        $this->estudiante = $estudiante;
    }

    public function __construct4($asunto, $carrera, $body, $user)
    {
        $this->asunto = $asunto;
        $this->carrera = $carrera;
        $this->body = $body;
        $this->user = $user;

    }
}
