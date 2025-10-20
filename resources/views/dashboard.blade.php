{{-- resources/views/dashboard.blade.php --}}
<x-avisos-publicos audiencia="aspirantes" />

<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-2xl text-gray-900 leading-tight">
      Dashboard
    </h1>
  </x-slot>

  <style>
    :root{
      --brand-1:#48AECC; /* azul */
      --brand-2:#48CCA9; /* verde aqua */
      --ok:#16a34a;      /* verde */
      --warn:#f59e0b;    /* amarillo */
      --muted:#6b7280;   /* gris texto */
      --line:#e5e7eb;    /* gris línea */
      --chip:#f8fafc;    /* chip bg */
    }
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .card-h{padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:700}
    .card-b{padding:18px}

    /* ===== Timeline (con línea conectando puntos) ===== */
    .timeline{
      position:relative;
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      margin:12px 0 8px;
    }
    /* barra base gris y progreso verde */
    .timeline-bar{
      position:absolute;top:15px;left:0;right:0;height:4px;
      background:var(--line);border-radius:999px;z-index:0;
    }
    .timeline-bar.progress{
      position:absolute;top:15px;left:0;height:4px;
      background:var(--ok);border-radius:999px;z-index:1;width:0;
      transition:width .3s ease;
    }

    .step{position:relative;text-align:center;padding-top:10px;padding-bottom:.5rem;z-index:2;flex:1}
    .step .dot{
      width:30px;height:30px;border-radius:999px;margin:0 auto;background:#e5e7eb;
      display:grid;place-items:center;font-weight:900;color:#fff;box-shadow:0 1px 1px rgba(0,0,0,.05)
    }
    .step .label{margin-top:.5rem;font-weight:700}

    /* estados del punto */
    .is-done .dot{background:var(--ok)}
    .is-pend .dot{background:var(--warn)}
    .is-future .dot{background:#d1d5db}

    .btn{display:inline-flex;align-items:center;justify-content:center;padding:.65rem 1rem;border-radius:10px;font-weight:700;text-decoration:none;color:#fff}
    .btn-a{background:var(--brand-1)}
    .btn-b{background:var(--brand-2)}
    .btn + .btn{margin-left:.75rem}

    /* lista fechas */
    .row{display:flex;align-items:center;gap:.6rem;border:1px solid #e5e7eb;border-radius:12px;padding:.55rem .75rem}
    .ico{width:28px;height:28px;display:grid;place-items:center;border-radius:999px;background:#eef2ff;color:#374151;font-weight:800}
    .kind{font-weight:700}
    .muted{color:var(--muted)}
  </style>

  <div class="py-6 max-w-5xl mx-auto px-4 space-y-6">

    {{-- ========== Progreso / Timeline ========== --}}
    <div class="card">
      <div class="card-h">Tu progreso</div>
      <div class="card-b">

        @php
          // índice del primer pendiente
          $firstPendingIndex = null;
          foreach ($steps as $i => $st) { if (empty($st['done'])) { $firstPendingIndex = $i; break; } }

          // cuántos pasos completados (asumiendo flujo secuencial)
          $total = count($steps);
          $completed = is_null($firstPendingIndex) ? $total : $firstPendingIndex;

          // porcentaje de segmentos completados (entre puntos)
          $segments = max(0, $total - 1);
          $completedSegments = max(0, $completed - 1);
          $progressPct = $segments ? ($completedSegments / $segments) * 100 : 0;
        @endphp

        <div class="timeline">
          {{-- línea base --}}
          <div class="timeline-bar"></div>
          {{-- progreso verde hasta el último paso completado --}}
          <div class="timeline-bar progress" style="width: {{ $progressPct }}%"></div>

          @foreach($steps as $i => $st)
            @php
              $state = $st['done'] ? 'is-done' : ($firstPendingIndex === $i ? 'is-pend' : 'is-future');
              $icon  = $st['done'] ? '✓' : ($firstPendingIndex === $i ? '!' : '•');
            @endphp

            <div class="step {{ $state }}">
              <div class="dot" aria-hidden="true">{{ $icon }}</div>
              <div class="label">{{ $st['label'] }}</div>
            </div>
          @endforeach
        </div>

        <div class="mt-3">
          <a href="{{ route('convocatorias.index') }}" class="btn btn-a">Ver convocatorias</a>
          <a href="{{ route('perfil.create') }}" class="btn btn-b">Editar/Completar mi perfil</a>
        </div>
      </div>
    </div>

    {{-- ========== Fechas (lista completa) ========== --}}
    <div class="card">
      <div class="card-h">Fechas</div>
      <div class="card-b space-y-2">
        @php
          // Normaliza a colección (por si viene null)
          $fechasAsignadas = collect($fechasAsignadas ?? []);
          $iconFor = function($tipo){
            return match(strtolower($tipo)){
              'psicometrico' => '🧠',
              'conocimiento' => '📝',
              'entrevista'   => '👤',
              default        => '📅',
            };
          };
          $labelFor = function($tipo){
            return match(strtolower($tipo)){
              'psicometrico' => 'Psicométrico',
              'conocimiento' => 'Conocimiento',
              'entrevista'   => 'Entrevista',
              default        => ucfirst($tipo),
            };
          };
        @endphp

        @if($fechasAsignadas->isNotEmpty())
          @foreach($fechasAsignadas as $f)
            <div class="row">
              <div class="ico">{{ $iconFor($f->tipo) }}</div>
              <div>
                <div class="kind">{{ $labelFor($f->tipo) }}</div>
                <div class="muted">
                  {{ $f->when->format('d/M/Y H:i') }}
                  @if($f->lugar) • {{ $f->lugar }} @endif
                </div>
              </div>
            </div>
          @endforeach
        @else
          <p class="text-gray-500 text-sm">Aún no tienes fechas asignadas.</p>
        @endif
      </div>
    </div>

    {{-- ========== Últimas postulaciones ========== --}}
    <div class="card">
      <div class="card-h">Tus últimas postulaciones</div>
      <div class="card-b space-y-3">
        @php $ultimasPostulaciones = $postulaciones ?? collect(); @endphp
        @forelse($ultimasPostulaciones as $p)
          <div class="flex justify-between items-center border rounded-lg px-4 py-3">
            <div>
              <div class="font-bold text-gray-800">{{ $p->convocatoria->titulo }}</div>
              <div class="text-sm text-gray-500">Creada: {{ $p->created_at->format('Y-m-d') }}</div>
            </div>
            <div class="flex items-center gap-3">
              <span class="px-3 py-1 rounded-full text-sm font-semibold
                {{ $p->estatus === 'pagado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($p->estatus) }}
              </span>
              <a href="{{ route('postulaciones.index') }}" class="btn btn-a text-sm px-3 py-1">Ver</a>
            </div>
          </div>
        @empty
          <p class="text-gray-500 text-sm">Aún no tienes postulaciones registradas.</p>
        @endforelse
      </div>
    </div>

  </div>
</x-app-layout>
