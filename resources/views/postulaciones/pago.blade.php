<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Pago de ficha — {{ $postulacion->convocatoria->titulo }}
    </h2>
  </x-slot>

  @php
    $pagado = $postulacion->estatus === \App\Models\Postulacion::ESTATUS_PAGADO;
  @endphp

  <style>
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .card-b{padding:20px}
    .grid{display:grid;gap:16px}
    @media(min-width:768px){.grid-2{grid-template-columns:1fr 1fr}}
    .mono{font-family:monospace}
    .help{font-size:.85rem;color:#6b7280}
    .input{border:1px solid #d1d5db;border-radius:.5rem;padding:.6rem .7rem;width:100%}
    .btn{display:inline-block;padding:.6rem 1rem;font-weight:600;border-radius:.55rem;line-height:1;text-decoration:none}
    .btn-primary{background:#059669;color:#fff}.btn-primary:hover{background:#047857}
    .btn-ghost{background:#fff;color:#111827;border:2px solid #e5e7eb}
    .btn-dark{background:#111827;color:#fff}.btn-dark:hover{background:#0b1220}
    .btn-gray{background:#4b5563;color:#fff}.btn-gray:hover{background:#374151}
    .badge{display:inline-block;background:#eef2ff;color:#3730a3;border-radius:999px;padding:.2rem .6rem;font-size:.78rem}
  </style>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

      @if (session('status'))
        <div class="mb-4 p-3 rounded" style="background:#ecfdf5;color:#065f46;">
          {{ session('status') }}
        </div>
      @endif

      <div class="card">
        <div class="card-b">
          <h3 class="text-lg font-bold mb-2">Datos de tu ficha</h3>
          <p class="help mb-4">
            Conserva estos datos. Usa la <strong>referencia de pago</strong> en tu banco o pago en línea.
          </p>

          <div class="grid grid-2">
            <div>
              <div class="help">Folio interno</div>
              <div class="mono text-base">{{ $postulacion->folio }}</div>
            </div>

            <div>
              <div class="help">Referencia de pago</div>
              <div class="flex items-center gap-2">
                <span class="mono text-base" id="refPago">{{ $postulacion->referencia_pago }}</span>
                <!-- Copiar referencia -->
                <button type="button" class="btn btn-ghost" id="btnCopyRef" aria-label="Copiar referencia">
                  Copiar
                </button>
              </div>
              <div class="help">Presenta esta referencia tal cual aparece.</div>
            </div>

            <div>
              <div class="help">Convocatoria</div>
              <div>{{ $postulacion->convocatoria->titulo }}</div>
            </div>

            <div>
              <div class="help">Estatus</div>
              <div>
                <span class="badge">{{ ucfirst(str_replace('_',' ',$postulacion->estatus)) }}</span>
              </div>
            </div>
          </div>

          @if($pagado)
            <hr class="my-6">

            <div class="p-3 rounded" style="background:#ecfdf5;color:#065f46;">
              Pago confirmado el {{ optional($postulacion->fecha_pago)->format('Y-m-d H:i') }}.<br>
              Folio bancario: <span class="mono">{{ $postulacion->folio_banco ?? 'N/D' }}</span>
            </div>

            <!-- Acciones útiles al estar pagado -->
            <div class="mt-4 flex flex-wrap gap-2">
              <a href="{{ route('postulaciones.recibo', $postulacion) }}" class="btn btn-dark">Descargar recibo PDF</a>

              <form method="POST" action="{{ route('postulaciones.reenviar-recibo', $postulacion) }}" class="inline">
                @csrf
                <button class="btn btn-gray" type="submit">Reenviar por correo</button>
              </form>

              <a href="{{ route('postulaciones.index') }}" class="btn btn-ghost">Volver a mis postulaciones</a>
            </div>
          @else
            <hr class="my-6">

            <h4 class="font-semibold mb-2">Confirmar pago</h4>
            <p class="help mb-3">
              Después de pagar en el banco, ingresa el <strong>folio del comprobante bancario</strong> para validar tu pago.
            </p>

            <form id="formConfirmarPago" method="POST" action="{{ route('postulaciones.confirmar-pago', $postulacion) }}">
              @csrf
              <label for="folio_banco" class="block text-sm text-gray-700 mb-1">Folio del comprobante *</label>
              <input id="folio_banco" type="text" name="folio_banco" required class="input" placeholder="Ej. 123456/ABC-789" value="{{ old('folio_banco') }}">
              @error('folio_banco') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror

              <div class="mt-4 flex gap-2">
                <a href="{{ route('postulaciones.index') }}" class="btn btn-ghost">Cancelar</a>
                <button id="btnSubmit" class="btn btn-primary" type="submit">Confirmar pago</button>
              </div>
            </form>
          @endif
        </div>
      </div>

    </div>
  </div>

  @if(!$pagado)
  <script>
    // Evitar doble envío
    document.getElementById('formConfirmarPago')?.addEventListener('submit', function(){
      const b = document.getElementById('btnSubmit');
      if (b) { b.disabled = true; b.textContent = 'Enviando...'; }
    });
  </script>
  @endif

  <script>
    // Copiar referencia
    (function(){
      const ref = document.getElementById('refPago');
      const btn = document.getElementById('btnCopyRef');
      if (!ref || !btn) return;
      btn.addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(ref.textContent.trim());
          btn.textContent = 'Copiado ✓';
          setTimeout(()=> btn.textContent = 'Copiar', 1500);
        } catch(e) {
          alert('No se pudo copiar. Selecciona y copia manualmente.');
        }
      });
    })();
  </script>
</x-app-layout>
