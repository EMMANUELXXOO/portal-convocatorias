<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-semibold">Validación masiva de pagos</h1></x-slot>
  <div class="max-w-6xl mx-auto p-6 bg-white border rounded">
    @if(session('ok')) <div class="mb-3 p-2 bg-green-50 text-green-800 rounded">{{ session('ok') }}</div>@endif
    @if(session('error')) <div class="mb-3 p-2 bg-red-50 text-red-800 rounded">{{ session('error') }}</div>@endif

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
      <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o email" class="border rounded p-2">
      <input type="number" name="convocatoria_id" value="{{ request('convocatoria_id') }}" placeholder="Convocatoria ID" class="border rounded p-2 w-40">
      <button class="px-3 py-2 bg-blue-600 text-white rounded">Filtrar</button>
    </form>

    <form method="POST" action="{{ route('admin.pagos.validar.aprobar') }}">
      @csrf
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50">
              <th class="p-2"><input type="checkbox" onclick="document.querySelectorAll('.chk').forEach(c=>c.checked=this.checked)"></th>
              <th class="p-2 text-left">Aspirante</th>
              <th class="p-2 text-left">Email</th>
              <th class="p-2 text-left">Convocatoria</th>
              <th class="p-2">Estatus</th>
              <th class="p-2">Creado</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $it)
              <tr class="border-t">
                <td class="p-2"><input type="checkbox" class="chk" name="ids[]" value="{{ $it->id }}"></td>
                <td class="p-2">{{ $it->user->name }}</td>
                <td class="p-2">{{ $it->user->email }}</td>
                <td class="p-2">{{ $it->convocatoria->titulo }}</td>
                <td class="p-2">
                  <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">{{ $it->estatus }}</span>
                </td>
                <td class="p-2">{{ $it->created_at->format('Y-m-d') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between mt-4">
        {{ $items->links() }}
        <button class="px-4 py-2 bg-green-600 text-white rounded" onclick="return confirm('¿Aprobar pagos seleccionados?')">Aprobar seleccionados</button>
      </div>
    </form>
  </div>
</x-app-layout>
