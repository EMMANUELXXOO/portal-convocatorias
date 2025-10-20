<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-900 leading-tight">Postulaciones</h2>
    <p class="text-sm text-gray-500">Administra aspirantes, pagos y asignaciones.</p>
  </x-slot>

  <style>
    .grid{display:grid;gap:16px}
    @media(min-width:768px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(min-width:1280px){.grid{grid-template-columns:repeat(3,minmax(0,1fr))}}

    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;
          box-shadow:0 1px 2px rgba(0,0,0,.05);display:flex;flex-direction:column}
    .card-h{padding:14px 16px;border-bottom:1px solid #e5e7eb;
            display:flex;justify-content:space-between;align-items:center}
    .card-b{padding:14px 16px}
    .card-f{padding:14px 16px;border-top:1px solid #e5e7eb}

    .title{font-weight:700;margin:0}
    .sub{margin:0;color:#4b5563;font-size:.9rem}
    .row{display:flex;gap:8px;margin:.25rem 0}
    .k{min-width:8rem;color:#6b7280}

    .badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .55rem;
           border-radius:999px;font-size:.75rem;font-weight:700}
    .b-pend{background:#fef3c7;color:#9a3412}
    .b-pay{background:#fde68a;color:#92400e}
    .b-paid{background:#d1fae5;color:#065f46}
    .b-ok{background:#dcfce7;color:#166534}
    .b-no{background:#fee2e2;color:#991b1b}

    /* 🎨 Botones con tu paleta */
    .btn{
      display:inline-block;padding:.55rem .9rem;font-weight:600;border-radius:.55rem;
      line-height:1;text-decoration:none;transition:.15s;min-width:110px;text-align:center;
      color:#fff;border:none;
    }
    .btn-validar{background:#48CCA9}.btn-validar:hover{background:#36b797}
    .btn-aux{background:#48AECC}.btn-aux:hover{background:#3797b5}       /* filtrar/limpiar/asignar */
    .btn-exportar{background:#486BCC}.btn-exportar:hover{background:#3a58a6}
    .btn-rechazar{background:#CC486B}.btn-rechazar:hover{background:#a63a57}
    .btn-detalle{background:#CCA948;color:#111827}.btn-detalle:hover{background:#b8933a;color:#fff}

    /* Centrado de acciones */
    .actions-centered{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}
  </style>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      {{-- 🔹 Ejemplo de toolbar arriba (Filtrar / Limpiar / Exportar) --}}
      <div class="mb-4 flex gap-2 flex-wrap">
        <form method="GET" class="flex gap-2">
          <input type="text" name="buscar" placeholder="Buscar…" class="border rounded px-3 py-2">
          <button class="btn btn-aux" type="submit">Filtrar</button>
          <a href="{{ route('admin.postulaciones.index') }}" class="btn btn-aux">Limpiar</a>
        </form>
        <a href="{{ route('admin.postulaciones.export') }}" class="btn btn-exportar ml-auto">Exportar CSV</a>
      </div>

      {{-- 🔹 Grid de tarjetas --}}
      <div class="grid">
        @foreach ($postulaciones as $p)
          @php
            $perfil  = optional($p->user->perfilPostulante);
            $estatus = $p->estatus ?? 'pendiente';
            $badge   = match($estatus){
              'validada' => 'b-ok',
              'rechazada'=> 'b-no',
              'pagado'   => 'b-paid',
              'pago_pendiente' => 'b-pay',
              default    => 'b-pend'
            };
            $label = ucfirst(str_replace('_',' ',$estatus));
          @endphp

          <div class="card">
            <div class="card-h">
              <div style="display:flex;gap:10px;align-items:center">
                <input type="checkbox" class="chk bulk-chk" value="{{ $p->id }}" form="bulk-form">
                <div>
                  <h3 class="title">{{ $p->convocatoria->titulo }}</h3>
                  <p class="sub">#{{ $p->id }} • {{ $p->created_at->format('Y-m-d H:i') }}</p>
                </div>
              </div>
              <span class="badge {{ $badge }}">{{ $label }}</span>
            </div>

            <div class="card-b">
              <div class="row"><div class="k">Nombre:</div><div>{{ $perfil->nombre_completo ?? $p->user->name }}</div></div>
              <div class="row"><div class="k">Email:</div><div>{{ $perfil->correo_contacto ?? $p->user->email }}</div></div>
              <div class="row"><div class="k">Teléfono:</div><div>{{ $perfil->telefono ?? '—' }}</div></div>
              <div class="row"><div class="k">Edad:</div><div>{{ $perfil->edad ?? '—' }}</div></div>
              <div class="row"><div class="k">Folio:</div><div>{{ $p->folio }}</div></div>
              <div class="row"><div class="k">Referencia:</div><div>{{ $p->referencia_pago ?? '—' }}</div></div>
              <div class="row"><div class="k">Fecha pago:</div><div>{{ $p->fecha_pago?->format('Y-m-d H:i') ?? '—' }}</div></div>
            </div>

            <div class="card-f">
              <div class="actions-centered">
                <a href="{{ route('admin.postulaciones.show', $p) }}" class="btn btn-detalle">Ver detalle</a>

                <form method="POST" action="{{ route('admin.postulaciones.update', $p) }}">
                  @csrf @method('PATCH')
                  <input type="hidden" name="estatus" value="validada">
                  <button type="submit" class="btn btn-validar">Validar</button>
                </form>

                <form method="POST" action="{{ route('admin.postulaciones.update', $p) }}">
                  @csrf @method('PATCH')
                  <input type="hidden" name="estatus" value="rechazada">
                  <button type="submit" class="btn btn-rechazar">Rechazar</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</x-app-layout>
