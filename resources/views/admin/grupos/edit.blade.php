<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">Editar grupo #{{ $grupo->id }}</h2>
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
    label{display:block;font-weight:600;margin-bottom:4px;color:#374151}
    .input,select,textarea{
      width:100%;border:1.5px solid #d1d5db;border-radius:10px;
      padding:.55rem .75rem;font-size:.9rem;color:#111827;
      transition:border .15s, box-shadow .15s;
    }
    .input:focus,select:focus,textarea:focus{
      outline:none;border-color:#48AECC;
      box-shadow:0 0 0 3px rgba(72,174,204,.25);
    }
    .space-y-4 > * + *{margin-top:1rem}
  </style>

  <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
    <form method="POST" action="{{ route('admin.grupos.update',$grupo) }}" class="space-y-4">
      @csrf @method('PUT')

      <div>
        <label>Convocatoria</label>
        <select name="convocatoria_id" class="input" required>
          @foreach($convocatorias as $c)
            <option value="{{ $c->id }}" @selected($grupo->convocatoria_id===$c->id)>
              {{ $c->id }} — {{ $c->titulo }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Tipo</label>
        <select name="tipo" class="input" required>
          <option value="psicometrico" @selected($grupo->tipo==='psicometrico')>Psicométrico</option>
          <option value="conocimiento" @selected($grupo->tipo==='conocimiento')>Conocimiento</option>
          <option value="entrevista" @selected($grupo->tipo==='entrevista')>Entrevista</option>
          <option value="diplomado" @selected($grupo->tipo==='diplomado')>Diplomado</option>
          <option value="primeros_auxilios" @selected($grupo->tipo==='primeros_auxilios')>1ros auxilios</option>
          <option value="aha" @selected($grupo->tipo==='aha')>Curso AHA</option>
          <option value="capacitacion" @selected($grupo->tipo==='capacitacion')>Capacitación</option>
          <option value="otro" @selected($grupo->tipo==='otro')>Otro</option>
        </select>
      </div>

      <div>
        <label>Fecha y hora</label>
        <input type="datetime-local" name="fecha_hora"
               value="{{ optional($grupo->fecha_hora)->timezone('America/Tijuana')->format('Y-m-d\TH:i') }}"
               class="input" required>
      </div>

      <div>
        <label>Lugar</label>
        <input type="text" name="lugar" value="{{ $grupo->lugar }}" class="input">
      </div>

      <div>
        <label>Capacidad</label>
        <input type="number" name="capacidad" min="1" max="5000"
               value="{{ $grupo->capacidad }}" class="input" required>
      </div>

      <div class="flex gap-3">
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn btn-ghost" href="{{ route('admin.grupos.index') }}">← Volver</a>
      </div>
    </form>
  </div>
</x-app-layout>
