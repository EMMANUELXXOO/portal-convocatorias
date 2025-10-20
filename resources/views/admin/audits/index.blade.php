<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Auditoría</h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow sm:rounded-lg p-4">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-600 border-b">
                <th class="py-2">Fecha</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Acción</th>
                <th>Entidad</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
            @forelse($items as $a)
              <tr class="border-b">
                <td class="py-2 text-gray-500">{{ $a->created_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $a->user?->name }} <span class="text-gray-400">{{ $a->user?->email }}</span></td>
                <td>{{ $a->user_role }}</td>
                <td class="font-mono">{{ $a->action }}</td>
                <td class="text-gray-600">
                  {{ class_basename($a->entity_type) }} #{{ $a->entity_id }}
                </td>
                <td class="text-gray-600">{{ $a->ip }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="py-4 text-gray-500">Sin registros aún.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-4">
          {{ $items->links() }}
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
