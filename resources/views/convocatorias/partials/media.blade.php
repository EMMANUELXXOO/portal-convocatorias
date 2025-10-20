{{-- Muestra portada + galería (solo lectura) --}}
@php $galeria = (array) ($convocatoria->galeria_urls ?? []); @endphp

@if($convocatoria->portada_url || count($galeria))
  <style>
    .c-media{display:grid;gap:12px}
    @media(min-width:768px){.c-media{grid-template-columns:2fr 1fr}}
    .c-portada{width:100%;height:260px;border-radius:14px;border:1px solid #e5e7eb;overflow:hidden;background:#f8fafc}
    .c-portada img{width:100%;height:100%;object-fit:cover;display:block}
    .c-grid{display:grid;gap:10px}
    .c-thumb{width:100%;height:120px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#f8fafc}
    .c-thumb img{width:100%;height:100%;object-fit:cover;display:block}
    .c-link{display:block;line-height:0}
  </style>

  <div class="c-media">
    <div class="c-portada">
      @if($convocatoria->portada_url)
        <img src="{{ $convocatoria->portada_url }}" alt="Portada {{ $convocatoria->titulo }}">
      @else
        {{-- Placeholder opcional --}}
      @endif
    </div>

    <div class="c-grid">
      @foreach($galeria as $i => $url)
        <a class="c-link" href="{{ $url }}" target="_blank" rel="noopener" title="Abrir imagen {{ $i+1 }}">
          <div class="c-thumb">
            <img src="{{ $url }}" alt="Imagen {{ $i+1 }} — {{ $convocatoria->titulo }}">
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endif
