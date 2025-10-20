{{-- resources/views/admin/settings/edit.blade.php --}}
<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800">Configuración de correo</h2>
    </div>
  </x-slot>

  <div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

      @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-3 rounded-lg">
          <ul class="list-disc ml-5">
            @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
          </ul>
        </div>
      @endif

      @if (session('status'))
        <div class="bg-green-50 text-green-800 p-3 rounded-lg">
          {{ session('status') }}
        </div>
      @endif

      <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-200">
        <h3 class="text-lg font-bold mb-4">Remitente</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="grid md:grid-cols-2 gap-4">
          @csrf
          <div>
            <x-input-label value="Nombre remitente"/>
            <x-text-input name="from_name" value="{{ old('from_name',$data['from_name']) }}" class="mt-1 w-full"/>
          </div>
          <div>
            <x-input-label value="Correo remitente"/>
            <x-text-input name="from_email" type="email" value="{{ old('from_email',$data['from_email']) }}" class="mt-1 w-full"/>
          </div>

          <div class="md:col-span-2 border-t my-4"></div>

          <h3 class="text-lg font-bold md:col-span-2">Servidor SMTP</h3>

          <div>
            <x-input-label value="Mailer"/>
            <select name="mailer" class="mt-1 w-full rounded-md border-gray-300">
              @foreach (['smtp'=>'SMTP','sendmail'=>'sendmail','log'=>'log','array'=>'array'] as $k=>$label)
                <option value="{{ $k }}" @selected(old('mailer',$data['mailer'])===$k)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <x-input-label value="Host"/>
            <x-text-input name="host" value="{{ old('host',$data['host']) }}" class="mt-1 w-full"/>
          </div>

          <div>
            <x-input-label value="Puerto"/>
            <x-text-input name="port" type="number" value="{{ old('port',$data['port']) }}" class="mt-1 w-full"/>
          </div>

          <div>
            <x-input-label value="Cifrado"/>
            <select name="encryption" class="mt-1 w-full rounded-md border-gray-300">
              @foreach (['tls'=>'TLS','ssl'=>'SSL','null'=>'Sin cifrado'] as $k=>$label)
                <option value="{{ $k }}" @selected(old('encryption',$data['encryption'])===$k)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <x-input-label value="Usuario (SMTP)"/>
            <x-text-input name="username" value="{{ old('username',$data['username']) }}" class="mt-1 w-full"/>
          </div>

          <div>
            <x-input-label value="Contraseña (SMTP)"/>
            <x-text-input name="password" type="password" placeholder="••••••••" class="mt-1 w-full"/>
            <p class="text-sm text-gray-500">Déjala vacía para conservar la actual.</p>
          </div>

          <div>
            <x-input-label value="Timeout (segundos)"/>
            <x-text-input name="timeout" type="number" value="{{ old('timeout',$data['timeout']) }}" class="mt-1 w-full"/>
          </div>

          <div class="md:col-span-2 flex justify-end">
            <x-primary-button>Guardar</x-primary-button>
          </div>
        </form>
      </div>

      <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-200">
        <h3 class="text-lg font-bold mb-4">Enviar correo de prueba</h3>
        <form method="POST" action="{{ route('admin.settings.test-email') }}" class="flex gap-3 items-end">
          @csrf
          <div class="flex-1">
            <x-input-label value="Enviar a"/>
            <x-text-input name="to" type="email" value="{{ old('to', auth()->user()->email) }}" class="mt-1 w-full"/>
          </div>
          <x-primary-button>Enviar prueba</x-primary-button>
        </form>
        <p class="text-sm text-gray-500 mt-3">
          Sugerencia: algunos proveedores (Gmail/Outlook) exigen que el “From” coincida con la cuenta autenticada
          o que sea un alias permitido.
        </p>
      </div>

    </div>
  </div>
</x-app-layout>
