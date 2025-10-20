<x-app-layout>
  <x-slot name="header"><h1 class="text-2xl font-semibold">{{ $item->exists ? 'Editar' : 'Nueva' }} notificación</h1></x-slot>
  <div class="max-w-3xl mx-auto p-6 bg-white border rounded space-y-4">
    <form method="POST" action="{{ $item->exists ? route('admin.notificaciones.update',$item) : route('admin.notificaciones.store') }}">
      @csrf
      @if($item->exists) @method('PUT') @endif

      <label class="block">Título
        <input name="titulo" value="{{ old('titulo',$item->titulo) }}" class="w-full border rounded p-2">
      </label>

      <label class="block">Mensaje
        <textarea name="mensaje" class="w-full border rounded p-2" rows="4">{{ old('mensaje',$item->mensaje) }}</textarea>
      </label>

      <label class="block">Nivel
        <select name="nivel" class="w-full border rounded p-2">
          @foreach(['info','warning','success','danger'] as $n)
            <option value="{{ $n }}" @selected(old('nivel',$item->nivel)===$n)>{{ $n }}</option>
          @endforeach
        </select>
      </label>

      <div class="grid md:grid-cols-3 gap-3">
        <label>Activo
          <select name="activo" class="w-full border rounded p-2">
            <option value="1" @selected(old('activo',$item->activo))>Sí</option>
            <option value="0" @selected(!old('activo',$item->activo))>No</option>
          </select>
        </label>
        <label>Inicio
          <input type="datetime-local" name="inicio" value="{{ old('inicio', optional($item->inicio)->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2">
        </label>
        <label>Fin
          <input type="datetime-local" name="fin" value="{{ old('fin', optional($item->fin)->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2">
        </label>
      </div>

      <div class="pt-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
        <a href="{{ route('admin.notificaciones.index') }}" class="ml-2">Cancelar</a>
      </div>
    </form>
  </div>
</x-app-layout>
