<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-semibold">Ranking de desempeño</h1></x-slot>
  <div class="max-w-6xl mx-auto p-6 bg-white border rounded">
    <form method="GET" class="flex gap-2 mb-4">
      <input type="number" name="convocatoria_id" value="{{ $convocatoriaId }}" placeholder="Convocatoria ID" class="border rounded p-2 w-48">
      <button class="px-3 py-2 bg-blue-600 text-white rounded">Filtrar</button>
    </form>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50">
            <th class="p-2 text-left">Aspirante</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-left">Convocatoria</th>
            <th class="p-2">Tipo</th>
            <th class="p-2">Puntaje</th>
            <th class="p-2">Fecha</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $it)
            <tr class="border-t">
              <td class="p-2">{{ $it->name }}</td>
              <td class="p-2">{{ $it->email }}</td>
              <td class="p-2">{{ $it->convocatoria }}</td>
              <td class="p-2">{{ ucfirst($it->tipo) }}</td>
              <td class="p-2 font-semibold">{{ $it->puntaje_total }}</td>
              <td class="p-2">{{ \Illuminate\Support\Carbon::parse($it->created_at)->format('Y-m-d') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">{{ $items->links() }}</div>
  </div>
</x-app-layout>
