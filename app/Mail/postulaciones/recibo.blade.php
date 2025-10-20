@component('mail::message')
# Pago confirmado

Hola {{ $p->user->name }},

Hemos registrado tu pago de ficha para **{{ $p->convocatoria->titulo }}**.

- **Folio interno:** {{ $p->folio }}
- **Referencia de pago:** {{ $p->referencia_pago }}
- **Folio bancario:** {{ $p->folio_banco }}
- **Fecha de pago:** {{ optional($p->fecha_pago)->format('Y-m-d H:i') }}

@component('mail::button', ['url' => route('postulaciones.index')])
Ver mis postulaciones
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
