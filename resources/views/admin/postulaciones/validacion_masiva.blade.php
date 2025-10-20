<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-2xl text-gray-900 leading-tight">Validación masiva de pagos</h1>
  </x-slot>

  <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Filtros --}}
    <form method="GET" class="bg-white border rounded-xl shadow-sm p-4 grid md:grid-cols-5 gap-3">
      <div>
        <label class="text-sm text-gray-600">Convocatoria</label>
        <select name="convocatoria_id" class="mt-1 w-full border rounded">
          <option value="">Todas</option>
          @foreach($convocatorias as $c)
            <option value="{{ $c->id }}" @selected($convocatoriaId==$c->id)>{{ $c->titulo }}</option>
          @endforeach
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="text-sm text-gray-600">Buscar</label>
        <input type="text" name="buscar" value="{{ $buscar }}" class="mt-1 w-full border rounded" placeholder="folio, referencia, nombre, correo, título...">
      </div>
      <div class="flex items-end gap-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" name="solo_pendientes" value="1" @checked($soloPendientes) class="rounded">
          Solo pago pendiente
        </label>
      </div>
      <div class="flex items-end">
        <button class="px-4 py-2 rounded bg-blue-600 text-white">Aplicar filtros</button>
      </div>
    </form>

    {{-- Acción masiva --}}
    <form method="POST" action="{{ route('admin.postulaciones.validar-masivo') }}" class="bg-white border rounded-xl shadow-sm p-4">
      @csrf
      @if ($errors->any())
        <div class="mb-3 p-3 rounded bg-red-50 text-red-700">
          {{ $errors->first() }}
        </div>
      @endif
      @if (session('status'))
        <div class="mb-3 p-3 rounded bg-green-50 text-green-700">
          {{ session('status') }}
        </div>
      @endif

      <div class="grid md:grid-cols-4 gap-3 mb-4">
        <div>
          <label class="text-sm text-gray-600">Folio bancario (opcional, se aplica si vacío)</label>
          <input name="folio_banco_global" class="mt-1 w-full border rounded" placeholder="Folio banco (mismo para todos)">
        </div>
        <div>
          <label class="text-sm text-gray-600">Fecha de pago</label>
          <input type="datetime-local" name="fecha_pago" value="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 w-full border rounded">
          <label class="inline-flex items-center gap-2 text-sm mt-1">
            <input type="checkbox" name="poner_fecha_pago" value="1" checked class="rounded">
            Colocar si está vacía
          </label>
        </div>
        <div class="md:col-span-2 flex items-end justify-end">
          <button class="px-4 py-2 rounded bg-emerald-600 text-white" onclick="return confirm('¿Marcar como PAGADO los seleccionados?')">
            Validar pagos seleccionados
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-gray-50">
              <th class="p-2"><input type="checkbox" id="check-all" class="rounded"></th>
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">Convocatoria</th>
              <th class="p-2 text-left">Usuario</th>
              <th class="p-2 text-left">Folio</th>
              <th class="p-2 text-left">Referencia</th>
              <th class="p-2 text-left">Estatus</th>
              <th class="p-2 text-left">Fecha pago</th>
              <th class="p-2 text-left">Creada</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $p)
              <tr class="border-b">
                <td class="p-2">
                  <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="chk row-chk rounded">
                </td>
                <td class="p-2">{{ $p->id }}</td>
                <td class="p-2">{{ $p->convocatoria->titulo ?? '—' }}</td>
                <td class="p-2">
                  {{ $p->user->name ?? '—' }}<br>
                  <span class="text-gray-500">{{ $p->user->email ?? '' }}</span>
                </td>
                <td class="p-2">{{ $p->folio }}</td>
                <td class="p-2">{{ $p->referencia_pago }}</td>
                <td class="p-2">
                  <span class="px-2 py-0.5 rounded text-xs
                    @if($p->estatus==='pagado') bg-green-100 text-green-700
                    @elseif($p->estatus==='pago_pendiente') bg-yellow-100 text-yellow-700
                    @else bg-gray-100 text-gray-700 @endif">
                    {{ $p->estatus }}
                  </span>
                </td>
                <td class="p-2">{{ optional($p->fecha_pago)->format('Y-m-d H:i') ?: '—' }}</td>
                <td class="p-2">{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
              </tr>
            @empty
              <tr><td class="p-3 text-gray-500" colspan="9">No hay registros con los filtros actuales.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $items->links() }}
      </div>
    </form>
  </div>

  @push('scripts')
  <script>
    const checkAll = document.getElementById('check-all');
    const rows = document.querySelectorAll('.row-chk');
    if (checkAll) {
      checkAll.addEventListener('change', e => {
        rows.forEach(chk => { chk.checked = checkAll.checked; });
      });
    }
  </script>
  @endpush
</x-app-layout>
