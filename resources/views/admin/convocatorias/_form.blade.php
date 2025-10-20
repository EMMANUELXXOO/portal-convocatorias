{{-- resources/views/admin/convocatorias/_form.blade.php --}}
@php
  /** @var \App\Models\Convocatoria|null $convocatoria */
  $c = $convocatoria ?? null;

  // Galería por URL
  $galeriaOld = old('galeria_urls_text');
  $galeriaTxt = is_string($galeriaOld)
      ? $galeriaOld
      : (is_array($c?->galeria_urls) ? implode("\n", $c->galeria_urls) : '');

  // Switch carrusel / ajuste
  $carouselEnabled = (bool) old('carousel_enabled', $c->carousel_enabled ?? true);
  $heroFit = old('hero_fit', $c->hero_fit ?? 'cover'); // 'cover' | 'contain'
@endphp

<style>
  .wrap{max-width:72rem;margin:0 auto;padding:0 1rem}
  .grid{display:grid;gap:1rem}
  @media(min-width:1024px){.grid-2{grid-template-columns:1fr 1fr}.grid-3{grid-template-columns:1fr 1fr 1fr}}
  .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
  .card-h{padding:16px;border-bottom:1px solid #e5e7eb;font-weight:700}
  .card-b{padding:16px}

  .input,.textarea,.select{border:1px solid #d1d5db;border-radius:.6rem;padding:.6rem .7rem;width:100%}
  .textarea{min-height:120px}
  .help{font-size:.85rem;color:#6b7280}
  .row{margin-bottom:.85rem}
  .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1rem;border-radius:.6rem;font-weight:700}
  .btn-primary{background:#111827;color:#fff}
  .btn-ghost{background:#fff;border:1px solid #e5e7eb}
  .error{color:#b91c1c;font-size:.85rem;margin-top:.25rem}
  .muted{color:#6b7280}

  /* Preview portada */
  .thumb-frame{position:relative;width:100%;aspect-ratio:16/9;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;background:#f8fafc}
  .thumb-img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}

  /* Grid previews galería */
  .gal-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem}
  @media(min-width:640px){.gal-grid{grid-template-columns:repeat(3,1fr)}}
  @media(min-width:1024px){.gal-grid{grid-template-columns:repeat(5,1fr)}}
  .gal-item{position:relative;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;background:#f8fafc}
  .gal-img{width:100%;aspect-ratio:16/10;object-fit:cover;display:block}
  .badge-mini{position:absolute;top:6px;left:6px;background:rgba(0,0,0,.6);color:#fff;border-radius:999px;font-size:.7rem;font-weight:700;padding:.15rem .45rem}
  .switch{display:flex;align-items:center;gap:.6rem}
  .switch input{width:1.15rem;height:1.15rem}

  /* Botón X */
  .btn-x{
    position:absolute;top:6px;right:6px;
    width:26px;height:26px;border-radius:999px;
    background:#fff;color:#111;border:1px solid rgba(0,0,0,.12);
    display:flex;align-items:center;justify-content:center;
    font-weight:700;line-height:1;cursor:pointer;
    box-shadow:0 1px 2px rgba(0,0,0,.16);
    opacity:0; /* default oculto */
    transition:opacity .15s ease, transform .08s ease;
    z-index:5; /* asegura que quede encima */
  }
  /* Mostrar en hover tanto en portada como galería */
  .thumb-frame:hover .btn-x,
  .gal-item:hover .btn-x,
  .btn-x:focus{opacity:1}
  .btn-x:hover{transform:scale(1.06)}
  .btn-x:active{transform:scale(0.98)}

  /* Si quieres que la portada tenga la X siempre visible: */
  #btnRemoveCover{opacity:1}
</style>

<div class="wrap grid gap-6">

  {{-- Datos generales --}}
  <div class="card">
    <div class="card-h">Datos generales</div>
    <div class="card-b grid grid-2">
      <div class="row">
        <label class="block font-semibold">Título *</label>
        <input class="input" name="titulo" required value="{{ old('titulo', $c->titulo ?? '') }}">
        @error('titulo') <div class="error">{{ $message }}</div> @enderror
      </div>

      <div class="row">
        @php $est = old('estatus', $c->estatus ?? 'activa'); @endphp
        <label class="block font-semibold">Estatus *</label>
        <select name="estatus" class="select">
          <option value="activa"   @selected($est==='activa')>Activa</option>
          <option value="inactiva" @selected($est==='inactiva')>Inactiva</option>
          <option value="borrador" @selected($est==='borrador')>Borrador</option>
          <option value="cerrada"  @selected($est==='cerrada')>Cerrada</option>
        </select>
        @error('estatus') <div class="error">{{ $message }}</div> @enderror
      </div>

      <div class="row lg:col-span-2">
        <label class="block font-semibold">Descripción *</label>
        <textarea class="textarea" name="descripcion" required>{{ old('descripcion', $c->descripcion ?? '') }}</textarea>
        @error('descripcion') <div class="error">{{ $message }}</div> @enderror
      </div>
    </div>
  </div>

  {{-- Fechas, costos y cupo --}}
  <div class="card">
    <div class="card-h">Fechas, costos y cupo</div>
    <div class="card-b grid grid-3">
      <div class="row">
        <label class="block font-semibold">Fecha inicio</label>
        <input class="input" type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $c?->fecha_inicio?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Fecha fin</label>
        <input class="input" type="date" name="fecha_fin" value="{{ old('fecha_fin', $c?->fecha_fin?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Precio ficha *</label>
        <input class="input" type="number" step="0.01" name="precio_ficha" required value="{{ old('precio_ficha', $c->precio_ficha ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Inscripción *</label>
        <input class="input" type="number" step="0.01" name="precio_inscripcion" required value="{{ old('precio_inscripcion', $c->precio_inscripcion ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Mensualidad *</label>
        <input class="input" type="number" step="0.01" name="precio_mensualidad" required value="{{ old('precio_mensualidad', $c->precio_mensualidad ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Cupo total</label>
        <input class="input" type="number" name="cupo_total" value="{{ old('cupo_total', $c->cupo_total ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Publicación resultados</label>
        <input class="input" type="date" name="fecha_publicacion_resultados" value="{{ old('fecha_publicacion_resultados', $c?->fecha_publicacion_resultados?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Inicio de clases</label>
        <input class="input" type="date" name="fecha_inicio_clases" value="{{ old('fecha_inicio_clases', $c?->fecha_inicio_clases?->format('Y-m-d')) }}">
      </div>
    </div>
  </div>

  {{-- Programa / Turnos --}}
  <div class="card">
    <div class="card-h">Programa</div>
    <div class="card-b grid grid-3">
      <div class="row">
        <label class="block font-semibold">Duración</label>
        <input class="input" name="duracion" value="{{ old('duracion', $c->duracion ?? '') }}">
      </div>
      <div class="row lg:col-span-2">
        <label class="block font-semibold">Certificaciones adicionales</label>
        <input class="input" name="certificaciones_adicionales" value="{{ old('certificaciones_adicionales', $c->certificaciones_adicionales ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Matutino</label>
        <input class="input" name="horario_matutino" value="{{ old('horario_matutino', $c->horario_matutino ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Vespertino</label>
        <input class="input" name="horario_vespertino" value="{{ old('horario_vespertino', $c->horario_vespertino ?? '') }}">
      </div>
    </div>
  </div>

  {{-- Ubicación y contacto --}}
  <div class="card">
    <div class="card-h">Ubicación y contacto</div>
    <div class="card-b grid grid-3">
      <div class="row lg:col-span-3">
        <label class="block font-semibold">Ubicación</label>
        <input class="input" name="ubicacion" value="{{ old('ubicacion', $c->ubicacion ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Teléfono 1</label>
        <input class="input" name="telefono_1" value="{{ old('telefono_1', $c->telefono_1 ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Teléfono 2</label>
        <input class="input" name="telefono_2" value="{{ old('telefono_2', $c->telefono_2 ?? '') }}">
      </div>
      <div class="row lg:col-span-3">
        <label class="block font-semibold">Horario de atención</label>
        <input class="input" name="horario_atencion" value="{{ old('horario_atencion', $c->horario_atencion ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Correo 1</label>
        <input class="input" type="email" name="correo_1" value="{{ old('correo_1', $c->correo_1 ?? '') }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Correo 2</label>
        <input class="input" type="email" name="correo_2" value="{{ old('correo_2', $c->correo_2 ?? '') }}">
      </div>
    </div>
  </div>

  {{-- Proceso (fechas por etapa) --}}
  <div class="card">
    <div class="card-h">Proceso de selección</div>
    <div class="card-b grid grid-3">
      <div class="row">
        <label class="block font-semibold">Entrega de solicitudes (inicio)</label>
        <input class="input" type="date" name="fecha_entrega_solicitudes_inicio" value="{{ old('fecha_entrega_solicitudes_inicio', $c?->fecha_entrega_solicitudes_inicio?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Entrega de solicitudes (fin)</label>
        <input class="input" type="date" name="fecha_entrega_solicitudes_fin" value="{{ old('fecha_entrega_solicitudes_fin', $c?->fecha_entrega_solicitudes_fin?->format('Y-m-d')) }}">
      </div>

      <div class="row">
        <label class="block font-semibold">Psicométrico (inicio)</label>
        <input class="input" type="date" name="fecha_psicometrico_inicio" value="{{ old('fecha_psicometrico_inicio', $c?->fecha_psicometrico_inicio?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Psicométrico (fin)</label>
        <input class="input" type="date" name="fecha_psicometrico_fin" value="{{ old('fecha_psicometrico_fin', $c?->fecha_psicometrico_fin?->format('Y-m-d')) }}">
      </div>

      <div class="row">
        <label class="block font-semibold">Entrevistas (inicio)</label>
        <input class="input" type="date" name="fecha_entrevistas_inicio" value="{{ old('fecha_entrevistas_inicio', $c?->fecha_entrevistas_inicio?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Entrevistas (fin)</label>
        <input class="input" type="date" name="fecha_entrevistas_fin" value="{{ old('fecha_entrevistas_fin', $c?->fecha_entrevistas_fin?->format('Y-m-d')) }}">
      </div>

      <div class="row">
        <label class="block font-semibold">Examen de conocimientos</label>
        <input class="input" type="date" name="fecha_examen_conocimientos" value="{{ old('fecha_examen_conocimientos', $c?->fecha_examen_conocimientos?->format('Y-m-d')) }}">
      </div>

      <div class="row">
        <label class="block font-semibold">Curso propedéutico (inicio)</label>
        <input class="input" type="date" name="fecha_curso_propedeutico_inicio" value="{{ old('fecha_curso_propedeutico_inicio', $c?->fecha_curso_propedeutico_inicio?->format('Y-m-d')) }}">
      </div>
      <div class="row">
        <label class="block font-semibold">Curso propedéutico (fin)</label>
        <input class="input" type="date" name="fecha_curso_propedeutico_fin" value="{{ old('fecha_curso_propedeutico_fin', $c?->fecha_curso_propedeutico_fin?->format('Y-m-d')) }}">
      </div>
    </div>
  </div>

  {{-- Portada, carrusel y galería --}}
  <div class="card">
    <div class="card-h">Portada, carrusel y galería</div>
    <div class="card-b grid grid-2">
     {{-- Portada --}}
<div class="row">
  <label class="block font-semibold">Portada</label>
  <div class="my-2 thumb-frame">
    <img id="portadaPreview"
         src="{{ $c?->portada_url ?? asset('images/convocatoria-placeholder.jpg') }}"
         class="thumb-img" alt="Portada">

    @if(!empty($c?->portada_path))
  <button type="button"
          id="btnRemoveCover"
          class="btn-x"
          title="Eliminar portada"
          aria-label="Eliminar portada"
          data-remove-url="{{ route('admin.convocatorias.removeCover', $c->id) }}">×</button>
@endif

  </div>
  <input class="input mt-2" type="file" name="portada" id="portadaInput" accept="image/*">
  @error('portada') <div class="error">{{ $message }}</div> @enderror
  <div class="help mt-1">
    Solo se usa como imagen representativa (no aparece en el carrusel).<br>
    Recomendado: 1600×900 px, &lt; 3–4 MB.
  </div>
</div>

      {{-- Config carrusel --}}
      <div class="row">
        <label class="block font-semibold">Carrusel en portada</label>
        <input type="hidden" name="carousel_enabled" value="0">
        <div class="switch mb-3">
          <input type="checkbox" id="carousel_enabled" name="carousel_enabled" value="1" @checked($carouselEnabled)>
          <label for="carousel_enabled">Activar carrusel (máx. 5 imágenes)</label>
        </div>

        <label class="block font-semibold">Ajuste de imagen del carrusel</label>
        <select class="select" name="hero_fit" id="hero_fit">
          <option value="cover"   @selected($heroFit==='cover')>Recortar para llenar (recomendado)</option>
          <option value="contain" @selected($heroFit==='contain')>Mostrar completa (puede dejar franjas)</option>
        </select>
        <div class="help mt-1">Controla cómo se muestran las imágenes en el hero/carrusel.</div>
      </div>

      {{-- Subida múltiple para carrusel --}}
      <div class="row lg:col-span-2">
        <label class="block font-semibold">Imágenes del carrusel (subir archivos)</label>
        <input class="input" type="file" id="galeriaArchivos" name="galeria_archivos[]" accept="image/*" multiple>
        <div class="help">
          Estas son las únicas imágenes que se mostrarán en el carrusel. Límite: 5 imágenes.
          <br>
          <strong>Tamaño recomendado:</strong> 1920×1080 px (relación 16:9). Peso &lt; 3–4 MB.
          <br>
          <em>Tip:</em> Con “Recortar para llenar (cover)” puede recortarse un poco en los bordes; deja área importante centrada.
        </div>
        <div id="galeriaPreview" class="gal-grid mt-3"></div>
        <div id="galeriaCount" class="help mt-1"></div>
        @error('galeria_archivos') <div class="error">{{ $message }}</div> @enderror
        @error('galeria_archivos.*') <div class="error">{{ $message }}</div> @enderror
      </div>

      {{-- URLs externas (se suman) --}}
      <div class="row lg:col-span-2">
        <label class="block font-semibold">Galería por URL (una por línea)</label>
        <textarea class="textarea" name="galeria_urls_text"
                  placeholder="https://.../foto1.jpg&#10;https://.../foto2.jpg">{{ $galeriaTxt }}</textarea>
        <div class="help">Se combinarán con las imágenes subidas (arriba). Se usarán las primeras 5 para el carrusel.</div>
        @error('galeria_urls_text') <div class="error">{{ $message }}</div> @enderror
      </div>

      {{-- Previsualización de la galería guardada (con X) --}}
      <div class="row lg:col-span-2">
        <label class="block font-semibold">Imágenes guardadas</label>
        @php $gal = is_array($c?->galeria_urls_public ?? null) ? $c->galeria_urls_public : []; @endphp

@if(count($gal))
  <div
    id="galGridSaved"
    class="gal-grid"
    data-remove-base="{{ route('admin.convocatorias.removeImage', ['convocatoria'=>$c->id, 'index'=>'__INDEX__']) }}"
  >
    @foreach($gal as $i => $u)
      <div class="gal-item">
        <span class="badge-mini">{{ $i+1 }}</span>
        <img src="{{ $u }}" class="gal-img" alt="Imagen galería" loading="lazy" onerror="this.style.opacity=.3">
        <button
          type="button"
          class="btn-x btn-remove-img"
          title="Eliminar"
          aria-label="Eliminar"
          data-index="{{ $i }}"
        >×</button>
      </div>
    @endforeach
  </div>
@else
  <div class="muted">No hay imágenes en la galería todavía.</div>
@endif
      </div>
    </div>
  </div>

  {{-- Requisitos y notas --}}
  <div class="card">
    <div class="card-h">Requisitos y notas</div>
    <div class="card-b grid grid-3">
      <div class="row lg:col-span-3">
        <label class="block font-semibold">Requisitos generales</label>
        <textarea class="textarea" name="requisitos_generales">{{ old('requisitos_generales', $c->requisitos_generales ?? '') }}</textarea>
      </div>
      <div class="row lg:col-span-3">
        <label class="block font-semibold">Requisitos para examen y entrevista</label>
        <textarea class="textarea" name="requisitos_examen_entrevista">{{ old('requisitos_examen_entrevista', $c->requisitos_examen_entrevista ?? '') }}</textarea>
      </div>
      <div class="row lg:col-span-3">
        <label class="block font-semibold">Documentos al ser aceptado</label>
        <textarea class="textarea" name="documentos_requeridos">{{ old('documentos_requeridos', $c->documentos_requeridos ?? '') }}</textarea>
      </div>
      <div class="row lg:col-span-3">
        <label class="block font-semibold">Notas</label>
        <textarea class="textarea" name="notas">{{ old('notas', $c->notas ?? '') }}</textarea>
      </div>
    </div>
  </div>

  {{-- Acciones --}}
  <div class="card">
    <div class="card-b flex items-center gap-3">
      <a href="{{ route('admin.convocatorias.index') }}" class="btn btn-ghost">Cancelar</a>
      <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Guardar' }}</button>
    </div>
  </div>
</div>

{{-- JS: previews, límite, portada y eliminación de imágenes --}}
<script>
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // ---- Galería
  (function setupRemoveGallery(){
    const grid = document.getElementById('galGridSaved');
    if (!grid) return;

    const base = grid.getAttribute('data-remove-base'); // .../remove-image/__INDEX__
    function reindex(){
      grid.querySelectorAll('.badge-mini').forEach((b,i)=>b.textContent=i+1);
      grid.querySelectorAll('.btn-remove-img').forEach((btn,i)=>btn.dataset.index=i);
    }

    grid.addEventListener('click', async (ev)=>{
      const btn = ev.target.closest('.btn-remove-img');
      if (!btn) return;

      const idx = btn.dataset.index;
      if (idx === undefined) { alert('Índice no encontrado'); return; }

      if (!confirm('¿Eliminar esta imagen de la galería?')) return;

      const url = base.replace('__INDEX__', idx);
      try{
        const resp = await fetch(url, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        if (!resp.ok) throw new Error('HTTP '+resp.status);
        btn.closest('.gal-item')?.remove();
        reindex();
      }catch(e){
        alert('No se pudo eliminar la imagen. '+e.message);
        console.error('removeImage failed', {url, idx, e});
      }
    });
  })();

  // ---- Portada
  (function setupRemoveCover(){
    const btn = document.getElementById('btnRemoveCover');
    if (!btn) return;

    const url = btn.getAttribute('data-remove-url');
    btn.addEventListener('click', async ()=>{
      if (!confirm('¿Eliminar la portada?')) return;
      try{
        const resp = await fetch(url, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        if (!resp.ok) throw new Error('HTTP '+resp.status);

        const img = document.getElementById('portadaPreview');
        if (img) img.src = '{{ asset('images/convocatoria-placeholder.jpg') }}';
        btn.remove();
      }catch(e){
        alert('No se pudo eliminar la portada. '+e.message);
        console.error('removeCover failed', {url, e});
      }
    });
  })();
</script>

