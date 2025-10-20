<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Asignados — Grupo #{{ $grupo->id }}
    </h2>
    <div class="text-sm text-gray-500">
      {{ ucfirst($grupo->tipo) }}
      @if($grupo->tipo_detalle) — {{ $grupo->tipo_detalle }} @endif
      • {{ optional($grupo->fecha_hora)->timezone('America/Tijuana')->format('Y-m-d H:i') }}
      • {{ $grupo->lugar ?? 'Por definir' }}
      • Convocatoria: {{ $grupo->convocatoria->titulo ?? '—' }}
    </div>
  </x-slot>

  <style>
    .btn{display:inline-block;padding:.5rem .85rem;border-radius:.55rem;font-weight:600;text-decoration:none}
    .btn-ghost{background:#fff;border:2px solid #e5e7eb}
    .btn-primary{background:#2563eb;color:#fff}
    .btn-danger{background:#fee2e2;border:2px solid #fecaca;color:#991b1b}
    .table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
    .table th,.table td{padding:12px 14px;border-bottom:1px solid #e5e7eb;text-align:left}
    .toolbar{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0;align-items:center;justify-content:space-between}
    .muted{color:#64748b}
    .msg{width:100%;border:1px solid #e5e7eb;border-radius:10px;padding:10px}
  </style>

  <div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
      @if(session('status'))
        <div class="mb-3 p-3 rounded" style="background:#ecfdf5;color:#065f46">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-3 p-3 rounded" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
      @endif

      <div class="toolbar">
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn btn-ghost" href="{{ route('admin.grupos.index', ['convocatoria_id'=>$grupo->convocatoria_id]) }}">← Volver a grupos</a>
          <a class="btn btn-ghost" href="{{ route('admin.grupos.export_asistencia',$grupo) }}">Exportar asistencia</a>
        </div>

        {{-- Botón de desasignar masivo (form independiente) --}}
        <form id="bulk-unassign-form" method="POST" action="{{ route('admin.grupos.desasignar_masivo', $grupo) }}">
          @csrf
          <button class="btn btn-danger" type="submit">Desasignar seleccionados</button>
        </form>
      </div>

      {{-- =========================
           FORM NOTIFICAR (POST)
         ========================= --}}
      <form id="notify-form"
            method="POST"
            action="{{ route('admin.grupos.notificar', $grupo) }}"
            onsubmit="this.querySelectorAll('input[name=_method]').forEach(e => e.remove());">
        @csrf

        {{-- Mensaje opcional para correo --}}
        <div class="mb-2">
          <label class="block text-sm text-gray-600 mb-1">Mensaje opcional para el correo</label>
          <textarea name="mensaje" rows="3" class="msg" placeholder="Escribe un mensaje adicional para los seleccionados (opcional)…"></textarea>
          <div class="text-xs muted mt-1">
            Si no seleccionas a nadie, se notificará a <strong>todos</strong> los asignados del grupo.
          </div>
        </div>

        <div style="display:flex;gap:12px;align-items:center;margin:12px 0">
          <label style="display:flex;gap:8px;align-items:center">
            <input type="checkbox" id="check-all">
            <span class="muted">Seleccionar todos</span>
          </label>
          <span class="muted" id="sel-count">0 seleccionados</span>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr class="text-gray-600">
                <th></th>
                <th>ID Postulación</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th class="muted">Acción individual</th>
              </tr>
            </thead>
            <tbody>
              @forelse($postulaciones as $p)
                @php $perfil = optional($p->user->perfilPostulante); @endphp
                <tr>
                  <td>
                    <input class="row-chk" type="checkbox" name="postulacion_ids[]" value="{{ $p->id }}">
                  </td>
                  <td>#{{ $p->id }}</td>
                  <td>{{ $perfil->nombre_completo ?? $p->user->name }}</td>
                  <td>{{ $perfil->correo_contacto ?? $p->user->email ?? '—' }}</td>
                  <td>{{ $perfil->telefono ?? '—' }}</td>
                  <td>
                    {{-- Botón que dispara un FORM OCULTO (DELETE) fuera del notify-form --}}
                    <button class="btn btn-ghost"
                            type="submit"
                            form="desasignar-{{ $p->id }}"
                            onclick="return confirm('¿Desasignar #{{ $p->id }} del grupo #{{ $grupo->id }}?')">
                      Desasignar
                    </button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="muted">No hay personas asignadas a este grupo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div style="margin-top:12px">
          <button class="btn btn-primary" type="submit" formmethod="post">Enviar correo a seleccionados</button>
        </div>
      </form>

      {{-- =========================
           FORMS OCULTOS (DELETE individual)
         ========================= --}}
      @foreach($postulaciones as $p)
        <form id="desasignar-{{ $p->id }}" method="POST"
              action="{{ route('admin.grupos.desasignar', [$grupo, $p]) }}"
              style="display:none">
          @csrf
          @method('DELETE')
        </form>
      @endforeach
    </div>
  </div>

  <script>
    // Check all / counter (trabajan sobre el notify-form)
    const all = document.getElementById('check-all');
    const checks = Array.from(document.querySelectorAll('#notify-form .row-chk'));
    const counter = document.getElementById('sel-count');
    function refresh(){
      const n = checks.filter(c => c.checked).length;
      counter.textContent = n + ' seleccionados';
    }
    if (all){
      all.addEventListener('change', () => {
        checks.forEach(c => { if (!c.disabled) c.checked = all.checked; });
        refresh();
      });
    }
    checks.forEach(c => c.addEventListener('change', refresh));
    refresh();

    // Desasignación masiva: clona los seleccionados al form bulk-unassign-form
    document.getElementById('bulk-unassign-form')?.addEventListener('submit', (e) => {
      const bulk = e.target;
      bulk.querySelectorAll('input[name="postulacion_ids[]"]').forEach(n => n.remove());
      checks.filter(c => c.checked).forEach(c => {
        const h = document.createElement('input');
        h.type = 'hidden';
        h.name = 'postulacion_ids[]';
        h.value = c.value;
        bulk.appendChild(h);
      });
    });

    // Cinturón y tirantes: limpiar cualquier _method en notify-form
    document.getElementById('notify-form')?.addEventListener('submit', (e) => {
      e.target.querySelectorAll('input[name="_method"]').forEach(el => el.remove());
    });
  </script>
</x-app-layout>
