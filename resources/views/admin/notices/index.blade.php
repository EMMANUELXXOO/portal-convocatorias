{{-- resources/views/admin/notices/index.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-2xl">Centro de notificaciones</h1>
  </x-slot>

  <div class="max-w-5xl mx-auto px-4 py-6 space-y-6">

    @if (session('status'))
      <div class="p-3 rounded bg-green-50 text-green-700">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="p-3 rounded bg-red-50 text-red-700">{{ $errors->first() }}</div>
    @endif

    {{-- Crear --}}
    <div class="bg-white border rounded-xl shadow-sm p-4">
      <h2 class="font-bold mb-3">Nueva notificación</h2>
      <form method="POST" action="{{ route('admin.notices.store') }}" class="grid gap-3">
        @csrf
        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-gray-600">Título *</label>
            <input name="titulo" class="mt-1 w-full border rounded" required>
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label class="text-sm text-gray-600">Nivel</label>
              <select name="nivel" class="mt-1 w-full border rounded">
                <option value="info">Info</option>
                <option value="success">Éxito</option>
                <option value="warning">Aviso</option>
                <option value="danger">Peligro</option>
              </select>
            </div>
            <div>
              <label class="text-sm text-gray-600">Audiencia</label>
              <select name="audiencia" class="mt-1 w-full border rounded">
                <option value="todos">Todos</option>
                <option value="aspirantes">Aspirantes</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="flex items-end">
              <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="activo" value="1" checked class="rounded">
                Activo
              </label>
            </div>
          </div>
        </div>

        <div>
          <label class="text-sm text-gray-600">Mensaje *</label>
          <textarea name="mensaje" rows="3" class="mt-1 w-full border rounded" required></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <label class="text-sm text-gray-600">Visible desde</label>
            <input type="datetime-local" name="visible_desde" class="mt-1 w-full border rounded">
          </div>
          <div>
            <label class="text-sm text-gray-600">Visible hasta</label>
            <input type="datetime-local" name="visible_hasta" class="mt-1 w-full border rounded">
          </div>
        </div>

        <div class="flex justify-end">
          <button class="px-4 py-2 rounded bg-blue-600 text-white">Publicar</button>
        </div>
      </form>
    </div>

    {{-- Listado --}}
    <div class="bg-white border rounded-xl shadow-sm">
      <div class="p-4 font-bold border-b">Notificaciones publicadas</div>
      <div class="divide-y">
        @forelse($items as $n)
          <div class="p-4 flex items-start gap-3">
            <div class="shrink-0">
              @php
                $colors = [
                  'info'    => 'bg-blue-100 text-blue-700',
                  'success' => 'bg-green-100 text-green-700',
                  'warning' => 'bg-yellow-100 text-yellow-700',
                  'danger'  => 'bg-red-100 text-red-700',
                ];
              @endphp
              <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $colors[$n->nivel] ?? '' }}">
                {{ strtoupper($n->nivel) }}
              </span>
            </div>
            <div class="grow">
              <div class="font-bold">{{ $n->titulo }}</div>
              <div class="text-gray-700 whitespace-pre-line">{{ $n->mensaje }}</div>
              <div class="text-sm text-gray-500 mt-1">
                Audiencia: {{ $n->audiencia }} —
                Vigencia: {{ optional($n->visible_desde)->format('d/m/Y H:i') ?? '—' }} a
                {{ optional($n->visible_hasta)->format('d/m/Y H:i') ?? '—' }}
              </div>
            </div>
            <div class="flex flex-col items-end gap-2">
              <form method="POST" action="{{ route('admin.notices.toggle', $n) }}">
                @csrf
                <button class="px-3 py-1 rounded {{ $n->activo ? 'bg-gray-200' : 'bg-emerald-600 text-white' }}">
                  {{ $n->activo ? 'Desactivar' : 'Activar' }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.notices.destroy', $n) }}" onsubmit="return confirm('¿Eliminar?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1 rounded bg-red-600 text-white">Eliminar</button>
              </form>
            </div>
          </div>
        @empty
          <div class="p-6 text-gray-500">No hay notificaciones.</div>
        @endforelse
      </div>
      <div class="p-4">{{ $items->links() }}</div>
    </div>

  </div>
</x-app-layout>
