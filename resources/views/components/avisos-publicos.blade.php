{{-- resources/views/components/avisos-publicos.blade.php --}}
@php
  // $audiencia: 'aspirantes' | 'admin' | 'todos'
  $aud = $audiencia ?? (auth()->check() && auth()->user()->can('backoffice') ? 'admin' : 'aspirantes');
  $avisos = \App\Models\AdminNotice::vigentesPara($aud)->take(5)->get();
@endphp

@if($avisos->isNotEmpty())
  <div class="space-y-2">
    @foreach($avisos as $n)
      @php
        $colors = [
          'info'    => 'bg-blue-50 text-blue-800 border-blue-200',
          'success' => 'bg-green-50 text-green-800 border-green-200',
          'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
          'danger'  => 'bg-red-50 text-red-800 border-red-200',
        ];
      @endphp
      <div class="border rounded-md p-3 {{ $colors[$n->nivel] ?? '' }}">
        <div class="font-semibold">{{ $n->titulo }}</div>
        <div class="text-sm">{{ $n->mensaje }}</div>
      </div>
    @endforeach
  </div>
@endif
