{{-- resources/views/postulaciones/edit.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Editar ficha del aspirante (Postulación #{{ $postulacion->id }})
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">

          @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-700">
              {{ session('status') }}
            </div>
          @endif

          <form method="POST" action="{{ route('postulaciones.update', $postulacion) }}" class="space-y-6" novalidate>
            @csrf
            @method('PATCH')

            @php
              $perfil = $perfil ?? (object)[];
              $d = fn($k,$default='') => old($k, $perfil->$k ?? $default);
              $fnVal = $perfil->fecha_nac instanceof \Carbon\Carbon ? $perfil->fecha_nac->toDateString() : $d('fecha_nac');
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre completo *</label>
                <input type="text" name="nombre_completo" value="{{ $d('nombre_completo', auth()->user()->name) }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('nombre_completo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">CURP</label>
                <input type="text" name="curp" value="{{ $d('curp') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" maxlength="18">
                @error('curp') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Teléfono *</label>
                <input type="text" name="telefono" value="{{ $d('telefono') }}" inputmode="tel" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('telefono') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Correo contacto *</label>
                <input type="email" name="correo_contacto" value="{{ $d('correo_contacto', auth()->user()->email) }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('correo_contacto') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Correo alternativo</label>
                <input type="email" name="correo_alternativo" value="{{ $d('correo_alternativo') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('correo_alternativo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Fecha de nacimiento *</label>
                <input id="fecha_nac" type="date" name="fecha_nac" value="{{ $fnVal }}" max="{{ now()->toDateString() }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                @error('fecha_nac') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <p id="edad_text" class="text-sm text-gray-500 mt-1"></p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Sexo</label>
                <select name="sexo" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">—</option>
                  <option value="masculino" {{ $d('sexo')==='masculino'?'selected':'' }}>Masculino</option>
                  <option value="femenino"  {{ $d('sexo')==='femenino'?'selected':'' }}>Femenino</option>
                  <option value="otro"      {{ $d('sexo')==='otro'?'selected':'' }}>Otro</option>
                </select>
                @error('sexo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Lugar de nacimiento</label>
                <input type="text" name="lugar_nacimiento" value="{{ $d('lugar_nacimiento') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('lugar_nacimiento') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Escuela de procedencia (Preparatoria)</label>
                <input type="text" name="preparatoria" value="{{ $d('preparatoria') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('preparatoria') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Promedio general</label>
                <input type="number" step="0.01" min="0" max="100" name="promedio_general" value="{{ $d('promedio_general') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('promedio_general') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Año de egreso de prepa</label>
                <input type="number" name="egreso_prepa_anio" value="{{ $d('egreso_prepa_anio') }}" min="1950" max="{{ now()->year + 1 }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('egreso_prepa_anio') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Documento de terminación</label>
                <select name="documento_terminacion" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="">—</option>
                  <option value="constancia"  {{ $d('documento_terminacion')==='constancia'?'selected':'' }}>Constancia de estudios</option>
                  <option value="certificado" {{ $d('documento_terminacion')==='certificado'?'selected':'' }}>Certificado</option>
                  <option value="kardex"      {{ $d('documento_terminacion')==='kardex'?'selected':'' }}>Kardex</option>
                </select>
                @error('documento_terminacion') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Tipo de sangre</label>
                <input type="text" name="tipo_sangre" value="{{ $d('tipo_sangre') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('tipo_sangre') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Estado de salud general</label>
                <input type="text" name="estado_salud" value="{{ $d('estado_salud') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('estado_salud') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Alergias o enfermedades crónicas</label>
                <textarea name="alergias" rows="2" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $d('alergias') }}</textarea>
                @error('alergias') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Medicamentos permanentes</label>
                <textarea name="medicamentos" rows="2" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $d('medicamentos') }}</textarea>
                @error('medicamentos') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Contacto de emergencia (Nombre)</label>
                <input type="text" name="contacto_emergencia_nombre" value="{{ $d('contacto_emergencia_nombre') }}" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('contacto_emergencia_nombre') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Contacto de emergencia (Teléfono)</label>
                <input type="text" name="contacto_emergencia_tel" value="{{ $d('contacto_emergencia_tel') }}" inputmode="tel" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @error('contacto_emergencia_tel') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Información adicional</label>
                <textarea name="info_adicional" rows="3" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ $d('info_adicional') }}</textarea>
                @error('info_adicional') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
              </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
              <button type="submit" class="px-4 py-2 rounded-lg font-semibold text-white" style="background:#48AECC">
                Guardar cambios
              </button>
              <a href="{{ route('postulaciones.index') }}" class="px-4 py-2 rounded-lg font-semibold border border-gray-300">
                Cancelar
              </a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  {{-- Edad a partir de la fecha de nacimiento --}}
  <script>
    const fn = document.getElementById('fecha_nac');
    const out = document.getElementById('edad_text');
    function calcEdad(){
      if(!fn || !fn.value) { out && (out.textContent=''); return; }
      const b = new Date(fn.value + 'T00:00:00');
      const t = new Date();
      let edad = t.getFullYear() - b.getFullYear();
      const m = t.getMonth() - b.getMonth();
      if (m < 0 || (m === 0 && t.getDate() < b.getDate())) edad--;
      out.textContent = isFinite(edad) ? `Edad: ${edad} años` : '';
    }
    fn && (fn.addEventListener('change', calcEdad), calcEdad());
  </script>
</x-app-layout>
