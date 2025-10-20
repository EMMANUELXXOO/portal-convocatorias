<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ $user->exists ? 'Editar usuario' : 'Nuevo usuario' }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
      @if(session('status'))
        <div class="mb-4 p-3 rounded font-semibold" style="background:#dcfce7;color:#166534">
          {{ session('status') }}
        </div>
      @endif

      <div class="bg-white shadow-sm sm:rounded-lg p-6 border border-gray-200">
        <form method="POST" action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}">
          @csrf
          @if($user->exists) @method('PUT') @endif

          <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
              <label class="block text-sm text-gray-600 mb-1">Nombre</label>
              <input name="name" value="{{ old('name', $user->name) }}" class="border border-gray-300 rounded-md px-3 py-2 w-full">
              @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-1">
              <label class="block text-sm text-gray-600 mb-1">Correo</label>
              <input name="email" value="{{ old('email', $user->email) }}" class="border border-gray-300 rounded-md px-3 py-2 w-full">
              @error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-1">
              <label class="block text-sm text-gray-600 mb-1">Rol</label>
              @php
                $current = auth()->user();
                $options = ['aspirante'=>'Aspirante','subadmin'=>'Subadmin','admin'=>'Admin'];
                if ($current->role === 'subadmin') {
                  unset($options['admin']); // no puede asignar admin
                }
              @endphp
              <select name="role" class="border border-gray-300 rounded-md px-3 py-2 w-full">
                @foreach($options as $val=>$label)
                  <option value="{{ $val }}" @selected(old('role',$user->role ?: 'aspirante')===$val)>{{ $label }}</option>
                @endforeach
              </select>
              @error('role')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="md:col-span-1">
              <label class="block text-sm text-gray-600 mb-1">
                {{ $user->exists ? 'Contraseña (opcional)' : 'Contraseña' }}
              </label>
              <input type="password" name="password" class="border border-gray-300 rounded-md px-3 py-2 w-full" autocomplete="new-password">
              @error('password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mt-6 flex gap-3">
            <button class="px-4 py-2 rounded-md font-semibold text-white" style="background:#2563eb">
              {{ $user->exists ? 'Guardar cambios' : 'Crear usuario' }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-md border">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</x-app-layout>
