<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-900 leading-tight">Grupos de examen</h2>
    <p class="text-sm text-gray-500">Gestiona fechas, lugares y asignaciones por convocatoria.</p>
  </x-slot>

  <style>
    :root{
      --ink:#111827;
      --muted:#64748b;
      --line:#e5e7eb;
      --bg-soft:#f8fafc;
      --white:#fff;

      /* Paleta específica */
      --btn-detalle:#48CCA9;
      --btn-validar:#48AECC;
      --btn-exportar:#486BCC;
      --btn-editar:#CCA948;
      --btn-rechazar:#CC486B;
    }

    /* ===== Tabla ===== */
    .table{width:100%;border-collapse:separate;border-spacing:0;background:var(--white);
           border:1px solid var(--line);border-radius:14px;overflow:hidden}
    .table th{background:var(--bg-soft);color:#334155;font-weight:700;font-size:.88rem;
              padding:12px 14px;border-bottom:1px solid var(--line);text-align:left}
    .table td{padding:14px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
    .table tr:last-child td{border-bottom:none}

    /* ===== Toolbar ===== */
    .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin:12px 0}
    .input{border:1px solid var(--line);border-radius:10px;padding:.55rem .7rem;font-size:.95rem;min-width:220px;background:var(--white)}
    .muted{color:var(--muted)}

    /* ===== Botones ===== */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:.45rem;
      padding:.56rem .9rem;border-radius:12px;font-weight:700;border:1.5px solid transparent;
      font-size:.92rem;line-height:1;white-space:nowrap;min-width:110px;min-height:40px;
      color:#fff;text-decoration:none;transition:.15s;
    }
    .btn-detalle{background:var(--btn-detalle)}
    .btn-detalle:hover{background:#36b797}

    .btn-validar{background:var(--btn-validar)}
    .btn-validar:hover{background:#3aa0b7}

    .btn-exportar{background:var(--btn-exportar)}
    .btn-exportar:hover{background:#3a58a6}

    .btn-editar{background:var(--btn-editar);color:#111827}
    .btn-editar:hover{background:#b8933a;color:#fff}

    .btn-rechazar{background:var(--btn-rechazar)}
    .btn-rechazar:hover{background:#a63a57}

    /* ===== Acciones ===== */
    .actions-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, max-content));
      gap:10px;align-items:center;justify-content:start;
    }

    /* ===== Badges ===== */
    .badge{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .6rem;
           border-radius:999px;font-size:.78rem;font-weight:700;background:var(--bg-soft);color:#334155;border:1px solid #e2e8f0}
    .b-psico{background:#f1f5f9;color:#0f172a}
    .b-conoc{background:#f5f3ff;color:#3730a3}
    .b-entre{background:#fefce8;color:#92400e}
    .b-other{background:#f1f5f9;color:#334155}
  </style>

  <div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      @if(session('status'))
        <div class="mb-3 p-3 rounded" style="background:#ecfdf5;color:#065f46">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-3 p-3 rounded" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
      @endif

      {{-- ===== Toolbar superior ===== --}}
      <div class="toolbar">
        <form method="GET" class="flex items-center gap-2">
          <input class="input" type="number" name="convocatoria_id"
                 value="{{ request('convocatoria_id') }}"
                 placeholder="ID de convocatoria">
          <button class="btn btn-validar" type="submit">Filtrar</button>
          <a class="btn btn-validar" href="{{ route('admin.grupos.index') }}">Limpiar</a>
        </form>
        <a class="btn btn-validar" href="{{ route('admin.grupos.create') }}">+ Nuevo grupo</a>
      </div>

      {{-- ===== Tabla ===== --}}
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Convocatoria</th>
              <th>Tipo</th>
              <th>Fecha • Hora</th>
              <th>Lugar</th>
              <th>Capacidad</th>
              <th>Ocupados</th>
              <th>Cupo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          @forelse($q as $g)
            @php
              $badgeClass = match($g->tipo){
                'psicometrico' => 'b-psico',
                'conocimiento' => 'b-conoc',
                'entrevista'   => 'b-entre',
                default        => 'b-other'
              };
            @endphp
            <tr>
              <td>#{{ $g->id }}</td>
              <td>{{ $g->convocatoria->titulo ?? '—' }} <span class="muted">(ID {{ $g->convocatoria_id }})</span></td>
              <td><span class="badge {{ $badgeClass }}">
                {{ ucfirst($g->tipo) }}@if(!empty($g->tipo_detalle)) — {{ $g->tipo_detalle }}@endif
              </span></td>
              <td>{{ optional($g->fecha_hora)->timezone('America/Tijuana')->format('Y-m-d • H:i') }}</td>
              <td>{{ $g->lugar ?? '—' }}</td>
              <td>{{ $g->capacidad }}</td>
              <td>{{ $g->ocupados }}</td>
              <td>{{ $g->cupo_disponible }}</td>
              <td>
                <div class="actions-grid">
                  <a class="btn btn-detalle"  href="{{ route('admin.grupos.asignados',$g) }}">Ver lista</a>
                  <a class="btn btn-validar"  href="{{ route('admin.grupos.asignar.form',$g) }}">Validar</a>
                  <a class="btn btn-exportar" href="{{ route('admin.grupos.export_asistencia',$g) }}">Exportar</a>
                  <a class="btn btn-editar"   href="{{ route('admin.grupos.edit',$g) }}">Editar</a>
                  <form method="POST" action="{{ route('admin.grupos.destroy',$g) }}"
                        onsubmit="return confirm('¿Eliminar grupo #{{ $g->id }}? Esta acción no se puede deshacer.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-rechazar" type="submit">Rechazar</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-gray-500 p-6">No hay grupos.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">{{ $q->links() }}</div>
    </div>
  </div>
</x-app-layout>
