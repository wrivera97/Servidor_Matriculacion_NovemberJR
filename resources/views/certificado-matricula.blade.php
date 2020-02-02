<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Certificado</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>

<div class="container">
    <div class="card row">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12 col-xs-12 text-right mt-5 text-center">
                    <img src="{{ asset('images/logo_instituto_'.$certificado[0]->instituto_id.'.png') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-xs-12 text-center">
                    <p>{{$certificado[0]->fecha}}</p>
                </div>
            </div>
            <div class="row m-2">
                <div class="col-lg-12 col-xs-12">
                    <h2 class="text-center">CERTIFICADO DE MATRÍCULA</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-xs-12 text-center">
                    <h3>MATRÍCULA</h3>
                    <p>{{$certificado[0]->codigo}}</p>
                </div>
                <div class="col-lg-6 col-sm-12 text-center">
                    <h3>FOLIO</h3>
                    <p>{{$certificado[0]->folio}}</p>
                </div>
            </div>
            <div class="row m-3">
                <div class="col-lg-12 col-sm-12">
                    <p class="text-justify">
                        <strong>CERTIFICO</strong> que,
                        <strong>
                            {{
                        $certificado[0]['estudiante']->apellido1 .' '.$certificado[0]['estudiante']->apellido2
                        .' '.$certificado[0]['estudiante']->nombre1.' '.$certificado[0]['estudiante']->nombre2
                        }}
                        </strong> con cédula de ciudadanía N°
                        <strong>{{$certificado[0]['estudiante']->identificacion}}</strong>
                        , previo cumpliento de los requisitos legales, se encuentra matriculado/a en
                        <strong>{{$certificado[0]['periodo_academico']->nombre}}</strong>
                        Periodo Académico, de la carrera
                        <strong>{{$certificado[0]->carrera}}</strong>
                        , para el periodo lectivo
                        <strong>{{$certificado[0]['periodo_lectivo']->nombre}}</strong>
                        , en las siguientes asignaturas:
                    </p>
                </div>
            </div>

            <div class="row ml-2 mr-2">
                <div class="col-lg-12">
                    <table class="table table-sm table-responsive">
                        <tr>
                            <th>
                                CÓDIGO
                            </th>
                            <th>
                                ASIGNATURA
                            </th>
                            <th>
                                PERIODO ACADÉMICO
                            </th>
                            <th>
                                NÚMERO MATRÍCULA
                            </th>
                            <th>
                                JORNADA
                            </th>
                            <th>
                                HORAS DOCENTE
                            </th>
                            <th>
                                HORAS PRÁCTICA
                            </th>
                            <th>
                                HORAS AUTÓNOMA
                            </th>
                        </tr>
                        @foreach ($certificado as $asignatura)
                            <tr>
                                <td>
                                    {{$asignatura->asignatura_codigo}}
                                </td>
                                <td>
                                    {{$asignatura->asignatura}}
                                </td>
                                <td>
                                    <select name="" id="" value="{{$asignatura->periodo}}" disabled>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                    </select>

                                </td>
                                <td>
                                    <select name="" id="" value="{{$asignatura->numero_matricula}}" disabled>
                                        <option value="1">PRIMERA</option>
                                        <option value="2">SEGUNDA</option>
                                        <option value="3">TERCERA</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="" id="" value="{{$asignatura->jornada}}" disabled>
                                        <option value="1">MATUTINA</option>
                                        <option value="2">VESPERTINA</option>
                                        <option value="3">NOCTURNA</option>
                                        <option value="4">INTENSIVA</option>
                                    </select>

                                </td>
                                <td width="10%">
                                    {{$asignatura->horas_docente}}
                                </td>
                                <td width="10%">
                                    {{$asignatura->horas_practica}}
                                </td>
                                <td width="10%">
                                    {{$asignatura->horas_autonoma}}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="row m-3">
                <div class="col-lg-12">
                    <p>Con sentimiento de distinguida consideración.</p>
                    <p class="mt-5">Atentamente,</p>
                    <p class="mt-5"><strong>SECRETARIA ACADEMICA</strong></p>
                    <p><strong>{{$certificado[0]->instituto}}</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
</body>
</html>
