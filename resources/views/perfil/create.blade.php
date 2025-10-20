{{-- resources/views/perfil/create.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Ficha del Aspirante
    </h2>
  </x-slot>

  {{-- Estilos de botones (independientes de Tailwind) --}}
  <style>
    .btn{display:inline-block;padding:.625rem 1rem;font-weight:600;border-radius:.6rem;
         line-height:1;text-decoration:none;transition:background .15s,color .15s,box-shadow .15s}
    .btn:focus{outline:0;box-shadow:0 0 0 3px rgba(59,130,246,.45)}
    .btn-accent{background:#2563eb;color:#fff!important}
    .btn-accent:hover{background:#1d4ed8}
    .btn-outline{background:#fff;color:#111827!important;border:2px solid #d1d5db}
    .btn-outline:hover{background:#f9fafb}

    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .card-h{padding:14px 18px;border-bottom:1px solid #e5e7eb;font-weight:700}
    .card-b{padding:18px}
    .hint{font-size:.85rem;color:#6b7280}
  </style>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
      <div class="card">
        <div class="card-h">Datos del alumno</div>
        <div class="card-b">

          @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
              <ul class="list-disc pl-6">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
              </ul>
            </div>
          @endif

          @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-700">
              {{ session('status') }}
            </div>
          @endif

          {{-- IMPORTANTE: asegúrate de tener la ruta aspirante.perfil.store definida --}}
        <form method="POST" action="{{ route('perfil.store') }}" class="space-y-8">
            @csrf

            {{-- === Identidad y contacto === --}}
            <section class="space-y-5">
              <h3 class="font-semibold text-gray-900">Identidad y contacto</h3>

              {{-- Nombre --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre completo *</label>
                <input
                  type="text"
                  name="nombre_completo"
                  value="{{ old('nombre_completo', ($perfil->nombre_completo ?? auth()->user()->name)) }}"
                  placeholder="Tu nombre completo"
                  autocomplete="name"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  required
                >
                @error('nombre_completo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- CURP --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">CURP *</label>
                <input
                  type="text"
                  name="curp"
                  value="{{ old('curp', $perfil->curp ?? '') }}"
                  placeholder="Ej. ABCD001122HDFRRS09"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase"
                  minlength="18" maxlength="18"
                  pattern="[A-Z0-9]{18}"
                  required
                >
                <p class="hint">Ingresa exactamente 18 caracteres (solo letras y números, en mayúsculas).</p>
                @error('curp') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Teléfono --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">Teléfono de contacto *</label>
                <input
                  type="text"
                  name="telefono"
                  value="{{ old('telefono', $perfil->telefono ?? '') }}"
                  placeholder="10 dígitos"
                  inputmode="tel"
                  pattern="[0-9+\s()-]{7,20}"
                  autocomplete="tel"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  required
                >
                @error('telefono') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Correo principal --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">Correo personal de contacto *</label>
                <input
                  type="email"
                  name="correo_contacto"
                  value="{{ old('correo_contacto', $perfil->correo_contacto ?? auth()->user()->email) }}"
                  placeholder="tucorreo@dominio.com"
                  autocomplete="email"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  required
                >
                @error('correo_contacto') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- Correo alternativo --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">Correo alternativo</label>
                <input
                  type="email"
                  name="correo_alternativo"
                  value="{{ old('correo_alternativo', $perfil->correo_alternativo ?? '') }}"
                  placeholder="opcional@dominio.com"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('correo_alternativo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>
            </section>

            {{-- === Información académica === --}}
            <section class="space-y-5">
              <h3 class="font-semibold text-gray-900">Información académica</h3>

              {{-- Escuela de procedencia (prepa) --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">Escuela de procedencia (preparatoria) *</label>
                <input
                  type="text"
                  name="preparatoria"
                  value="{{ old('preparatoria', $perfil->preparatoria ?? '') }}"
                  placeholder="Nombre de la preparatoria"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  required
                >
                @error('preparatoria') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Promedio general (0–10) --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Promedio general *</label>
                  <input
                    type="number"
                    name="promedio_general"
                    value="{{ old('promedio_general', $perfil->promedio_general ?? '') }}"
                    step="0.01" min="0" max="10" inputmode="decimal"
                    placeholder="Ej. 8.60"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                  @error('promedio_general') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Año de egreso --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Año de egreso de prepa *</label>
                  <input
                    type="number"
                    name="egreso_prepa_anio"
                    value="{{ old('egreso_prepa_anio', $perfil->egreso_prepa_anio ?? '') }}"
                    min="1980" max="{{ now()->year + 1 }}"
                    placeholder="{{ now()->year }}"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                  @error('egreso_prepa_anio') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Documento probatorio --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Documento probatorio *</label>
                  <select
                    name="documento_terminacion"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                    @php
                      $docVal = old('documento_terminacion', $perfil->documento_terminacion ?? '');
                    @endphp
                    <option value="">Selecciona…</option>
                    <option value="constancia"  @selected($docVal==='constancia')>Constancia de estudios</option>
                    <option value="certificado" @selected($docVal==='certificado')>Certificado</option>
                    <option value="kardex"      @selected($docVal==='kardex')>Kardex</option>
                  </select>
                  @error('documento_terminacion') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
              </div>
            </section>

            {{-- === Datos personales y de salud === --}}
            <section class="space-y-5">
              <h3 class="font-semibold text-gray-900">Datos personales y de salud</h3>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Fecha de nacimiento (name = fecha_nac, se mapea a fecha_nacimiento en el Request) --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento *</label>
                  @php
                    $fn = $perfil->fecha_nacimiento ?? null;
                    if ($fn instanceof \Carbon\Carbon) { $fn = $fn->toDateString(); }
                  @endphp
                  <input
                    id="fecha_nac"
                    type="date"
                    name="fecha_nac"
                    value="{{ old('fecha_nac', $fn) }}"
                    max="{{ now()->toDateString() }}"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                  @error('fecha_nac') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Edad (auto) --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Edad</label>
                  <input
                    id="edad_display"
                    type="text"
                    value=""
                    readonly
                    class="mt-1 w-full rounded border-gray-200 bg-gray-50 text-gray-700"
                  >
                  <p class="hint">Se calcula automáticamente a partir de tu fecha de nacimiento.</p>
                </div>

                {{-- Sexo (valores en texto; valida en Request: in:femenino,masculino,no_binario,prefiero_no_decir) --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Sexo *</label>
                  @php $sexoVal = old('sexo', $perfil->sexo ?? ''); @endphp
                  <select
                    name="sexo"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                    <option value="">Selecciona…</option>
                    <option value="femenino"   @selected($sexoVal==='femenino')>Femenino</option>
                    <option value="masculino"  @selected($sexoVal==='masculino')>Masculino</option>
                    <option value="no_binario" @selected($sexoVal==='no_binario')>No binario</option>
                    <option value="prefiero_no_decir" @selected($sexoVal==='prefiero_no_decir')>Prefiero no decir</option>
                  </select>
                  @error('sexo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Lugar de nacimiento --}}
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Lugar de nacimiento *</label>
                  <input
                    type="text"
                    name="lugar_nacimiento"
                    value="{{ old('lugar_nacimiento', $perfil->lugar_nacimiento ?? '') }}"
                    placeholder="Ciudad, Estado, País"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                  @error('lugar_nacimiento') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Tipo de sangre --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tipo de sangre</label>
                  @php $ts = old('tipo_sangre', $perfil->tipo_sangre ?? ''); @endphp
                  <select
                    name="tipo_sangre"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="">No especificado</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $opt)
                      <option value="{{ $opt }}" @selected($ts===$opt)>{{ $opt }}</option>
                    @endforeach
                  </select>
                  @error('tipo_sangre') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Estado de salud --}}
                <div>
                  <label class="block text-sm font-medium text-gray-700">Estado de salud</label>
                  <select
                    name="estado_salud"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    @php $es = old('estado_salud', $perfil->estado_salud ?? ''); @endphp
                    <option value="">No especificado</option>
                    <option value="excelente" @selected($es==='excelente')>Excelente</option>
                    <option value="bueno"     @selected($es==='bueno')>Bueno</option>
                    <option value="regular"   @selected($es==='regular')>Regular</option>
                    <option value="delicado"  @selected($es==='delicado')>Delicado</option>
                  </select>
                  @error('estado_salud') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Alergias / enfermedades crónicas --}}
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Alergias o enfermedades crónicas</label>
                  <input
                    type="text"
                    name="alergias"
                    value="{{ old('alergias', $perfil->alergias ?? '') }}"
                    placeholder="Ej. alergia a penicilina, asma, diabetes…"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                  >
                  @error('alergias') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
              </div>

              {{-- Medicamentos permanentes --}}
              <div>
                <label class="block text-sm font-medium text-gray-700">Medicamentos permanentes</label>
                <input
                  type="text"
                  name="medicamentos"
                  value="{{ old('medicamentos', $perfil->medicamentos ?? '') }}"
                  placeholder="Si tomas algún medicamento de forma regular, indícalo aquí"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >
                @error('medicamentos') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>
            </section>

            {{-- === Contacto de emergencia === --}}
            <section class="space-y-5">
              <h3 class="font-semibold text-gray-900">Contacto de emergencia</h3>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Nombre *</label>
                  <input
                    type="text"
                    name="contacto_emergencia_nombre"
                    value="{{ old('contacto_emergencia_nombre', $perfil->contacto_emergencia_nombre ?? '') }}"
                    placeholder="Nombre completo de contacto"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                  @error('contacto_emergencia_nombre') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Teléfono *</label>
                  <input
                    type="text"
                    name="contacto_emergencia_tel"
                    value="{{ old('contacto_emergencia_tel', $perfil->contacto_emergencia_tel ?? '') }}"
                    placeholder="10 dígitos"
                    inputmode="tel"
                    pattern="[0-9+\s()-]{7,20}"
                    class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    required
                  >
                  @error('contacto_emergencia_tel') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
              </div>
            </section>

            {{-- === Información adicional === --}}
            <section class="space-y-5">
              <h3 class="font-semibold text-gray-900">Información adicional</h3>

              <div>
                <label class="block text-sm font-medium text-gray-700">Comentarios / información adicional</label>
                <textarea
                  name="info_adicional"
                  rows="4"
                  placeholder="¿Algo más que debamos saber?"
                  class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('info_adicional', $perfil->info_adicional ?? '') }}</textarea>
                @error('info_adicional') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>
            </section>

            {{-- Acciones --}}
            <div class="pt-2 flex items-center gap-3">
              <button type="submit" class="btn btn-accent">Guardar ficha</button>
              <a href="{{ url()->previous() }}" class="btn btn-outline">Cancelar</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  {{-- Cálculo de edad (cliente) --}}
  @push('scripts')
    <script>
      (function(){
        const $f = document.getElementById('fecha_nac');
        const $edad = document.getElementById('edad_display');

        function calcAge(iso){
          if(!iso) return '';
          const birth = new Date(iso + 'T00:00:00');
          if (isNaN(birth)) return '';
          const today = new Date();
          let age = today.getFullYear() - birth.getFullYear();
          const m = today.getMonth() - birth.getMonth();
          if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
          return (age >= 0 && age < 140) ? `${age} años` : '';
        }

        function update(){ if ($edad) $edad.value = calcAge($f?.value || ''); }

        $f && $f.addEventListener('change', update);
        document.addEventListener('DOMContentLoaded', update);
        update();
      })();
    </script>
  @endpush
</x-app-layout>
