<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-2xl text-gray-900 leading-tight">
      Convocatorias disponibles
    </h1>
  </x-slot>

  <style>
    /* Colores de marca */
    :root{
      --c-primary:#48CCA9; /* verde aqua */
      --c-secondary:#48AECC; /* azul teal */
    }

  /* Tarjeta */
  .c-card{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    box-shadow:0 1px 2px rgba(0,0,0,.05);
    overflow:hidden;
    display:flex;
    flex-direction:column;
    transition:box-shadow .2s;
  }
  .c-card:hover{box-shadow:0 4px 10px rgba(0,0,0,.07)}
    /* Imagen */
  .c-media{
    width:100%;
    height:auto;              /* deja que la altura sea proporcional */
    object-fit:contain;       /* muestra completa la imagen */
    object-position:center;
    display:block;
    background:#f3f4f6;       /* fondo gris claro de relleno */
  }

  .c-body{padding:16px}
  .c-sep{border-top:1px solid #e5e7eb}

    /* Botones */
    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:.65rem 1rem;
      border-radius:10px;
      font-weight:700;
      text-decoration:none;
      transition:filter .2s;
    }
    .btn-main{background:var(--c-secondary);color:#fff}
    .btn-main:hover{filter:brightness(.95)}
    .btn-register{background:var(--c-primary);color:#fff}
    .btn-register:hover{filter:brightness(.95)}
    .btn-login{background:var(--c-secondary);color:#fff}
    .btn-login:hover{filter:brightness(.95)}
  </style>

  <div class="py-6 max-w-7xl mx-auto px-4">

    {{-- Aviso para invitados --}}
    @guest
      <div class="mb-6 bg-white border border-gray-200 rounded-xl shadow p-6 text-center">
        <h2 class="text-xl font-bold text-gray-800 mb-2">¡Bienvenido aspirante!</h2>
        <p class="text-gray-600 mb-5">Para inscribirte a una convocatoria necesitas una cuenta.</p>
        <div class="flex flex-wrap justify-center gap-4">
          <a href="{{ route('register') }}" class="btn btn-register">Registrarme</a>
          <a href="{{ route('login') }}" class="btn btn-login">Iniciar sesión</a>
        </div>
      </div>
    @endguest

    {{-- Lista de convocatorias --}}
    @if($convocatorias->isEmpty())
      <div class="p-6 bg-white shadow-sm sm:rounded-lg text-gray-500 text-center">
        No hay convocatorias activas en este momento.
      </div>
    @else
      {{-- Cards en grid responsivo --}}
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mt-10">
        @foreach($convocatorias as $c)
          @php
            $thumb = $c->portada_url ?? asset('images/convocatoria-placeholder.jpg');
          @endphp

          <div class="c-card">
            <a href="{{ route('convocatorias.show', $c) }}">
              <img src="{{ $thumb }}" alt="Portada de {{ $c->titulo }}" class="c-media">
            </a>

            <div class="c-body c-sep flex flex-col h-full">
              <h3 class="font-bold text-lg mb-1 text-gray-900">
                <a href="{{ route('convocatorias.show', $c) }}" class="hover:underline">
                  {{ $c->titulo }}
                </a>
              </h3>
              <p class="text-sm text-gray-500 mb-2">
                {{ $c->fecha_inicio?->format('Y-m-d') ?? '—' }} → {{ $c->fecha_fin?->format('Y-m-d') ?? '—' }}
              </p>
              <p class="text-gray-700 mb-4 flex-1">
                {{ \Illuminate\Support\Str::limit($c->descripcion, 100) }}
              </p>

              <a href="{{ route('convocatorias.show', $c) }}" class="btn btn-main w-full mt-auto">
                Ver detalles
              </a>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Paginación --}}
      <div class="mt-8">
        {{ $convocatorias->links() }}
      </div>
    @endif
  </div>
</x-app-layout>
