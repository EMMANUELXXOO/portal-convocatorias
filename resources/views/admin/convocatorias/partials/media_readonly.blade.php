@php $galeria = array_values((array)($convocatoria->galeria_urls_public ?? [])); @endphp

@if($convocatoria->portada_url || count($galeria))
  <div class="mb-4">
    <h3 class="font-semibold text-gray-800 mb-2">Imágenes</h3>

    <style>
      .a-media{display:grid;gap:12px}
      @media(min-width:1024px){.a-media{grid-template-columns:2fr 1fr}}
      .a-portada{width:100%;height:220px;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;background:#f8fafc}
      .a-portada img{width:100%;height:100%;object-fit:cover;display:block}
      .a-grid{display:grid;gap:10px;grid-template-columns:repeat(2,1fr)}
      @media(min-width:640px){.a-grid{grid-template-columns:repeat(3,1fr)}}
      .a-thumb{width:100%;height:96px;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#f8fafc}
      .a-thumb img{width:100%;height:100%;object-fit:cover;display:block}
      .a-link{display:block;line-height:0}
    </style>

    <div class="a-media">
      <div class="a-portada">
        @if($convocatoria->portada_url)
          <img
            src="{{ $convocatoria->portada_url }}"
            alt="Portada {{ $convocatoria->titulo }}"
            loading="lazy"
            onerror="this.style.opacity=.3">
        @endif
      </div>
      <div class="a-grid">
        @foreach($galeria as $i => $url)
          <a class="a-link" href="{{ $url }}" target="_blank" rel="noopener" title="Abrir imagen {{ $i+1 }}">
            <div class="a-thumb">
              <img
                src="{{ $url }}"
                alt="Imagen {{ $i+1 }} — {{ $convocatoria->titulo }}"
                loading="lazy"
                onerror="this.style.opacity=.3">
            </div>
          </a>
        @endforeach
      </div>
    </div>
  </div>
@endif
