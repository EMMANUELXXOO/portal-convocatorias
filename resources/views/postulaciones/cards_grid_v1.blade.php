{{-- resources/views/postulaciones/cards_grid_v1.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-900 leading-tight">Postulaciones (Admin)</h2>
    <p class="text-sm text-gray-500">Gestiona aspirantes, pagos y asignaciones.</p>
  </x-slot>

  <style>
    /* ===== Tarjeta de filtros ===== */
    .filter-card{
      background:#fff;border:1px solid #e5e7eb;border-radius:14px;
      padding:14px 16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center
    }
    .field{position:relative;display:flex;align-items:center}
    .field input,.field select{
      border:1px solid #d1d5db;border-radius:10px;
      padding:.55rem .9rem .55rem 2.1rem;font-size:.95rem;background:#fff;min-width:220px
    }
    .field.icon-left::before{
      content:"";position:absolute;left:.7rem;width:1rem;height:1rem;opacity:.55;
      background-repeat:no-repeat;background-position:center;background-size:1rem
    }
    .ico-search::before{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364738b' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M21 21l-4.3-4.3'/%3E%3C/svg%3E")}
    .ico-hash::before  {background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364738b' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='M10 3L8 21M16 3l-2 18M4 8h16M3 16h16'/%3E%3C/svg%3E")}
    .ico-filter::before{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364738b' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='M3 5h18M6 12h12M10 19h4'/%3E%3C/svg%3E")}

    /* ===== Botones (paleta) ===== */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:.45rem;
      padding:.58rem .95rem;border-radius:12px;font-weight:700;border:1.5px solid transparent;
      min-height:42px;min-width:128px;white-space:nowrap;font-size:.92rem;text-align:center;
      color:#fff;text-decoration:none;transition:.15s;
    }
    .btn-validar{background:#48CCA9}.btn-validar:hover{background:#39b998}
    .btn-aux{background:#48AECC}.btn-aux:hover{background:#3a97b4}
    .btn-export{background:#486BCC}.btn-export:hover{background:#3b59a8}
    .btn-rechazar{background:#CC486B}.btn-rechazar:hover{background:#a63a57}
    .btn-detalle{background:#48AECC}.btn-detalle:hover{background:#3a97b4}

    /* ===== Grid tarjetas ===== */
    .grid{display:grid;gap:16px}
    @media(min-width:768px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(min-width:1280px){.grid{grid-template-columns:repeat(3,minmax(0,1fr))}}

    /* ===== Tarjeta ===== */
    .card{
      background:#fff;border:1px solid #e5e7eb;border-radius:14px;
      box-shadow:0 1px 2px rgba(0,0,0,.05);
      display:flex;flex-direction:column;
    }
    .card-h{
      padding:14px 16px;border-bottom:1px solid #e5e7eb;
      display:flex;justify-content:space-between;align-items:center;gap:12px
    }
    .card-b{
      padding:14px 16px;flex:1; /* ocupa todo el espacio disponible */
    }
    .card-f{
      padding:14px 16px;border-top:1px solid #e5e7eb;
      margin-top:auto; /* siempre al fondo */
    }
    .actions-centered{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}

    .title{font-weight:700;margin:0}
    .sub{margin:0;color:#4b5563;font-size:.9rem}
    .row{display:flex;gap:8px;margin:.25rem 0}
    .k{min-width:8rem;color:#6b7280}

    /* Estado */
    .badge{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .55rem;
           border-radius:999px;font-size:.75rem;font-weight:700}
    .b-pend{background:#fef3c7;color:#9a3412}
    .b-pay {background:#fde68a;color:#92400e}
    .b-paid{background:#d1fae5;color:#065f46}
    .b-ok  {background:#dcfce7;color:#166534}
    .b-no  {background:#fee2e2;color:#991b1b}

    /* Chips sesiones */
    .chips{display:flex;gap:6px;flex-wrap:wrap}
    .chip{display:inline-flex;align-items:center;gap:.35rem;padding:.15rem .5rem;
          border-radius:999px;background:#f1f5f9;color:#334155;font-size:.75rem}

    .muted{color:#6b7280}
    .mono{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace}
    .chk{width:18px;height:18px}
    .hr{border:0;border-top:1px solid #e5e7eb;margin:10px 0}
  </style>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      {{-- Mensajes --}}
      @if (session('status'))
        <div class="mb-4 p-3 rounded" style="background:#ecfdf5;color:#065f46">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-4 p-3 rounded" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
      @endif

      {{-- ===== Filtros ===== --}}
      @php $qs = request()->only('buscar','convocatoria_id','estatus','tipo'); @endphp
      <form method="GET" class="filter-card mb-4">
        <div class="field icon-left ico-search">
          <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar nombre, email, folio o referencia">
        </div>
        <div class="field icon-left ico-hash">
          <input type="number" name="convocatoria_id" value="{{ request('convocatoria_id') }}" placeholder="Convocatoria ID">
        </div>
        <div class="field icon-left ico-filter">
          <select name="estatus">
            <option value="">Estatus (todos)</option>
            @foreach(['pendiente','pago_pendiente','pagado','validada','rechazada'] as $st)
              <option value="{{ $st }}" @selected(request('estatus')===$st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
            @endforeach
          </select>
        </div>
        <div class="field icon-left ico-filter">
          <select name="tipo">
            <option value="">Tipo (ambos)</option>
            <option value="psicometrico" @selected(request('tipo')==='psicometrico')>Psicométrico</option>
            <option value="conocimiento" @selected(request('tipo')==='conocimiento')>Conocimiento</option>
          </select>
        </div>

        <button class="btn btn-aux" type="submit">Filtrar</button>
        <a class="btn btn-aux" href="{{ route('admin.postulaciones.index') }}">Limpiar</a>
        <a class="btn btn-export" style="margin-left:auto" href="{{ route('admin.postulaciones.export', $qs) }}">Exportar CSV</a>
      </form>

      {{-- ===== Asignación masiva ===== --}}
      <form id="bulk-form" method="POST" class="filter-card" style="gap:10px">
        @csrf
        @isset($grupos)
          <div class="field icon-left ico-filter">
            <select id="bulk-grupo" required>
              <option value="">Selecciona un grupo…</option>
              @foreach($grupos as $g)
                <option value="{{ $g->id }}">[{{ ucfirst($g->tipo) }}] {{ $g->fecha_hora?->timezone('America/Tijuana')->format('d/M H:i') }} — {{ $g->lugar ?? 'Por definir' }} (cap: {{ $g->capacidad }})</option>
              @endforeach
            </select>
          </div>
        @else
          <div class="field icon-left ico-filter">
            <select id="bulk-grupo" disabled>
              <option value="">Filtra por convocatoria para ver grupos…</option>
            </select>
          </div>
        @endisset

        <button id="bulk-assign-btn" class="btn btn-aux" type="submit" disabled>Asignar a grupo</button>
        <span class="muted" id="bulk-count">0 seleccionados</span>
      </form>

      {{-- ===== Tarjetas ===== --}}
      <div class="grid mt-4">
        @forelse ($postulaciones as $p)
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
                <input type="checkbox" class="chk bulk-chk" name="postulacion_ids[]" value="{{ $p->id }}" form="bulk-form">
                <div>
                  <h3 class="title">{{ $p->convocatoria->titulo }}</h3>
                  <p class="sub">#{{ $p->id }} • {{ $p->created_at->format('Y-m-d H:i') }}</p>
                </div>
              </div>
              <span class="badge {{ $badge }}">{{ $label }}</span>
            </div>

            <div class="card-b">
              <div class="row"><div class="k">Nombre:</div><div>{{ $perfil->nombre_completo ?? $p->user->name }}</div></div>
              <div class="row"><div class="k">Email:</div><div class="truncate-email">{{ $perfil->correo_contacto ?? $p->user->email }}</div></div>
              <div class="row"><div class="k">Teléfono:</div><div>{{ $perfil->telefono ?? '—' }}</div></div>
              <div class="row"><div class="k">Edad:</div><div>{{ $perfil->edad ?? '—' }}</div></div>
              <div class="row"><div class="k">Folio:</div><div class="mono">{{ $p->folio }}</div></div>
              <div class="row"><div class="k">Referencia:</div><div class="mono">{{ $p->referencia_pago ?? '—' }}</div></div>
              <div class="row"><div class="k">Fecha pago:</div><div>{{ $p->fecha_pago?->format('Y-m-d H:i') ?? '—' }}</div></div>

              @if($p->gruposExamen->isNotEmpty())
                <div class="hr"></div>
                <div class="row">
                  <div class="k">Sesiones:</div>
                  <div class="chips">
                    @foreach($p->gruposExamen as $g)
                      <span class="chip" title="Grupo #{{ $g->id }}">
                        {{ ucfirst($g->tipo) }}@if($g->tipo==='otro' && $g->tipo_detalle) — {{ $g->tipo_detalle }}@endif
                        • {{ $g->fecha_hora?->timezone('America/Tijuana')->format('d/M H:i') }}
                        • {{ $g->lugar ?? 'Por definir' }}
                        • #{{ $g->id }}
                      </span>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>

            <div class="card-f">
              <div class="actions-centered">
                <a href="{{ route('admin.postulaciones.show', $p) }}" class="btn btn-detalle">Ver detalle</a>

                @if($estatus !== \App\Models\Postulacion::ESTATUS_PAGADO)
                  <form method="POST" action="{{ route('admin.postulaciones.update', $p) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="marcar_pago" value="1">
                    <button class="btn btn-aux" type="submit">Marcar pagado</button>
                  </form>
                @endif

                @if($estatus !== \App\Models\Postulacion::ESTATUS_VALIDADA)
                  <form method="POST" action="{{ route('admin.postulaciones.update', $p) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="estatus" value="{{ \App\Models\Postulacion::ESTATUS_VALIDADA }}">
                    <button class="btn btn-validar" type="submit">Validar</button>
                  </form>
                @endif

                @if($estatus !== \App\Models\Postulacion::ESTATUS_RECHAZADA)
                  <form method="POST" action="{{ route('admin.postulaciones.update', $p) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="estatus" value="{{ \App\Models\Postulacion::ESTATUS_RECHAZADA }}">
                    <button class="btn btn-rechazar" type="submit">Rechazar</button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        @empty
          <p class="muted">No hay postulaciones registradas.</p>
        @endforelse
      </div>

      <div class="mt-4">{{ $postulaciones->links() }}</div>
    </div>
  </div>
</x-app-layout>
