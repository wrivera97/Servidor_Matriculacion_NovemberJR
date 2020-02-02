<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Notificacion</title>
</head>
<body>
<ul>
    <li>Usuario: {{ $notificacion->user}}</li>
    <li>Carrera: {{ $notificacion->carrera}}</li>
    <li>Estudiante: {{ $notificacion->estudiante}}</li>
    <li>Razon: {{ $notificacion->body}}</li>
    <li>Accion: {{ $notificacion->accion}}</li>
    <li>Tabla: {{ $notificacion->tabla_id}} - {{ $notificacion->tabla}}</li>
    <li>Valor Anterior: {{ $notificacion->valor_anterior}}</li>
    <li>Valor Nuevo: {{ $notificacion->valor_nuevo}}</li>
</ul>
</body>
</html>
