<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-900 leading-tight">
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
    /* ===== Botones unificados (paleta) ===== */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:.45rem;
      padding:.58rem .95rem;border-radius:12px;font-weight:700;border:1.5px solid transparent;
      min-height:40px;min-width:120px;white-space:nowrap;font-size:.9rem;text-align:center;
      text-decoration:none;transition:.15s;
    }
    .btn-neutral{background:#fff;border-color:#e5e7eb;color:#374151}
    .btn-neutral:hover{background:#f9fafb}
    .btn-validar{background:#48CCA9;color:#fff}.btn-validar:hover{background:#3ab997}
    .btn-aux{background:#48AECC;color:#fff}.btn-aux:hover{background:#3a97b4}
    .btn-export{background:#486BCC;color:#fff}.btn-export:hover{background:#3b59a8}
    .btn-rechazar{background:#CC486B;color:#fff}.btn-rechazar:hover{background:#a63a57}
    .btn-pagado{background:#CCA948;color:#fff}.btn-pagado:hover{background:#a68536}

    /* ===== Tabla ===== */
    .table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;
           border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
    .table thead th{background:#f8fafc;color:#475569;font-weight:700;font-size:.85rem;
                    padding:12px 14px;border-bottom:1px solid #e5e7eb;text-align:left}
    .table td{padding:12px 14px;border-bottom:1px solid #f1f5f9}
    .table tr:last-child td{border-bottom:none}

    /* ===== Layout ===== */
    .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:12px 0}
    .toolbar--space{justify-content:space-between}
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

      {{-- Toolbar superior --}}
      <div class="toolbar">
        <a class="btn btn-neutral"
           href="{{ route('admin.grupos.index', ['convocatoria_id'=>$grupo->convocatoria_id]) }}">
          ← Volver a grupos
        </a>
        <a class="btn btn-export" href="{{ route('admin.grupos.export_asistencia',$grupo) }}">
          Exportar asistencia
        </a>
      </div>

      {{-- Form principal --}}
      <form id="bulk-form"
            method="POST"
            action="{{ route('admin.grupos.desasignar_masivo', $grupo) }}"
            onsubmit="this.querySelectorAll('input[name=_method]').forEach(e => e.remove());">
        @csrf

        {{-- Mensaje opcional para el correo --}}
        <div class="mb-2">
          <label class="block text-sm text-gray-600 mb-1">Mensaje opcional para el correo</label>
          <textarea name="mensaje" rows="3" class="msg" placeholder="Escribe un mensaje adicional (opcional)…"></textarea>
          <div class="text-xs muted mt-1">
            Si no seleccionas a nadie y presionas “Enviar correo…”, se notificará a <strong>todos</strong> los asignados del grupo.
          </div>
        </div>

        {{-- Toolbar de acciones masivas --}}
        <div class="toolbar toolbar--space">
          <div style="display:flex;gap:12px;align-items:center">
            <label style="display:flex;gap:8px;align-items:center">
              <input type="checkbox" id="check-all">
              <span class="muted">Seleccionar todos</span>
            </label>
            <span class="muted" id="sel-count">0 seleccionados</span>
          </div>

          <div style="display:flex;gap:10px;flex-wrap:wrap">
            {{-- Desasignar seleccionados (acción por defecto) --}}
            <button class="btn btn-rechazar" type="submit">
              Desasignar seleccionados
            </button>

            {{-- Enviar correo a seleccionados (forzado a POST) --}}
            <button class="btn btn-aux"
                    type="submit"
                    formaction="{{ route('admin.grupos.notificar', $grupo) }}"
                    formmethod="post">
              Enviar correo a seleccionados
            </button>
          </div>
        </div>

        {{-- Tabla --}}
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th style="width:40px"></th>
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
                    <form method="POST"
                          action="{{ route('admin.grupos.desasignar', [$grupo, $p]) }}"
                          onsubmit="return confirm('¿Desasignar #{{ $p->id }} del grupo #{{ $grupo->id }}?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-rechazar" type="submit">Desasignar</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="muted">No hay personas asignadas a este grupo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </form>
    </div>
  </div>

  <script>
    const all = document.getElementById('check-all');
    const checks = Array.from(document.querySelectorAll('.row-chk'));
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
  </script>
</x-app-layout>
