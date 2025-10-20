<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-semibold">Demografía de aspirantes</h1></x-slot>
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl border p-4">
        <h3 class="font-bold mb-2">Género</h3>
        @foreach($genero as $g)
          <div class="flex justify-between border-b py-2">
            <span>{{ $g->genero ?? 'Sin dato' }}</span>
            <span class="font-semibold">{{ $g->total }}</span>
          </div>
        @endforeach
      </div>

      <div class="bg-white rounded-xl border p-4">
        <h3 class="font-bold mb-2">Rangos de edad</h3>
        @foreach($edades as $e)
          <div class="flex justify-between border-b py-2">
            <span>{{ $e->rango }}</span>
            <span class="font-semibold">{{ $e->total }}</span>
          </div>
        @endforeach
      </div>

      <div class="bg-white rounded-xl border p-4">
        <h3 class="font-bold mb-2">Top preparatorias</h3>
        @forelse($prepas as $p)
          <div class="flex justify-between border-b py-2">
            <span>{{ $p->prepa ?: 'Sin dato' }}</span>
            <span class="font-semibold">{{ $p->total }}</span>
          </div>
        @empty
          <p class="text-gray-500">Sin datos.</p>
        @endforelse
      </div>
    </div>
  </div>
</x-app-layout>
