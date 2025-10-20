Hola {{ optional($postulacion->user->perfilPostulante)->nombre_completo ?? $postulacion->user->name ?? 'Aspirante' }},

Detalles de tu Proceso de Convocatoia — Grupo #{{ $grupo->id }}

Tipo: {{ $tipoLegible }}
Fecha: {{ $fecha ?? 'Por definir' }}
Hora: {{ $hora ?? 'Por definir' }}
Lugar: {{ $lugar ?? 'Por definir' }}
@isset($convocatoria->titulo)
Convocatoria: {{ $convocatoria->titulo }}
@endisset

@isset($mensajeLibre)
Mensaje: {{ $mensajeLibre }}
@endisset

Ver mis postulaciones: {{ $urlPostulaciones }}

{{ config('app.name') }}
