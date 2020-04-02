select asignaturas.nombre from asignaturas
join mallas
on mallas.id=asignaturas.malla_id
where carrera_id=7 and asignaturas.periodo_academico_id=6

select periodo_academico_id from docente_asignaturas
join asignaturas
on asignaturas.id=docente_asignaturas.asignatura_id
