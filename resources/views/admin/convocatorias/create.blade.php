{{-- resources/views/admin/convocatorias/create.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <div class="wrap" style="display:flex;align-items:center;gap:12px">
      <h2 class="title" style="margin:0;flex:1">Nueva convocatoria</h2>

      <a href="{{ route('admin.convocatorias.index') }}"
         class="btn btn-ghost"
         style="border:1px solid #e5e7eb;padding:10px 16px;border-radius:8px;font-weight:700;text-decoration:none">
        Volver al listado
      </a>
    </div>
  </x-slot>

  <div class="wrap" style="margin-top:12px">
    {{-- Flash de estado --}}
    @if (session('status'))
      <div class="flash" style="background:#ecfeff;border:1px solid #a5f3fc;color:#155e75;padding:10px 12px;border-radius:8px;margin-bottom:12px">
        {{ session('status') }}
      </div>
    @endif

    {{-- Resumen de errores --}}
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

  {{-- Form principal de creación --}}
  <form method="POST"
        action="{{ route('admin.convocatorias.store') }}"
        enctype="multipart/form-data">
    @csrf
    @php($submitLabel = 'Crear')
    @include('admin.convocatorias._form', [
      'convocatoria' => $convocatoria,
      'submitLabel'  => $submitLabel
    ])
  </form>
</x-app-layout>
