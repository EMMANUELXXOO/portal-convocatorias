@component('mail::message')
# ¡Gracias por tu pago!

Folio: **{{ $p->folio ?? $p->id }}**

@component('mail::panel')
**Fecha:** {{ optional($p->fecha_pago)->timezone('America/Tijuana')->format('Y-m-d H:i') }}
@endcomponent

@component('mail::button', ['url' => route('postulaciones.index')])
Ver mis postulaciones
@endcomponent

Saludos,  
**{{ config('app.name') }}**
@endcomponent
