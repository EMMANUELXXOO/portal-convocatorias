{{-- resources/views/convocatorias/show.blade.php --}}
<x-app-layout>
  {{-- Incluye Bootstrap solo si tu layout no lo carga ya --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <x-slot name="header">
    <h1 class="font-semibold text-2xl text-gray-900 leading-tight">
      {{ $convocatoria->titulo }}
    </h1>
  </x-slot>

  <style>
    :root{
      --brand:#486BCC; --accent:#48CCA9; --surface:#fff; --muted:#6b7280; --ring:#e5e7eb; --chip:#f1f5f9;
    }
    .container{max-width:1100px;margin:0 auto;padding:0 1rem}

    /* ===== HERO (marco del carrusel) ===== */
    .hero{
      position:relative;width:100%;max-width:980px;margin:0 auto 18px;border-radius:18px;overflow:hidden;
      border:1px solid var(--ring);aspect-ratio:16/9;background:#f3f4f6;box-shadow:0 4px 18px rgba(0,0,0,.06);
    }
    .hero .carousel,.hero .carousel-inner,.hero .carousel-item{height:100%}
    .hero-img{width:100%;height:100%;display:block;object-position:center}
    .hero--cover  .hero-img{object-fit:cover}
    .hero--contain .hero-img{object-fit:contain;background:#fff}

    .status-badge{background:#dcfce7;color:#166534;border-radius:999px;padding:.35rem .6rem;font-weight:700;font-size:.78rem}
    .price-chip{background:rgba(0,0,0,.55);color:#fff;border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(4px);border-radius:10px;padding:.35rem .6rem;font-weight:700}
    .chips{display:flex;gap:8px;flex-wrap:wrap}
    .chip{background:var(--chip);border:1px solid var(--ring);border-radius:999px;padding:.3rem .6rem;font-weight:700;font-size:.8rem;color:#334155}
    .title-shadow{font-weight:800;font-size:clamp(1.1rem, 2.5vw, 1.35rem);text-shadow:0 2px 10px rgba(0,0,0,.35)}
    .caption-wrap{position:absolute;left:0;right:0;bottom:0;padding:14px 16px;display:flex;justify-content:space-between;align-items:flex-end;gap:12px;color:#fff;background:linear-gradient(180deg, rgba(0,0,0,.0) 40%, rgba(0,0,0,.25) 100%)}

    /* ===== Layout / tarjetas ===== */
    .grid{display:grid;gap:18px;margin-top:18px}
    @media(min-width:1024px){.grid-2{grid-template-columns:1.7fr 1fr}}
    .card{background:var(--surface);border:1px solid var(--ring);border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    .card-h{padding:14px 16px;border-bottom:1px solid var(--ring);font-weight:700}
    .card-b{padding:16px}
    .muted{color:var(--muted)}
    .sep{border-top:1px solid var(--ring);margin:10px 0}
    .kv{display:flex;justify-content:space-between;gap:10px;margin:.4rem 0}.kv .k{color:var(--muted)}.kv .v{font-weight:700}
    .btn{display:inline-flex;align-items:center;justify-content:center;padding:.6rem 1rem;border-radius:12px;font-weight:800;text-decoration:none;border:1.5px solid transparent;transition:.15s}
    .btn-cta{background:var(--accent);color:#fff}.btn-cta:hover{filter:brightness(.95)}
    .btn-ghost{background:#fff;border-color:var(--ring);color:#111827}.btn-ghost:hover{background:#f8fafc}
    .btn-brand{background:var(--brand);color:#fff}.btn-brand:hover{filter:brightness(.95)}
    .btn-line{display:flex;flex-wrap:wrap;gap:10px}
    @media(min-width:1024px){.sticky-col{position:sticky;top:90px;align-self:start}}

    .gal{display:grid;gap:10px;grid-template-columns:repeat(2,1fr)}
    @media(min-width:768px){.gal{grid-template-columns:repeat(3,1fr)}}
    .gal img{width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:12px;border:1px solid var(--ring);background:#f8fafc;display:block}
    .ph{display:grid;place-items:center;height:120px;border-radius:12px;border:1px solid var(--ring);background:#f3f4f6;color:#9ca3af}
    .lead{line-height:1.75}
  </style>

  @php
     $placeholder = asset('images/convocatoria-placeholder.svg');

    // Usa directamente lo que expone el modelo:
    $slides = array_values(array_filter((array)($convocatoria->carousel_slides ?? [])));
    if (empty($slides)) {
        // fallback si no hay nada
      $slides = [$convocatoria->portada_url];
    }

    $coverClass = ($convocatoria->hero_fit ?? 'cover') === 'contain' ? 'hero--contain' : 'hero--cover';
    $usarCarrusel = (bool) ($convocatoria->carousel_enabled ?? true);
    $fi = $convocatoria->fecha_inicio?->format('Y-m-d') ?? '—';
    $ff = $convocatoria->fecha_fin?->format('Y-m-d') ?? '—';
  @endphp

  <div class="py-6">
    <div class="container">

      {{-- ===== HERO centrado ===== --}}
      <div class="hero {{ $coverClass }}">
        @if($usarCarrusel && count($slides) > 1)
          <div id="convocatoriaCarousel" class="carousel slide h-100" data-bs-ride="carousel">
            <div class="carousel-indicators">
              @foreach($slides as $i => $_)
                <button type="button" data-bs-target="#convocatoriaCarousel" data-bs-slide-to="{{ $i }}"
                        @class(['active'=>$i===0]) @if($i===0) aria-current="true" @endif
                        aria-label="Slide {{ $i+1 }}"></button>
              @endforeach
            </div>

            <div class="carousel-inner h-100">
              @foreach($slides as $i => $img)
                <div class="carousel-item h-100 @if($i===0) active @endif" data-bs-interval="5000">
                  <img src="{{ $img }}" class="hero-img" alt="Imagen {{ $i+1 }} de {{ $convocatoria->titulo }}"
                     onerror="this.onerror=null;this.src='{{ $placeholder }}';" loading="lazy">
                  @if($i===0)
                    <div class="caption-wrap">
                      <div class="title-shadow">{{ $convocatoria->titulo }}</div>
                      <div class="chips">
                        @if($convocatoria->estatus)<span class="status-badge">{{ ucfirst($convocatoria->estatus) }}</span>@endif
                        @if(!is_null($convocatoria->precio_ficha))<span class="price-chip">Ficha: ${{ number_format((float)$convocatoria->precio_ficha,2) }} MXN</span>@endif
                        <span class="chip">Inicio: <b>{{ $fi }}</b></span>
                        <span class="chip">Cierre: <b>{{ $ff }}</b></span>
                      </div>
                    </div>
                  @endif
                </div>
              @endforeach
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#convocatoriaCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#convocatoriaCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Siguiente</span>
            </button>
          </div>
        @else
          {{-- Modo estático (sin carrusel) --}}
          <img src="{{ $slides[0] ?? $placeholder }}" class="hero-img"
               alt="Portada de {{ $convocatoria->titulo }}"
             onerror="this.onerror=null;this.src='{{ $placeholder }}';" loading="lazy">
          <div class="caption-wrap">
            <div class="title-shadow">{{ $convocatoria->titulo }}</div>
            <div class="chips">
              @if($convocatoria->estatus)<span class="status-badge">{{ ucfirst($convocatoria->estatus) }}</span>@endif
              @if(!is_null($convocatoria->precio_ficha))<span class="price-chip">Ficha: ${{ number_format((float)$convocatoria->precio_ficha,2) }} MXN</span>@endif
              <span class="chip">Inicio: <b>{{ $fi }}</b></span>
              <span class="chip">Cierre: <b>{{ $ff }}</b></span>
            </div>
          </div>
        @endif
      </div>

      {{-- ===== CONTENIDO ===== --}}
      <div class="grid grid-2">
        <div class="grid" style="gap:18px">
          <div class="card">
            <div class="card-h">Descripción</div>
            <div class="card-b">
              @if($convocatoria->descripcion)
                <div class="lead">{!! nl2br(e($convocatoria->descripcion)) !!}</div>
              @else <p class="muted">Próximamente…</p> @endif
            </div>
          </div>

          <div class="card">
            <div class="card-h">Costos</div>
            <div class="card-b">
              <div class="kv"><div class="k">Ficha de aspirante</div><div class="v">${{ number_format((float)($convocatoria->precio_ficha ?? 0),2) }} MXN</div></div>
              <div class="kv"><div class="k">Inscripción</div><div class="v">${{ number_format((float)($convocatoria->precio_inscripcion ?? 0),2) }} MXN</div></div>
              <div class="kv"><div class="k">Mensualidad</div><div class="v">${{ number_format((float)($convocatoria->precio_mensualidad ?? 0),2) }} MXN</div></div>
              @if(!is_null($convocatoria->cupo_total))
                <div class="kv"><div class="k">Cupo</div><div class="v">{{ $convocatoria->cupo_total }}</div></div>
              @endif
            </div>
          </div>

          <div class="card">
            <div class="card-h">Requisitos</div>
            <div class="card-b">
              <div class="mb-2 font-semibold">Generales</div>
              {!! $convocatoria->requisitos_generales ? '<div class="lead">'.nl2br(e($convocatoria->requisitos_generales)).'</div>' : '<p class="muted">—</p>' !!}
              <div class="sep"></div>
              <div class="mb-2 font-semibold">Para examen y entrevista</div>
              {!! $convocatoria->requisitos_examen_entrevista ? '<div class="lead">'.nl2br(e($convocatoria->requisitos_examen_entrevista)).'</div>' : '<p class="muted">—</p>' !!}
              <div class="sep"></div>
              <div class="mb-2 font-semibold">Documentos al ser aceptado</div>
              {!! $convocatoria->documentos_requeridos ? '<div class="lead">'.nl2br(e($convocatoria->documentos_requeridos)).'</div>' : '<p class="muted">—</p>' !!}
            </div>
          </div>

          {{-- Mosaico de galería (usa URLs públicas del modelo) --}}
          @php
            $galeria = array_values(array_unique(array_filter((array)($convocatoria->galeria_urls_public ?? []))));
           if ($convocatoria->portada_path) array_unshift($galeria, $convocatoria->portada_url);
            $galeria = array_values(array_unique($galeria));
          @endphp
          <div class="card">
            <div class="card-h">Galería</div>
            <div class="card-b">
              @if(count($galeria))
                <div class="gal">
                  @foreach($galeria as $img)
                    <img src="{{ $img }}" loading="lazy" alt="Imagen de {{ $convocatoria->titulo }}"
                         onerror="this.style.display='none'">
                  @endforeach
                </div>
              @else
                <div class="ph">Sin imágenes</div>
              @endif
            </div>
          </div>

          @if($convocatoria->notas)
            <div class="card">
              <div class="card-h">Notas</div>
              <div class="card-b"><div class="lead">{!! nl2br(e($convocatoria->notas)) !!}</div></div>
            </div>
          @endif
        </div>

        {{-- Sidebar --}}
        <aside class="grid sticky-col" style="gap:18px">
          <div class="card">
            <div class="card-h">Acción</div>
            <div class="card-b">
              @auth
                @php
                  $user = auth()->user();
                  $perfil = $user->perfilPostulante;
                  $postulacionActual = $user->postulaciones()->where('convocatoria_id',$convocatoria->id)->latest('id')->first();
                  $inspeccion = \Illuminate\Support\Facades\Gate::forUser($user)->inspect('postular', $convocatoria);
                  $EST_PAGADO = \App\Models\Postulacion::ESTATUS_PAGADO ?? 'pagado';
                @endphp

                @if (!$perfil)
                  <div class="mb-3 p-3 rounded bg-yellow-50 text-yellow-800">Completa tu ficha para poder postularte.</div>
                  <a href="{{ route('perfil.create') }}" class="btn btn-ghost w-full">Completar ficha</a>
                @elseif($postulacionActual)
                  <div class="btn-line">
                    @if($postulacionActual->estatus === $EST_PAGADO)
                      <a href="{{ route('postulaciones.recibo', $postulacionActual) }}" class="btn btn-brand w-full" target="_blank" rel="noopener">Ver/descargar recibo</a>
                      <form method="POST" action="{{ route('postulaciones.reenviar-recibo', $postulacionActual) }}">@csrf
                        <button type="submit" class="btn btn-ghost w-full">Reenviar por correo</button>
                      </form>
                    @else
                      <a href="{{ route('postulaciones.pago', $postulacionActual) }}" class="btn btn-cta w-full">Continuar con el pago</a>
                    @endif
                    <a class="btn btn-ghost w-full" href="{{ route('postulaciones.index') }}">Mis postulaciones</a>
                  </div>
                @else
                  @if ($inspeccion->allowed())
                    <form method="POST" action="{{ route('convocatorias.aplicar', $convocatoria) }}">@csrf
                      <button type="submit" class="btn btn-cta w-full">Postularme</button>
                    </form>
                  @else
                    <div class="p-3 rounded bg-gray-100 text-gray-700">{{ $inspeccion->message() ?: 'En este momento no es posible postular.' }}</div>
                  @endif
                @endif
              @else
                <div class="btn-line">
                  <a href="{{ route('register') }}" class="btn btn-cta w-full">Registrarme</a>
                  <a href="{{ route('login') }}" class="btn btn-brand w-full">Iniciar sesión</a>
                </div>
              @endauth
            </div>
          </div>

          <div class="card">
            <div class="card-h">Fechas clave</div>
            <div class="card-b">
              <div class="kv"><div class="k">Resultados</div><div class="v">{{ $convocatoria->fecha_publicacion_resultados?->format('Y-m-d') ?? '—' }}</div></div>
              <div class="kv"><div class="k">Inicio de clases</div><div class="v">{{ $convocatoria->fecha_inicio_clases?->format('Y-m-d') ?? '—' }}</div></div>
            </div>
          </div>

          <div class="card">
            <div class="card-h">Programa</div>
            <div class="card-b">
              <div class="kv"><div class="k">Duración</div><div class="v">{{ $convocatoria->duracion ?: '—' }}</div></div>
              <div class="kv"><div class="k">Certificaciones</div><div class="v">{{ $convocatoria->certificaciones_adicionales ?: '—' }}</div></div>
              <div class="sep"></div>
              <div class="kv"><div class="k">Matutino</div><div class="v">{{ $convocatoria->horario_matutino ?: '—' }}</div></div>
              <div class="kv"><div class="k">Vespertino</div><div class="v">{{ $convocatoria->horario_vespertino ?: '—' }}</div></div>
            </div>
          </div>

          <div class="card">
            <div class="card-h">Ubicación y contacto</div>
            <div class="card-b">
              <div class="kv"><div class="k">Ubicación</div><div class="v">{{ $convocatoria->ubicacion ?: '—' }}</div></div>
              <div class="kv"><div class="k">Teléfono 1</div><div class="v">{{ $convocatoria->telefono_1 ?: '—' }}</div></div>
              <div class="kv"><div class="k">Teléfono 2</div><div class="v">{{ $convocatoria->telefono_2 ?: '—' }}</div></div>
              <div class="kv"><div class="k">Correo 1</div><div class="v">{{ $convocatoria->correo_1 ?: '—' }}</div></div>
              <div class="kv"><div class="k">Correo 2</div><div class="v">{{ $convocatoria->correo_2 ?: '—' }}</div></div>
              <div class="kv"><div class="k">Horario</div><div class="v">{{ $convocatoria->horario_atencion ?: '—' }}</div></div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</x-app-layout>
