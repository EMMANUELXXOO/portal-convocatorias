@component('mail::message')
# Detalles de tu Proceso de Convocatoria — Grupo #{{ $grupo->id }}

Hola {{ optional($postulacion->user->perfilPostulante)->nombre_completo ?? $postulacion->user->name ?? 'Aspirante' }},

Te compartimos la información de tu **{{ $tipoLegible }}**:

@component('mail::panel')
**Fecha:** {{ $fecha ?? 'Por definir' }}  
**Hora:** {{ $hora ?? 'Por definir' }}  
**Lugar:** {{ $lugar ?? 'Por definir' }}  
@isset($convocatoria->titulo)
**Convocatoria:** {{ $convocatoria->titulo }}
@endisset
@endcomponent

@isset($mensajeLibre)
### Mensaje del equipo
{{ $mensajeLibre }}
@endisset

@component('mail::button', ['url' => $urlPostulaciones])
Ver mis postulaciones
@endcomponent

Si tienes dudas, responde a este correo y te ayudamos.

Gracias, ESCUELA CRUZ ROJA TIJUANA 
@endcomponent
