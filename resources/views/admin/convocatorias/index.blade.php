<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Convocatorias (Admin)
        </h2>
        <p class="text-sm text-gray-500">Crea, publica y gestiona periodos de registro.</p>
      </div>
      <a href="{{ route('admin.convocatorias.create') }}" class="btn btn-export">
        + Nueva convocatoria
      </a>
    </div>
  </x-slot>

  <style>
    .badge{display:inline-block;padding:.2rem .55rem;border-radius:999px;font-weight:700;font-size:.75rem}
    .b-activa{background:#dcfce7;color:#166534}
    .b-inactiva{background:#fee2e2;color:#991b1b}
    .b-cerrada{background:#fef3c7;color:#92400e}
    .b-borrador{background:#e0e7ff;color:#3730a3}

    .btn{display:inline-flex;align-items:center;justify-content:center;padding:.56rem .9rem;border-radius:12px;font-weight:700;border:1.5px solid transparent;font-size:.92rem;line-height:1;white-space:nowrap;min-height:40px;min-width:110px;text-decoration:none;transition:.15s}
    .btn-aux{background:#48AECC;color:#fff}.btn-aux:hover{background:#3a97b4}
    .btn-validar{background:#48CCA9;color:#fff}.btn-validar:hover{background:#39b998}
    .btn-export{background:#486BCC;color:#fff}.btn-export:hover{background:#3b59a8}
    .btn-rechazar{background:#CC486B;color:#fff}.btn-rechazar:hover{background:#a63a57}
    .btn-editar{background:#CCA948;color:#111827}.btn-editar:hover{background:#b8933a;color:#fff}
    .btn-postulaciones{background:#48CCA9;color:#fff}.btn-postulaciones:hover{background:#39b998}

    .card{display:flex;flex-direction:column;gap:10px;padding:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .card-body{display:flex;flex-direction:column;gap:6px;flex-grow:1}
    .card-actions{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;padding-top:10px;border-top:1px solid #e5e7eb}

    /* === Portada: marco uniforme y imagen completa (sin recorte) === */
    .thumb-wrap{
      position:relative;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;background:#f8fafc;
      width:100%;aspect-ratio:16/9; /* marco consistente */
    }
    .thumb{
      width:100%;height:100%;           /* ocupa el marco */
      object-fit:contain;               /* se ve completa, con “barras” si no es 16:9 */
      object-position:center;display:block;background:#f3f4f6;
    }
    .thumb-badge{position:absolute;top:10px;right:10px}

    .meta{font-size:.85rem;color:#6b7280}
  </style>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      @if(session('status'))
        <div class="mb-4 p-3 rounded font-semibold" style="background:#dcfce7;color:#166534">
          {{ session('status') }}
        </div>
      @endif

      <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-sm text-gray-600 mb-1">Búsqueda</label>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Título o descripción…"
                 class="border border-gray-300 rounded-md px-3 py-2 w-64">
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Estatus</label>
          <select name="estatus" class="border border-gray-300 rounded-md px-3 py-2">
            <option value="">Todos</option>
            @foreach(['activa','inactiva','borrador','cerrada'] as $opt)
              <option value="{{ $opt }}" @selected(request('estatus')===$opt)>{{ ucfirst($opt) }}</option>
            @endforeach
          </select>
        </div>
        <button class="btn btn-aux" type="submit">Filtrar</button>
        <a href="{{ route('admin.convocatorias.index') }}" class="btn btn-aux">Limpiar</a>
      </form>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $c)
          @php
            $fi  = $c?->fecha_inicio?->format('Y-m-d') ?? '—';
            $ff  = $c?->fecha_fin?->format('Y-m-d') ?? '—';
            $fpr = $c?->fecha_publicacion_resultados?->format('Y-m-d') ?? '—';
            $fic = $c?->fecha_inicio_clases?->format('Y-m-d') ?? '—';
            $estatus = $c->estatus ?? null;
            $badgeClass = match($estatus) {
              'activa'   => 'b-activa',
              'inactiva' => 'b-inactiva',
              'cerrada'  => 'b-cerrada',
              'borrador' => 'b-borrador',
              default    => 'b-inactiva',
            };
          $thumb       = $c->portada_url;
            $placeholder = asset('images/convocatoria-placeholder.svg');
          @endphp

          <div class="card">
            <a href="{{ route('admin.convocatorias.edit', $c) }}" class="thumb-wrap">
              <img
                class="thumb"
                src="{{ $thumb }}"
                alt="Portada de {{ $c->titulo }}"
                loading="lazy"
                decoding="async"
                onerror="this.onerror=null;this.src='{{ $placeholder }}';"
              >
              @if($estatus)
                <span class="badge {{ $badgeClass }} thumb-badge">{{ ucfirst($estatus) }}</span>
              @endif
            </a>

            <div class="card-body">
              <h3 class="font-bold text-lg text-gray-900 leading-snug">
                {{ $c->titulo }}
              </h3>
              <p class="text-sm text-gray-500">
                {{ $fi }} → {{ $ff }} • Postulaciones:
                <strong>{{ (int)($c->postulaciones_count ?? 0) }}</strong>
              </p>
              <p class="meta">
                Resultados: <strong>{{ $fpr }}</strong> • Inicio clases: <strong>{{ $fic }}</strong>
              </p>
              @if(\Illuminate\Support\Str::of($c->descripcion ?? '')->trim()->isNotEmpty())
                <p class="text-gray-700">
                  {{ \Illuminate\Support\Str::limit($c->descripcion, 120) }}
                </p>
              @endif
              @if(!is_null($c->precio_ficha))
                <p class="text-sm text-gray-600">
                  Ficha de aspirante: <strong>${{ number_format((float)$c->precio_ficha, 2) }} MXN</strong>
                </p>
              @endif
            </div>

            <div class="card-actions">
              <a href="{{ route('convocatorias.show', $c) }}" class="btn btn-aux" target="_blank" rel="noopener">
                Ver pública
              </a>
              <a href="{{ route('admin.postulaciones.index') }}?convocatoria_id={{ $c->id }}" class="btn btn-postulaciones">
                Postulaciones
              </a>
              <a href="{{ route('admin.postulaciones.export') }}?convocatoria_id={{ $c->id }}" class="btn btn-export">
                Exportar
              </a>
              <a href="{{ route('admin.convocatorias.edit', $c) }}" class="btn btn-editar">
                Editar
              </a>
              <form method="POST" action="{{ route('admin.convocatorias.destroy', $c) }}"
                    onsubmit="return confirm('¿Seguro que deseas eliminar esta convocatoria?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-rechazar">Eliminar</button>
              </form>
            </div>
          </div>
        @empty
          <p class="text-gray-500">No hay convocatorias aún.</p>
        @endforelse
      </div>

      <div class="mt-6">
        {{ $items->appends(request()->only('q','estatus'))->links() }}
      </div>
    </div>
  </div>
</x-app-layout>
