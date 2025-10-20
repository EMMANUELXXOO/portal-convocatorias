<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-1">
      <span class="text-xs font-semibold uppercase tracking-[0.35em] text-cr-blue/70 dark:text-slate-400">Panel</span>
      <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Convocatorias</h2>
    </div>
  </x-slot>

  <div class="py-10">
    <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
      @if($convocatorias->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white/90 px-8 py-16 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
          <h3 class="text-xl font-semibold text-slate-900 dark:text-white">Por el momento no hay convocatorias disponibles</h3>
          <p class="mt-2 max-w-2xl text-base text-slate-600 dark:text-slate-300">Cuando se publiquen nuevas oportunidades aparecerán en este espacio.</p>
        </div>
      @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
          @foreach($convocatorias as $c)
            @php
            $thumb = $c->portada_url;
              $placeholder = asset('images/convocatoria-placeholder.svg');
              $estatus = strtoupper($c->estatus ?? 'Inactiva');
              $estatusColor = match($c->estatus) {
                'activa' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
                'borrador' => 'bg-slate-200 text-slate-700 dark:bg-slate-500/20 dark:text-slate-200',
                'cerrada' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                default => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
              };
              $tipoEtiqueta = strtoupper($c->tipo ?? $c->categoria ?? $c->duracion ?? 'Convocatoria');
              $fechaInicio = $c->fecha_inicio?->translatedFormat('d \de F Y') ?? 'Por definir';
              $fechaFin = $c->fecha_fin?->translatedFormat('d \de F Y') ?? 'Por definir';
            @endphp  
        
            <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/70 bg-white/95 shadow-sm ring-1 ring-transparent transition hover:-translate-y-1 hover:shadow-soft hover:ring-cr-sky/40 dark:border-slate-800/80 dark:bg-slate-900/80">
              <a href="{{ route('convocatorias.show', $c) }}" class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
                <img
                  src="{{ $thumb }}"
                  alt="Portada de {{ $c->titulo }}"
                  loading="lazy"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                  onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                >
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-900/30 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
              </a>
              <div class="flex flex-1 flex-col gap-4 p-6">
                <span class="inline-flex items-center self-start rounded-full bg-cr-blue/10 px-3 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.3em] text-cr-blue dark:bg-cr-blue/20 dark:text-slate-200">{{ $tipoEtiqueta }}</span>

                <h3 class="text-lg font-semibold text-slate-900 transition group-hover:text-cr-blue dark:text-white dark:group-hover:text-cr-sky">
                  <a href="{{ route('convocatorias.show', $c) }}" class="focus:outline-none focus-visible:ring-2 focus-visible:ring-cr-sky focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-900">
                    {{ $c->titulo }}
                  </a>
                </h3>

                <dl class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                  <div class="flex items-center gap-2">
                    <dt class="font-semibold text-slate-700 dark:text-slate-100">Apertura</dt>
                    <dd>{{ $fechaInicio }}</dd>
                  </div>
                  <div class="flex items-center gap-2">
                    <dt class="font-semibold text-slate-700 dark:text-slate-100">Cierre</dt>
                    <dd>{{ $fechaFin }}</dd>
                  </div>
                </dl>

                @if($c->descripcion)
                
                  <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    {{ \Illuminate\Support\Str::limit($c->descripcion, 140) }}
                  </p>
                @endif

              
                 <div class="mt-auto flex items-center justify-between pt-2">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.2em] {{ $estatusColor }}">{{ $estatus }}</span>
                  <a href="{{ route('convocatorias.show', $c) }}" class="inline-flex items-center gap-2 rounded-full bg-cr-red px-5 py-2 text-sm font-semibold text-white shadow shadow-cr-red/30 transition hover:-translate-y-0.5 hover:bg-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cr-red">Ver detalles</a>
                </div>
              </div>
            </article>
          @endforeach
        </div>
        <div class="mt-8">
          {{ $convocatorias->links() }}
        </div>
      @endif
    </div>
  </div>
</x-app-layout>