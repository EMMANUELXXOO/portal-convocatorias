<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Convocatorias
    </h2>
  </x-slot>

  <style>
    .card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:14px;
      box-shadow:0 1px 2px rgba(0,0,0,.05);
      display:flex;
      flex-direction:column;
      overflow:hidden;
    }

    /* Contenedor de la imagen */
    .thumb-wrap {
      position: relative;
      border-radius: 12px 12px 0 0;
      overflow: hidden;
      border-bottom:1px solid #e5e7eb;
      background: #f8fafc;
      width: 100%;
      aspect-ratio: 16/9; /* marco uniforme */
    }

    /* Imagen */
    .thumb {
      width: 100%;
      height: 100%;
      object-fit: contain;   /* se muestra completa */
      object-position: center;
      display: block;
      background: #f3f4f6;
    }

    .card-body {
      padding:16px;
      display:flex;
      flex-direction:column;
      flex:1;
    }

    .card h3 {
      font-weight:700;
      font-size:1.1rem;
      margin-bottom:.25rem;
    }

    .badge {
      display:inline-block;
      border-radius:999px;
      padding:.25rem .6rem;
      font-size:.75rem;
      font-weight:600;
    }
    .b-activa{background:#dcfce7;color:#166534}
    .b-inactiva{background:#fee2e2;color:#991b1b}
    .b-cerrada{background:#fef3c7;color:#92400e}
    .b-borrador{background:#e0e7ff;color:#3730a3}

    .muted {
      color:#6b7280;
      font-size:.85rem;
    }
  </style>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      @if($convocatorias->isEmpty())
        <div class="p-6 bg-white shadow-sm sm:rounded-lg text-gray-500">
          No hay convocatorias aún.
        </div>
      @else
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          @foreach($convocatorias as $c)
            @php
              $thumb = $c->portada_url ?? asset('images/convocatoria-placeholder.jpg');
              $badgeClass = match($c->estatus) {
                'activa'   => 'b-activa',
                'inactiva' => 'b-inactiva',
                'cerrada'  => 'b-cerrada',
                'borrador' => 'b-borrador',
                default    => 'b-inactiva',
              };
              $placeholder = asset('images/convocatoria-placeholder.jpg');
            @endphp

            <div class="card">
              <a href="{{ route('convocatorias.show', $c) }}" class="thumb-wrap">
                <img
                  class="thumb"
                  src="{{ $thumb }}"
                  alt="Portada de {{ $c->titulo }}"
                  loading="lazy"
                  decoding="async"
                  onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
              </a>

              <div class="card-body">
                <h3>
                  <a href="{{ route('convocatorias.show', $c) }}" class="text-indigo-600 hover:underline">
                    {{ $c->titulo }}
                  </a>
                </h3>

                <p class="muted mb-2">
                  {{ $c->fecha_inicio?->format('Y-m-d') ?? '—' }}
                  → {{ $c->fecha_fin?->format('Y-m-d') ?? '—' }}
                </p>

                @if($c->descripcion)
                  <p class="text-gray-700 mb-3">
                    {{ \Illuminate\Support\Str::limit($c->descripcion, 120) }}
                  </p>
                @endif

                <div class="mt-auto flex items-center justify-between">
                  @if($c->estatus)
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($c->estatus) }}</span>
                  @endif
                  <a href="{{ route('convocatorias.show', $c) }}" class="text-sm text-indigo-600 hover:underline">
                    Ver más
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-6">
          {{ $convocatorias->links() }}
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
