<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Detalle de postulación #{{ $postulacion->id }}
    </h2>
  </x-slot>

  <style>
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05);margin-bottom:16px}
    .card-h{padding:14px 16px;border-bottom:1px solid #e5e7eb}
    .card-b{padding:14px 16px}
    .grid{display:grid;gap:16px}
    @media(min-width:1024px){.grid-2{grid-template-columns:repeat(2,1fr)}}
    @media(min-width:1280px){.grid-3{grid-template-columns:repeat(3,1fr)}}
    .k{color:#6b7280;min-width:12rem}
    .row{display:flex;gap:12px;margin:.35rem 0;align-items:flex-start}
    .val{word-break:break-word}
    .mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono",monospace}
    .badge{display:inline-block;padding:.25rem .6rem;border-radius:999px;font-weight:700;font-size:.75rem}
    .b-pend{background:#fef3c7;color:#9a3412}
    .b-payp{background:#fde68a;color:#92400e}
    .b-paid{background:#dcfce7;color:#166534} 
    .b-ok{background:#d1fae5;color:#065f46}
    .b-no{background:#fee2e2;color:#991b1b}

    /* Botones - paleta solicitada */
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:.55rem .95rem;border-radius:12px;
         font-weight:600;text-decoration:none;font-size:.9rem;transition:.15s;min-width:120px;border:1.5px solid transparent}
    .btn-ghost{background:#fff;border-color:#e5e7eb;color:#374151}
    .btn-validar{background:#48CCA9;color:#fff}.btn-validar:hover{background:#3ab997}
    .btn-aux{background:#48AECC;color:#fff}.btn-aux:hover{background:#3a97b4}
    .btn-export{background:#486BCC;color:#fff}.btn-export:hover{background:#3b59a8}
    .btn-rechazar{background:#CC486B;color:#fff}.btn-rechazar:hover{background:#a63a57}
    .btn-pagado{background:#CCA948;color:#fff}.btn-pagado:hover{background:#a68536}

    .chips{display:flex;gap:8px;flex-wrap:wrap}
    .chip{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .6rem;border-radius:999px;background:#f1f5f9;color:#334155;font-size:.8rem}
    .muted{color:#6b7280}
  </style>

  <div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

      @if (session('status'))
        <div class="mb-4 p-3 rounded" style="background:#ecfdf5;color:#065f46;">
          {{ session('status') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="mb-4 p-3 rounded" style="background:#fee2e2;color:#991b1b;">
          {{ $errors->first() }}
        </div>
      @endif

   {{-- ===== Encabezado / Resumen de pago ===== --}}
<div class="card">
  <div class="card-h">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-sm text-gray-500">Convocatoria</div>
        <div class="text-lg font-semibold">{{ $postulacion->convocatoria->titulo ?? '—' }}</div>
      </div>
      @php
        $e = $postulacion->estatus;
        $badge = match($e){
          'validada'=>'b-ok','rechazada'=>'b-no','pagado'=>'b-paid','pago_pendiente'=>'b-payp',default=>'b-pend'
        };
      @endphp
      <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_',' ', $e)) }}</span>
    </div>
  </div>
  <div class="card-b grid grid-2">
    <div>
      <div class="row"><div class="k">ID postulación:</div><div class="val">#{{ $postulacion->id }}</div></div>
      <div class="row"><div class="k">Folio interno:</div><div class="val mono">{{ $postulacion->folio ?? '—' }}</div></div>
      <div class="row"><div class="k">Referencia pago:</div><div class="val mono">{{ $postulacion->referencia_pago ?? '—' }}</div></div>
      <div class="row"><div class="k">Folio banco:</div><div class="val mono">{{ $postulacion->folio_banco ?? '—' }}</div></div>
      <div class="row"><div class="k">Fecha pago:</div><div class="val">{{ optional($postulacion->fecha_pago)->format('Y-m-d H:i') ?? '—' }}</div></div>
      <div class="row"><div class="k">Reenvíos recibo:</div><div class="val">{{ (int)($postulacion->recibo_reenvios ?? 0) }}</div></div>
    </div>
    <div>
      <div class="row"><div class="k">Creado:</div><div class="val">{{ optional($postulacion->created_at)->format('Y-m-d H:i') ?? '—' }}</div></div>
      <div class="row"><div class="k">IP registro:</div><div class="val">{{ $postulacion->ip_registro ?? '—' }}</div></div>
      <div class="row"><div class="k">Agente:</div><div class="val text-sm text-gray-600">{{ $postulacion->agente ?? '—' }}</div></div>
    </div>
  </div>
</div>
      @php
  $perfil = optional($postulacion->user?->perfilPostulante);

  // Compat: fecha_nac o fecha_nacimiento
  $fnRaw = $perfil?->fecha_nac ?? $perfil?->fecha_nacimiento;
  if ($fnRaw instanceof \Carbon\Carbon) {
    $fnFmt = $fnRaw->format('Y-m-d');
  } elseif ($fnRaw) {
    try { $fnFmt = \Carbon\Carbon::parse($fnRaw)->format('Y-m-d'); } catch (\Throwable $__) { $fnFmt = $fnRaw; }
  } else {
    $fnFmt = null;
  }

  // formateo a 2 decimales si es numérico
  $fmt2 = function($n){ return is_numeric($n) ? number_format((float)$n, 2, '.', '') : $n; };
@endphp


   {{-- ===== Datos del aspirante ===== --}}
<div class="card">
  <div class="card-h"><div class="font-semibold">Aspirante</div></div>
  <div class="card-b grid grid-3">
    <div>
      <div class="row"><div class="k">Usuario (sistema):</div>
        <div class="val">{{ $postulacion->user?->name ?? '—' }} <span class="muted">({{ $postulacion->user?->email ?? '—' }})</span></div>
      </div>
      <div class="row"><div class="k">Nombre (ficha):</div><div class="val">{{ $perfil?->nombre_completo ?? '—' }}</div></div>
      <div class="row"><div class="k">Correo (ficha):</div><div class="val">{{ $perfil?->correo_contacto ?? '—' }}</div></div>
      <div class="row"><div class="k">Correo alterno:</div><div class="val">{{ $perfil?->correo_alternativo ?? '—' }}</div></div>
      <div class="row"><div class="k">Teléfono:</div><div class="val">{{ $perfil?->telefono ?? '—' }}</div></div>
    </div>
    <div>
      <div class="row"><div class="k">CURP:</div><div class="val mono">{{ $perfil?->curp ?? '—' }}</div></div>
      <div class="row"><div class="k">Sexo:</div><div class="val">{{ $perfil?->sexo ?? '—' }}</div></div>
      <div class="row"><div class="k">Fecha de nacimiento:</div><div class="val">{{ $fnFmt ?? '—' }}</div></div>
      <div class="row"><div class="k">Edad (cálculo):</div>
        <div class="val">
          {{ $perfil?->edad ?? ( ($fnFmt ?? null) ? \Carbon\Carbon::parse($fnFmt)->age : '—') }}
        </div>
      </div>
      <div class="row"><div class="k">Lugar de nacimiento:</div><div class="val">{{ $perfil?->lugar_nacimiento ?? '—' }}</div></div>
    </div>
    <div>
      <div class="row"><div class="k">Tipo de sangre:</div><div class="val">{{ $perfil?->tipo_sangre ?? '—' }}</div></div>
      <div class="row"><div class="k">Estado de salud:</div><div class="val">{{ $perfil?->estado_salud ?? '—' }}</div></div>
      <div class="row"><div class="k">Alergias / crónicas:</div><div class="val">{{ $perfil?->alergias ?? '—' }}</div></div>
      <div class="row"><div class="k">Medicamentos permanentes:</div><div class="val">{{ $perfil?->medicamentos ?? '—' }}</div></div>
      <div class="row"><div class="k">Info adicional:</div><div class="val">{{ $perfil?->info_adicional ?? '—' }}</div></div>
    </div>
  </div>
</div>

    {{-- ===== Datos escolares ===== --}}
<div class="card">
  <div class="card-h"><div class="font-semibold">Datos escolares</div></div>
  <div class="card-b grid grid-3">
    <div>
      <div class="row"><div class="k">Preparatoria:</div><div class="val">{{ $perfil?->preparatoria ?? '—' }}</div></div>
      <div class="row"><div class="k">Promedio general:</div><div class="val">{{ $perfil?->promedio_general !== null ? $fmt2($perfil?->promedio_general) : '—' }}</div></div>
    </div>
    <div>
      <div class="row"><div class="k">Año de egreso (prepa):</div><div class="val">{{ $perfil?->egreso_prepa_anio ?? '—' }}</div></div>
      <div class="row"><div class="k">Documento probatorio:</div><div class="val">{{ $perfil?->documento_terminacion ?? '—' }}</div></div>
    </div>
    <div>
      <div class="row"><div class="k">Contacto de emergencia:</div>
        <div class="val">
          {{ $perfil?->contacto_emergencia_nombre ?? '—' }}
          @if(!empty($perfil?->contacto_emergencia_tel))
            <span class="muted"> — {{ $perfil?->contacto_emergencia_tel }}</span>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

     {{-- ===== Sesiones asignadas ===== --}}
<div class="card">
  <div class="card-h"><div class="font-semibold">Sesiones de examen</div></div>
  <div class="card-b">
    @if($postulacion->gruposExamen->isNotEmpty())
      <div class="chips">
        @foreach($postulacion->gruposExamen as $g)
          <span class="chip" title="Grupo #{{ $g->id }}">
            {{ ucfirst($g->tipo) }}@if($g->tipo==='otro' && $g->tipo_detalle) — {{ $g->tipo_detalle }}@endif
            • {{ $g->fecha_hora?->timezone('America/Tijuana')?->format('Y-m-d H:i') ?? 'Sin fecha' }}
            • {{ $g->lugar ?? 'Por definir' }}
            • #{{ $g->id }}
          </span>
        @endforeach
      </div>
    @else
      <div class="text-gray-500 text-sm">Aún no tiene sesiones asignadas.</div>
    @endif
  </div>
  <div class="card-b" style="border-top:1px solid #e5e7eb;display:flex;gap:8px;flex-wrap:wrap">
    <a href="{{ route('admin.grupos.index', ['convocatoria_id' => $postulacion->convocatoria_id]) }}" class="btn btn-ghost">
      Gestionar grupos
    </a>
    @foreach($postulacion->gruposExamen as $g)
      <a class="btn btn-export" href="{{ route('admin.grupos.export_asistencia', $g) }}">
        Exportar asistencia (#{{ $g->id }})
      </a>
    @endforeach
  </div>
</div>

      {{-- ===== Acciones ===== --}}
      <div class="card">
        <div class="card-h"><div class="font-semibold">Acciones</div></div>
        <div class="card-b" style="display:flex;gap:8px;flex-wrap:wrap">
          @if($postulacion->estatus !== \App\Models\Postulacion::ESTATUS_PAGADO)
            <form method="POST" action="{{ route('admin.postulaciones.update', $postulacion) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="marcar_pago" value="1">
              <button class="btn btn-pagado" type="submit">Marcar pagado</button>
            </form>
          @endif

          @if($postulacion->estatus !== \App\Models\Postulacion::ESTATUS_VALIDADA)
            <form method="POST" action="{{ route('admin.postulaciones.update', $postulacion) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="estatus" value="{{ \App\Models\Postulacion::ESTATUS_VALIDADA }}">
              <button class="btn btn-validar" type="submit">Validar</button>
            </form>
          @endif

          @if($postulacion->estatus !== \App\Models\Postulacion::ESTATUS_RECHAZADA)
            <form method="POST" action="{{ route('admin.postulaciones.update', $postulacion) }}">
              @csrf @method('PATCH')
              <input type="hidden" name="estatus" value="{{ \App\Models\Postulacion::ESTATUS_RECHAZADA }}">
              <button class="btn btn-rechazar" type="submit">Rechazar</button>
            </form>
          @endif

          @if($postulacion->estatus === \App\Models\Postulacion::ESTATUS_PAGADO)
            <form method="POST" action="{{ route('admin.postulaciones.reenviar-recibo', $postulacion) }}"
                  onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerText='Enviando...';">
              @csrf
              <button class="btn btn-aux" type="submit">Reenviar recibo</button>
            </form>
          @endif

          <a class="btn btn-export" href="{{ route('admin.postulaciones.index') }}">Volver al listado</a>
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
