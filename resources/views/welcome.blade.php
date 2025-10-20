<x-app-layout>
  <x-slot name="header">
    <div class="flex flex-col gap-2">
      <span class="text-xs font-semibold uppercase tracking-[0.35em] text-cr-blue/70 dark:text-slate-400">Portal de convocatorias</span>
      <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">Convocatorias disponibles</h1>
    </div>
  </x-slot>

  <div class="py-10">
    <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
      @guest
        <section class="relative overflow-hidden rounded-3xl border border-white/60 bg-white/90 p-8 shadow-soft backdrop-blur md:p-12 dark:border-slate-800/60 dark:bg-slate-900/80">
          <div class="absolute inset-0 bg-gradient-to-br from-cr-red/20 via-cr-sky/15 to-cr-blue/20 opacity-90 dark:from-cr-red/30 dark:via-sky-500/15 dark:to-cr-blue/40"></div>
          <div class="relative z-10 flex flex-col gap-6 text-center sm:flex-row sm:items-center sm:text-left">
            <div class="flex-1 space-y-3">
              <p class="text-sm font-semibold uppercase tracking-[0.3em] text-cr-blue dark:text-slate-200">Bienvenido aspirante</p>
              <h2 class="text-2xl font-semibold text-slate-900 md:text-3xl dark:text-white">Construye tu futuro en la Cruz Roja</h2>
              <p class="text-base text-slate-600 dark:text-slate-300">Crea tu cuenta o ingresa para postularte a la convocatoria que más se adapte a tus metas.</p>
            </div>
            <div class="flex flex-1 flex-col items-center gap-4 sm:items-end">
              <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-full bg-cr-red px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cr-red/30 transition hover:-translate-y-0.5 hover:bg-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cr-red sm:w-auto">Registrarme</a>
              <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-full border border-cr-blue/60 bg-white/70 px-6 py-3 text-sm font-semibold text-cr-blue transition hover:-translate-y-0.5 hover:border-cr-blue hover:text-cr-blue focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cr-blue dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-cr-sky dark:hover:text-white sm:w-auto">Iniciar sesión</a>
            </div>
          </div>
        </section>
      @endguest

      <section class="space-y-8">
        @if($convocatorias->isEmpty())
          <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white/80 px-8 py-16 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <h3 class="text-xl font-semibold text-slate-900 dark:text-white">Por el momento no hay convocatorias disponibles</h3>
            <p class="mt-2 max-w-xl text-base text-slate-600 dark:text-slate-300">Vuelve pronto para descubrir nuevas oportunidades académicas y de formación.</p>
          </div>
        @else
          <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($convocatorias as $c)
              @php
               $thumb = $c->portada_url;
                if (!$thumb && filled($c->portada_path)) {
                  $path = ltrim(preg_replace('~^(?:public|storage)/~', '', $c->portada_path ?? ''), '/');
                  if ($path !== '') {
                    $thumb = asset('storage/' . $path);
                  }
                }
               $thumb = $c->portada_url;
                $placeholder = asset('images/convocatoria-placeholder.svg');
                $tipoEtiqueta = strtoupper($c->tipo ?? $c->categoria ?? $c->duracion ?? 'Convocatoria');
                $fechaInicio = $c->fecha_inicio?->translatedFormat('d \de F Y') ?? 'Por definir';
                $fechaFin = $c->fecha_fin?->translatedFormat('d \de F Y') ?? 'Por definir';
              @endphp
              <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-sm ring-1 ring-transparent transition hover:-translate-y-1 hover:shadow-soft hover:ring-cr-sky/40 dark:border-slate-800/80 dark:bg-slate-900/80">
                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
                  <img
                    src="{{ $thumb }}"
                    alt="Portada de {{ $c->titulo }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                  >
                  <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-900/25 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                </div>

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
                      <dd class="text-slate-600 dark:text-slate-300">{{ $fechaInicio }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                      <dt class="font-semibold text-slate-700 dark:text-slate-100">Cierre</dt>
                      <dd class="text-slate-600 dark:text-slate-300">{{ $fechaFin }}</dd>
                    </div>
                  </dl>

           
                  @if($c->descripcion)
                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                      {{ \Illuminate\Support\Str::limit($c->descripcion, 140) }}
                    </p>
                  @endif

                  <div class="mt-auto flex items-center justify-between pt-2">
                    @if($c->estatus)
                      <span class="inline-flex items-center rounded-full bg-cr-sky/15 px-3 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-cr-blue dark:bg-cr-sky/25 dark:text-slate-100">{{ strtoupper($c->estatus) }}</span>
                    @endif
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
      </section>
    </div>
  </div>
</x-app-layout>