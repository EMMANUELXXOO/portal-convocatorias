{{-- resources/views/postulaciones/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Mis postulaciones
    </h2>
  </x-slot>

  <style>
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05);margin-bottom:16px}
    .card-h{padding:14px 16px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center}
    .card-b{padding:14px 16px}
    .badge{display:inline-block;padding:.25rem .6rem;border-radius:999px;font-weight:700;font-size:.75rem}
    .b-pend{background:#fef3c7;color:#9a3412}
    .b-payp{background:#fde68a;color:#92400e}
    .b-paid{background:#dcfce7;color:#166534}
    .b-ok{background:#d1fae5;color:#065f46}
    .b-no{background:#fee2e2;color:#991b1b}
    .grid{display:grid;gap:16px}
    @media(min-width:1024px){.grid-3{grid-template-columns:1fr 1fr 1fr}}
    .row{display:flex;gap:12px;margin:.25rem 0}
    .k{color:#6b7280;min-width:8rem}
    .mono{font-family:monospace}
    .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .85rem;border-radius:.6rem;font-weight:600;border:1px solid #e5e7eb;background:#fff}
    .btn-primary{background:#111827;border-color:#111827;color:#fff}
    .btn-ghost{background:#fff}
    .btn-danger{background:#dc2626;color:#fff;border-color:#dc2626}
    .empty{background:#f9fafb;border:1px dashed #e5e7eb;border-radius:16px;padding:24px;text-align:center;color:#6b7280}
    .chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
    .chip{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .6rem;border-radius:999px;background:#f1f5f9;color:#334155;font-size:.8rem}
    .hr{border:0;border-top:1px solid #e5e7eb;margin:10px 0}
  </style>

  <div class="py-6 max-w-6xl mx-auto sm:px-6 lg:px-8">

    @if (session('status'))
      <div class="mb-4 p-3 rounded" style="background:#ecfdf5;color:#065f46;">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 p-3 rounded" style="background:#fee2e2;color:#991b1b;">
        <ul class="list-disc ml-5">
          @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if ($items->isEmpty())
      <div class="empty">
        <div class="text-lg font-semibold mb-1">Aún no tienes postulaciones</div>
        <div class="mb-3">Cuando te registres a una convocatoria, aparecerá aquí.</div>
        <a href="{{ route('convocatorias.index') }}" class="btn btn-primary">Ver convocatorias</a>
      </div>
    @else
      <div class="grid grid-3">
        @foreach ($items as $p)
          @php
            $EST_PAGADO   = \App\Models\Postulacion::ESTATUS_PAGADO   ?? 'pagado';
            $EST_VALIDADA = \App\Models\Postulacion::ESTATUS_VALIDADA ?? 'validada';

            $badge = match($p->estatus){
              'validada' => 'b-ok',
              'rechazada'=> 'b-no',
              'pagado'   => 'b-paid',
              'pago_pendiente' => 'b-payp',
              default    => 'b-pend',
            };

            $puedeEliminar = !in_array($p->estatus, [$EST_PAGADO, $EST_VALIDADA], true);
            $puedeEditar   = ($p->user_edit_count ?? 0) < 1
                             && !in_array($p->estatus, [$EST_PAGADO, $EST_VALIDADA], true);
          @endphp

          <div class="card">
            <div class="card-h">
              <div>
                <div class="text-sm text-gray-500">Convocatoria</div>
                <div class="font-semibold">{{ $p->convocatoria->titulo ?? '—' }}</div>
              </div>
              <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_',' ', (string)$p->estatus)) }}</span>
            </div>

            <div class="card-b">
              <div class="row"><div class="k">Folio</div><div class="mono">{{ $p->folio ?? '—' }}</div></div>
              <div class="row"><div class="k">Creada</div><div>{{ $p->created_at?->format('Y-m-d H:i') ?? '—' }}</div></div>
              <div class="row"><div class="k">Pago</div><div>{{ $p->fecha_pago?->format('Y-m-d H:i') ?? '—' }}</div></div>

              {{-- Sesiones asignadas: SOLO si existen --}}
              @if($p->gruposExamen && $p->gruposExamen->isNotEmpty())
                <div class="hr"></div>
                <div class="text-sm text-gray-600">Fechas asignadas</div>
                <div class="chips">
                  @foreach($p->gruposExamen->sortBy('fecha_hora') as $g)
                    <span class="chip" title="Grupo #{{ $g->id }}">
                      {{ ucfirst($g->tipo) }}@if($g->tipo==='otro' && $g->tipo_detalle) — {{ $g->tipo_detalle }}@endif
                      • {{ $g->fecha_hora?->timezone('America/Tijuana')->format('d/M H:i') }}
                      • {{ $g->lugar ?? 'Por definir' }}
                    </span>
                  @endforeach
                </div>
              @endif

              <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
                @if($p->estatus !== $EST_PAGADO)
                  <a class="btn btn-primary" href="{{ route('postulaciones.pago', $p) }}">Ir al pago</a>
                @else
                  <a class="btn btn-ghost" href="{{ route('postulaciones.recibo', $p) }}" target="_blank" rel="noopener">
                    Ver recibo
                  </a>
                  <form method="POST" action="{{ route('postulaciones.reenviar-recibo', $p) }}" class="inline">
                    @csrf
                    <button class="btn" type="submit">Reenviar correo</button>
                  </form>
                @endif

                @if($puedeEditar)
                  <a class="btn btn-ghost" href="{{ route('postulaciones.edit', $p) }}">
                    Editar (una sola vez)
                  </a>
                @else
                  <span class="text-sm text-gray-500" title="Edición no disponible">
                    Edición no disponible
                  </span>
                @endif

                @if($puedeEliminar)
                  <form method="POST" action="{{ route('postulaciones.destroy', $p) }}"
                        onsubmit="return confirm('¿Seguro que deseas eliminar esta postulación? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Eliminar</button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="mt-4">
        {{ $items->links() }}
      </div>
    @endif
  </div>
</x-app-layout>
