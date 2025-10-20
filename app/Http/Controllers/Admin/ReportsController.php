<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-2xl text-gray-900 leading-tight">
      Estadísticas demográficas
    </h1>
  </x-slot>

  <style>
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .card-h{padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:700}
    .card-b{padding:18px}
    .grid-3{display:grid;gap:16px;grid-template-columns:repeat(3,1fr)}
    @media (max-width: 1024px){ .grid-3{grid-template-columns:1fr} }
  </style>

  <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Avisos de columnas detectadas --}}
    <div class="text-sm text-gray-500">
      Columnas detectadas:
      <span class="px-2 py-0.5 bg-gray-100 rounded">género: {{ $colGenero ?? '—' }}</span>
      <span class="px-2 py-0.5 bg-gray-100 rounded">fecha_nac: {{ $colFechaN ?? '—' }}</span>
      <span class="px-2 py-0.5 bg-gray-100 rounded">preparatoria: {{ $colPrepa ?? '—' }}</span>
    </div>

    <div class="grid-3">
      {{-- Género / Sexo --}}
      <div class="card">
        <div class="card-h">Distribución por género</div>
        <div class="card-b">
          @if($colGenero && $generoData->isNotEmpty())
            <canvas id="chartGenero" height="200"></canvas>
          @else
            <p class="text-sm text-gray-500">No se encontró columna de género/sexo o no hay datos.</p>
          @endif
        </div>
      </div>

      {{-- Edades --}}
      <div class="card">
        <div class="card-h">Distribución por edad</div>
        <div class="card-b">
          @if($colFechaN && $ageBuckets->sum() > 0)
            <canvas id="chartEdad" height="200"></canvas>
          @else
            <p class="text-sm text-gray-500">No se encontró columna de fecha de nacimiento o no hay datos.</p>
          @endif
        </div>
      </div>

      {{-- Top preparatorias --}}
      <div class="card">
        <div class="card-h">Preparatorias de origen (Top 10)</div>
        <div class="card-b">
          @if($colPrepa && $prepas->isNotEmpty())
            <canvas id="chartPrepa" height="200"></canvas>
          @else
            <p class="text-sm text-gray-500">No se encontró columna de preparatoria/escuela o no hay datos.</p>
          @endif
        </div>
      </div>
    </div>

    {{-- Tabla detalle preparatorias --}}
    @if($colPrepa && $prepas->isNotEmpty())
      <div class="card">
        <div class="card-h">Detalle Top preparatorias</div>
        <div class="card-b overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500">
                <th class="py-2 pr-4">#</th>
                <th class="py-2 pr-4">Preparatoria</th>
                <th class="py-2 pr-4">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($prepas as $i => $row)
                <tr class="border-t">
                  <td class="py-2 pr-4">{{ $i+1 }}</td>
                  <td class="py-2 pr-4">{{ $row->label }}</td>
                  <td class="py-2 pr-4">{{ $row->total }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

  </div>

  {{-- Chart.js (CDN) --}}
  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // ===== Género
      @if($colGenero && $generoData->isNotEmpty())
      (function(){
        const ctx = document.getElementById('chartGenero').getContext('2d');
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: @json($generoData->pluck('label')),
            datasets: [{
              data: @json($generoData->pluck('total')),
            }]
          },
          options: { responsive: true, plugins: { legend: {position:'bottom'} } }
        });
      })();
      @endif

      // ===== Edad
      @if($colFechaN && $ageBuckets->sum() > 0)
      (function(){
        const ctx = document.getElementById('chartEdad').getContext('2d');
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: @json($ageBuckets->keys()),
            datasets: [{
              label: 'Aspirantes',
              data: @json($ageBuckets->values()),
            }]
          },
          options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { precision:0 } } },
            plugins: { legend: { display:false } }
          }
        });
      })();
      @endif

      // ===== Prepas
      @if($colPrepa && $prepas->isNotEmpty())
      (function(){
        const ctx = document.getElementById('chartPrepa').getContext('2d');
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: @json($prepas->pluck('label')),
            datasets: [{
              label: 'Aspirantes',
              data: @json($prepas->pluck('total')),
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            scales: { x: { beginAtZero: true, ticks: { precision:0 } } },
            plugins: { legend: { display:false } }
          }
        });
      })();
      @endif
    </script>
  @endpush
</x-app-layout>
