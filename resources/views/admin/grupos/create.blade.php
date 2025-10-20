{{-- resources/views/admin/grupos/create.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo grupo</h2>
  </x-slot>

  <style>
    /* ===== Botones paleta ===== */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;
      padding:.55rem 1rem;border-radius:12px;font-weight:700;font-size:.9rem;
      border:1.5px solid transparent;text-decoration:none;transition:.15s;
      min-height:40px;min-width:120px;
    }
    .btn-primary{background:#48CCA9;color:#fff}.btn-primary:hover{background:#3ab997}
    .btn-ghost{background:#fff;border:1.5px solid #e5e7eb;color:#374151}
    .btn-ghost:hover{background:#f9fafb}

    /* ===== Inputs ===== */
    .label{font-weight:600;margin-bottom:4px;color:#374151}
    .input,select,textarea{
      width:100%;border:1.5px solid #d1d5db;border-radius:10px;
      padding:.55rem .75rem;font-size:.9rem;color:#111827;
      transition:border .15s, box-shadow .15s;
    }
    .input:focus,select:focus,textarea:focus{
      outline:none;border-color:#48AECC;
      box-shadow:0 0 0 3px rgba(72,174,204,.25);
    }
    .row{margin:12px 0}
    .actions{display:flex;gap:12px;margin-top:16px;justify-content:flex-end}
    .muted{color:#6b7280;font-size:.9rem}
  </style>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex justify-center">
      <div class="w-full max-w-2xl bg-white rounded-lg shadow p-6">

        @if($errors->any())
          <div class="mb-4 p-3 rounded" style="background:#fee2e2;color:#991b1b;">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('admin.grupos.store') }}">
          @csrf

          {{-- Convocatoria --}}
          <div class="row">
            <div class="label">Convocatoria</div>
            <select name="convocatoria_id" class="input" required>
              @foreach($convocatorias as $c)
                <option value="{{ $c->id }}" @selected(old('convocatoria_id')==$c->id)>
                  {{ $c->id }} — {{ $c->titulo }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Tipo --}}
          <div class="row">
            <div class="label">Tipo</div>
            <select id="tipo" name="tipo" class="input" required>
              @php
                $opts = [
                  'psicometrico'     => 'Psicométrico',
                  'conocimiento'     => 'Conocimiento',
                  'entrevista'       => 'Entrevista',
                  'diplomado'        => 'Diplomado',
                  'primeros_auxilios'=> 'Curso de primeros auxilios',
                  'aha'              => 'Curso AHA',
                  'capacitacion'     => 'Curso de capacitación',
                  'otro'             => 'Otro (especificar)',
                ];
              @endphp
              @foreach($opts as $val=>$label)
                <option value="{{ $val }}" @selected(old('tipo')===$val)>{{ $label }}</option>
              @endforeach
            </select>
            <div id="tipo-detalle-wrap" class="mt-2" style="display:none">
              <input class="input" type="text" name="tipo_detalle" value="{{ old('tipo_detalle') }}"
                     placeholder="Especifica el tipo (p. ej. Taller X)" maxlength="120">
              <div class="muted">Requerido cuando el tipo es “Otro”.</div>
            </div>
          </div>

          {{-- Fecha-hora --}}
          <div class="row">
            <div class="label">Fecha–Hora</div>
            <input class="input" type="datetime-local" name="fecha_hora"
                   value="{{ old('fecha_hora') }}" required>
          </div>

          {{-- Lugar --}}
          <div class="row">
            <div class="label">Lugar</div>
            <input class="input" type="text" name="lugar" value="{{ old('lugar') }}" placeholder="Aula 101, Zoom, etc.">
          </div>

          {{-- Capacidad --}}
          <div class="row">
            <div class="label">Capacidad</div>
            <input class="input" type="number" name="capacidad" min="1" max="5000"
                   value="{{ old('capacidad', 20) }}" required>
          </div>

          {{-- Botones --}}
          <div class="actions">
            <a class="btn btn-ghost" href="{{ route('admin.grupos.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Crear</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Mostrar/ocultar "tipo_detalle" cuando es "otro"
    const tipoSel = document.getElementById('tipo');
    const wrap    = document.getElementById('tipo-detalle-wrap');
    function toggleDetalle(){
      wrap.style.display = (tipoSel.value === 'otro') ? 'block' : 'none';
    }
    tipoSel.addEventListener('change', toggleDetalle);
    toggleDetalle();
  </script>
</x-app-layout>
