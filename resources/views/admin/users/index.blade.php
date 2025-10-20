<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Usuarios
        </h2>
        <p class="text-sm text-gray-500">Gestiona cuentas y roles del sistema.</p>
      </div>
      <a href="{{ route('admin.users.create') }}" class="btn btn-validar">
        + Nuevo usuario
      </a>
    </div>
  </x-slot>

  <style>
    /* ===== Botones (paleta unificada) ===== */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;
      padding:.55rem .9rem;border-radius:10px;font-weight:600;
      border:1.5px solid transparent;font-size:.9rem;
      min-width:100px;min-height:38px;transition:.15s;
      text-decoration:none;
    }
    .btn-validar{background:#48CCA9;color:#fff}.btn-validar:hover{background:#3ab997}
    .btn-aux{background:#48AECC;color:#fff}.btn-aux:hover{background:#3a97b4}
    .btn-export{background:#486BCC;color:#fff}.btn-export:hover{background:#3b59a8}
    .btn-rechazar{background:#CC486B;color:#fff}.btn-rechazar:hover{background:#a63a57}
    .btn-pagado{background:#CCA948;color:#fff}.btn-pagado:hover{background:#a68536}
    .btn-neutral{background:#f9fafb;color:#374151;border-color:#e5e7eb}
    .btn-neutral:hover{background:#f3f4f6}

    /* ===== Badges roles ===== */
    .badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-weight:600;font-size:.75rem}
    .b-admin{background:#dbeafe;color:#1e40af}
    .b-asp{background:#f3f4f6;color:#374151}
    .b-other{background:#ede9fe;color:#5b21b6}

    /* ===== Cards ===== */
    .card{padding:16px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;
          box-shadow:0 1px 2px rgba(0,0,0,.05);display:flex;flex-direction:column;justify-content:space-between}
    .card-f{margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;display:flex;gap:10px;justify-content:center}
  </style>

  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

      @if(session('status'))
        <div class="mb-4 p-3 rounded font-semibold bg-green-50 text-green-800">
          {{ session('status') }}
        </div>
      @endif

      {{-- ===== Filtros ===== --}}
      <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o email"
               class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
        <button class="btn btn-aux" type="submit">Buscar</button>
        @if(request()->has('q') && request('q')!=='')
          <a href="{{ route('admin.users.index') }}" class="btn btn-neutral">Limpiar</a>
        @endif
      </form>

      @if($users->isEmpty())
        <div class="p-6 bg-white shadow-sm sm:rounded-lg text-gray-500">
          No hay usuarios.
        </div>
      @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          @foreach($users as $u)
            @php
              $role = $u->role ?? 'aspirante';
              $badgeClass = match($role){
                'admin' => 'b-admin',
                'aspirante' => 'b-asp',
                default => 'b-other'
              };
            @endphp

            <div class="card">
              <div>
                <div class="font-semibold text-lg text-gray-900">{{ $u->name }}</div>
                <div class="text-gray-600">{{ $u->email }}</div>
                <div class="mt-1 text-sm">
                  Rol:
                  <span class="badge {{ $badgeClass }}">{{ ucfirst($role) }}</span>
                </div>
              </div>

              <div class="card-f">
                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-export">Editar</a>
                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                      onsubmit="return confirm('¿Eliminar este usuario?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-rechazar">Eliminar</button>
                </form>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-6">
          {{ $users->links() }}
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
