<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-semibold">Notificaciones</h1></x-slot>
  <div class="max-w-5xl mx-auto p-6">
    <a href="{{ route('admin.notificaciones.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Nueva</a>
    <div class="mt-4 bg-white border rounded">
      <table class="w-full text-sm">
        <thead><tr class="bg-gray-50">
          <th class="p-2 text-left">Título</th>
          <th class="p-2">Nivel</th>
          <th class="p-2">Activo</th>
          <th class="p-2">Vigencia</th>
          <th class="p-2"></th>
        </tr></thead>
        <tbody>
          @foreach($items as $it)
          <tr class="border-t">
            <td class="p-2">{{ $it->titulo }}</td>
            <td class="p-2">{{ $it->nivel }}</td>
            <td class="p-2">{{ $it->activo ? 'Sí' : 'No' }}</td>
            <td class="p-2">
              {{ optional($it->inicio)->format('Y-m-d H:i') }} — {{ optional($it->fin)->format('Y-m-d H:i') }}
            </td>
            <td class="p-2 text-right">
              <a class="text-blue-600" href="{{ route('admin.notificaciones.edit',$it) }}">Editar</a>
              <form action="{{ route('admin.notificaciones.destroy',$it) }}" method="POST" class="inline">@csrf @method('DELETE')
                <button class="text-red-600 ml-2" onclick="return confirm('¿Eliminar?')">Eliminar</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="p-2">{{ $items->links() }}</div>
    </div>
  </div>
</x-app-layout>
