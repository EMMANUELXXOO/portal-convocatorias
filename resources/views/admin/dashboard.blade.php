
{{-- resources/views/admin/dashboard.blade.php --}}
<x-avisos-publicos audiencia="admin" />

<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-2xl text-gray-900 leading-tight">
      Dashboard administrador
    </h1>
  </x-slot>

  <style>
    :root{
      --brand-1:#48AECC; /* azul */
      --brand-2:#48CCA9; /* verde aqua */
      --muted:#6b7280;
      --line:#e5e7eb;
    }
    .grid-kpis{display:grid;gap:12px;grid-template-columns:repeat(4,1fr)}
    @media (max-width:1024px){.grid-kpis{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:640px){.grid-kpis{grid-template-columns:1fr}}

    .kpi{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px}
    .kpi .label{color:var(--muted);font-size:.9rem}
    .kpi .val{font-size:1.8rem;font-weight:900;line-height:1.1}

    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .card-h{padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:700}
    .card-b{padding:18px}

    /* Funnel simple */
    .bar{height:14px;border-radius:999px;background:#f3f4f6;overflow:hidden}
    .bar > span{display:block;height:100%;background:linear-gradient(90deg,var(--brand-1),var(--brand-2))}

    /* Próximos */
    .row{position:relative;display:flex;align-items:center;justify-content:space-between;gap:.75rem;border:1px solid #e5e7eb;border-radius:12px;padding:.55rem .75rem}
    .left{min-width:0}
    .title{font-weight:700}
    .sub{font-size:.85rem;color:var(--muted)}
    .pill{display:inline-block;padding:.22rem .5rem;border-radius:999px;font-weight:700;font-size:.72rem}
    .pill-psico{background:#e0f2fe;color:#075985}
    .pill-cono{background:#dcfce7;color:#166534}
    .pill-entre{background:#fef3c7;color:#92400e}
    .pill-act{background:#e5e7eb;color:#374151}

    /* Incidencias */
    .tabs{display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem}
    .tab{padding:.45rem .7rem;border:1px solid #e5e7eb;border-radius:999px;font-weight:700;cursor:pointer}
    .tab[aria-selected="true"]{background:#111827;color:#fff;border-color:#111827}
    .inc-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;border:1px solid #e5e7eb;border-radius:12px;padding:.55rem .75rem}
    .pill-rej{background:#fee2e2;color:#991b1b}
    .pill-pend{background:#fef3c7;color:#92400e}
    .pill-dupe{background:#e0e7ff;color:#3730a3}
    .pill-warn{background:#fef3c7;color:#92400e}
  </style>

  <div class="py-6 max-w-7xl mx-auto px-4 space-y-6">

    {{-- KPIs --}}
    <div class="grid-kpis">
      <div class="kpi">
        <div class="label">Postulaciones activas</div>
        <div class="val">{{ number_format($totalPostulacionesActivas ?? 0) }}</div>
      </div>
      <div class="kpi">
        <div class="label">Usuarios registrados</div>
        <div class="val">{{ number_format(($funnel[0]['value'] ?? 0)) }}</div>
      </div>
      <div class="kpi">
        <div class="label">Pagos completados</div>
        <div class="val">{{ number_format(($funnel[3]['value'] ?? 0)) }}</div>
      </div>
      <div class="kpi">
        <div class="label">Con examen asignado</div>
        <div class="val">{{ number_format(($funnel[4]['value'] ?? 0)) }}</div>
      </div>
    </div>

    {{-- Embudo --}}
    <div class="card">
      <div class="card-h">Progreso por etapa</div>
      <div class="card-b space-y-3">
        @php
          $funnelCol = collect($funnel ?? []);
          $max = max(1, ...$funnelCol->pluck('value')->all());
        @endphp
        @forelse($funnelCol as $step)
          @php $pct = round((($step['value'] ?? 0) / $max) * 100, 2); @endphp
          <div>
            <div class="flex items-center justify-between mb-1">
              <div class="font-semibold">{{ $step['label'] ?? '—' }}</div>
              <div class="text-sm text-gray-500">{{ number_format($step['value'] ?? 0) }}</div>
            </div>
            <div class="bar"><span style="width: {{ $pct }}%"></span></div>
          </div>
        @empty
          <p class="text-sm text-gray-500">Sin datos para el embudo.</p>
        @endforelse
      </div>
    </div>

    {{-- Próximos eventos --}}
    <div class="card">
      <div class="card-h">Próximos eventos</div>
      <div class="card-b space-y-2">
        @if(empty($fechaColUsada))
          <p class="text-sm text-gray-500">
            No se encontró una columna de fecha en <code>grupos_examen</code>. Crea una de estas: <b>programado_en</b>, <b>fecha_hora</b>, <b>fecha_inicio</b>, <b>fecha</b> o <b>inicio</b>.
          </p>
        @else
          @forelse($proximos ?? [] as $e)
            @php
              $tipo = strtolower($e->tipo ?? 'actividad');
              $icon = match($tipo){ 'psicometrico'=>'🧠','conocimiento'=>'📝','entrevista'=>'👤', default=>'📅' };
              $pillClass = match($tipo){ 'psicometrico'=>'pill-psico','conocimiento'=>'pill-cono','entrevista'=>'pill-entre', default=>'pill-act'};
              $label = ucfirst($tipo);
            @endphp
            <div class="row">
              <div class="left">
                <div class="title">
                  <span class="pill {{ $pillClass }}">{{ $label }}</span>
                  <span class="ml-2">{{ $e->convocatoria }}</span>
                </div>
                <div class="sub">{{ $icon }} {{ $e->when->format('d/M/Y H:i') }} @if(!empty($e->lugar)) • {{ $e->lugar }} @endif</div>
              </div>
            </div>
          @empty
            <p class="text-sm text-gray-500">No hay eventos próximos.</p>
          @endforelse
        @endif
      </div>
    </div>

    {{-- Top incidencias --}}
    <div class="card" x-data="{t:'rechazos'}">
      <div class="card-h">Top incidencias</div>
      <div class="card-b">
        <div class="tabs">
          <button class="tab" :aria-selected="t==='rechazos'" @click="t='rechazos'">Pagos rechazados</button>
          <button class="tab" :aria-selected="t==='pendientes'" @click="t='pendientes'">Pagos pendientes</button>
          <button class="tab" :aria-selected="t==='duplicados'" @click="t='duplicados'">Duplicados</button>
          <button class="tab" :aria-selected="t==='inconsistencias'" @click="t='inconsistencias'">Inconsistencias</button>
        </div>

        {{-- Rechazados --}}
        <div x-show="t==='rechazos'" class="space-y-2">
          @forelse(($pagosRechazados ?? []) as $r)
            <div class="inc-row">
              <div class="left">
                <div class="title">
                  {{ $r->user->name }}
                  <span class="pill pill-rej">rechazado</span>
                </div>
                <div class="sub">{{ $r->user->email }} • {{ $r->convocatoria->titulo }}</div>
              </div>
              <a href="{{ route('admin.postulaciones.show',$r) }}" class="text-blue-600 font-semibold">Ver</a>
            </div>
          @empty
            <p class="text-sm text-gray-500">Sin pagos rechazados recientes.</p>
          @endforelse
        </div>

        {{-- Pendientes --}}
        <div x-show="t==='pendientes'" x-cloak class="space-y-2">
          @forelse(($pagosPendientes ?? []) as $p)
            <div class="inc-row">
              <div class="left">
                <div class="title">
                  {{ $p->user->name }}
                  <span class="pill pill-pend">pago pendiente</span>
                </div>
                <div class="sub">{{ $p->user->email }} • {{ $p->convocatoria->titulo }}</div>
              </div>
              <a href="{{ route('admin.postulaciones.show',$p) }}" class="text-blue-600 font-semibold">Ver</a>
            </div>
          @empty
            <p class="text-sm text-gray-500">Sin pagos pendientes recientes.</p>
          @endforelse
        </div>

        {{-- Duplicados --}}
        <div x-show="t==='duplicados'" x-cloak class="space-y-2">
          @forelse(($duplicados ?? []) as $d)
            <div class="inc-row">
              <div class="left">
                <div class="title">
                  {{ $d->user_name }}
                  <span class="pill pill-dupe">x{{ $d->total }}</span>
                </div>
                <div class="sub">{{ $d->user_email }} • {{ $d->convocatoria_titulo }}</div>
              </div>
              <a href="{{ route('admin.postulaciones.index') }}?user_id={{ $d->user_id }}&convocatoria_id={{ $d->convocatoria_id }}" class="text-blue-600 font-semibold">Filtrar</a>
            </div>
          @empty
            <p class="text-sm text-gray-500">No se detectaron duplicados.</p>
          @endforelse
        </div>

        {{-- Inconsistencias --}}
        <div x-show="t==='inconsistencias'" x-cloak class="space-y-4">
          <div class="space-y-2">
            <div class="font-semibold">Pagado sin fecha de pago</div>
            @forelse(($pagadoSinFecha ?? []) as $x)
              <div class="inc-row">
                <div class="left">
                  <div class="title">
                    {{ $x->user->name }}
                    <span class="pill pill-warn">sin fecha_pago</span>
                  </div>
                  <div class="sub">{{ $x->user->email }} • {{ $x->convocatoria->titulo }}</div>
                </div>
                <a href="{{ route('admin.postulaciones.show',$x) }}" class="text-blue-600 font-semibold">Revisar</a>
              </div>
            @empty
              <p class="text-sm text-gray-500">Todo bien en este chequeo.</p>
            @endforelse
          </div>

          <div class="space-y-2">
            <div class="font-semibold">Pagado sin referencia</div>
            @forelse(($pagadoSinReferencia ?? []) as $y)
              <div class="inc-row">
                <div class="left">
                  <div class="title">
                    {{ $y->user->name }}
                    <span class="pill pill-warn">sin referencia</span>
                  </div>
                  <div class="sub">{{ $y->user->email }} • {{ $y->convocatoria->titulo }}</div>
                </div>
                <a href="{{ route('admin.postulaciones.show',$y) }}" class="text-blue-600 font-semibold">Revisar</a>
              </div>
            @empty
              <p class="text-sm text-gray-500">Sin hallazgos aquí.</p>
            @endforelse
          </div>

          <div class="space-y-2">
            <div class="font-semibold">Usuarios sin perfil (con postulaciones)</div>
            @forelse(($sinPerfil ?? []) as $z)
              <div class="inc-row">
                <div class="left">
                  <div class="title">
                    {{ $z->user_name }}
                    <span class="pill pill-warn">sin perfil</span>
                  </div>
                  <div class="sub">{{ $z->user_email }} • {{ $z->postulaciones }} postulaciones</div>
                </div>
                <a href="{{ route('admin.users.index') }}?q={{ urlencode($z->user_email) }}" class="text-blue-600 font-semibold">Ver usuario</a>
              </div>
            @empty
              <p class="text-sm text-gray-500">Todos los usuarios con perfil creado.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>
<div class="card">
  <div class="card-h">Estadísticas demográficas</div>
  <div class="card-b grid md:grid-cols-3 gap-6">

    {{-- Género --}}
    <div>
      <div class="font-semibold mb-2">Género</div>
      @forelse($demGenero as $g)
        <div class="flex items-center justify-between border rounded-lg px-3 py-2 mb-2">
          <span>{{ $g->genero ? ucfirst($g->genero) : 'No especificado' }}</span>
          <span class="font-bold">{{ number_format($g->total) }}</span>
        </div>
      @empty
        <p class="text-sm text-gray-500">Sin datos.</p>
      @endforelse
    </div>

    {{-- Edad por rangos --}}
    <div>
      <div class="font-semibold mb-2">Edad (rangos)</div>
      <div class="flex items-center justify-between border rounded-lg px-3 py-2 mb-2">
        <span>15–17</span><span class="font-bold">{{ number_format($demEdad->e_15_17 ?? 0) }}</span>
      </div>
      <div class="flex items-center justify-between border rounded-lg px-3 py-2 mb-2">
        <span>18–20</span><span class="font-bold">{{ number_format($demEdad->e_18_20 ?? 0) }}</span>
      </div>
      <div class="flex items-center justify-between border rounded-lg px-3 py-2 mb-2">
        <span>21–23</span><span class="font-bold">{{ number_format($demEdad->e_21_23 ?? 0) }}</span>
      </div>
      <div class="flex items-center justify-between border rounded-lg px-3 py-2">
        <span>24+</span><span class="font-bold">{{ number_format($demEdad->e_24_mas ?? 0) }}</span>
      </div>
    </div>

    {{-- Top preparatorias --}}
    <div>
      <div class="font-semibold mb-2">Top preparatorias</div>
      @forelse($demPrepas as $p)
        <div class="flex items-center justify-between border rounded-lg px-3 py-2 mb-2">
          <span class="truncate">{{ $p->preparatoria }}</span>
          <span class="font-bold">{{ number_format($p->total) }}</span>
        </div>
      @empty
        <p class="text-sm text-gray-500">Sin datos.</p>
      @endforelse
    </div>

  </div>
</div>

  </div>

  {{-- Alpine para tabs (si tu layout ya lo trae, omite esto) --}}
  @once
    @push('scripts')
      <script src="https://unpkg.com/alpinejs" defer></script>
    @endpush
  @endonce
</x-app-layout>
