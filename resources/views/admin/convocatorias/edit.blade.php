{{-- resources/views/admin/convocatorias/edit.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <div class="wrap" style="display:flex;align-items:center;gap:12px">
      <h2 class="title" style="margin:0;flex:1">Editar convocatoria</h2>

      {{-- Ver pública (opcional) --}}
      @if (Route::has('convocatorias.show'))
        <a href="{{ route('convocatorias.show', $convocatoria) }}"
           target="_blank"
           class="btn btn-ghost"
           style="text-decoration:none;border:1px solid #e5e7eb;border-radius:8px;padding:10px 16px;font-weight:700">
          Ver pública
        </a>
      @endif

      {{-- Form de eliminación SEPARADO (no anidado en el form de edición) --}}
      <form method="POST"
            action="{{ route('admin.convocatorias.destroy', $convocatoria) }}"
            onsubmit="return confirm('¿Eliminar esta convocatoria?');"
            style="margin:0">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="btn btn-danger"
                style="background:#dc2626;color:#fff;border:none;border-radius:8px;padding:10px 16px;font-weight:700;cursor:pointer">
          Eliminar
        </button>
      </form>
    </div>
  </x-slot>

  <div class="wrap" style="margin-top:12px">
    {{-- Flash de estado --}}
    @if (session('status'))
      <div class="flash" style="background:#ecfeff;border:1px solid #a5f3fc;color:#155e75;padding:10px 12px;border-radius:8px;margin-bottom:12px">
        {{ session('status') }}
      </div>
    @endif

    {{-- Resumen de errores (útil cuando la validación bloquea la subida) --}}
    @if ($errors->any())
      <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:8px;margin-bottom:12px">
        <div style="font-weight:700;margin-bottom:6px">Corrige los siguientes campos:</div>
        <ul style="margin:0 0 0 18px;padding:0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>

  {{-- Form principal de edición --}}
  <form method="POST"
        action="{{ route('admin.convocatorias.update', $convocatoria) }}"
        enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @php($submitLabel = 'Actualizar')
    @include('admin.convocatorias._form', [
      'convocatoria' => $convocatoria,
      'submitLabel'  => $submitLabel
    ])
  </form>
</x-app-layout>
