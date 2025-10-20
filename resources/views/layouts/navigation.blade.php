<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
  @php
    $user         = auth()->user();
    $role         = $user->role ?? 'aspirante';
    $esBackoffice = $user && in_array($role, ['admin','subadmin'], true);
    $esAspirante  = $user && ! $esBackoffice;

    $urlConv      = $esBackoffice ? route('admin.convocatorias.index') : route('convocatorias.index');

    // Activos
    $convActive     = request()->routeIs('convocatorias.*') || request()->routeIs('admin.convocatorias.*');
    $dashActive     = request()->routeIs('dashboard');
    $postsActive    = request()->routeIs('admin.postulaciones.*');
    $gruposActive   = request()->routeIs('admin.grupos.*');
    $misPostActive  = request()->routeIs('postulaciones.*');

    $usersActive    = request()->routeIs('admin.users.*');
    $settingsActive = request()->routeIs('admin.settings.*');
    $auditActive    = request()->routeIs('admin.audits.*');
  @endphp

  <!-- Top bar -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex">
        <!-- Logo -->
        <div class="shrink-0 flex items-center">
          <a href="{{ url('/') }}">
            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
          </a>
        </div>

        <!-- Desktop nav -->
        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
          {{-- Convocatorias --}}
          <x-nav-link :href="$urlConv" :active="$convActive">
            {{ __('Convocatorias') }}
          </x-nav-link>

          @auth
            {{-- Dashboard --}}
            @if (Route::has('dashboard'))
              <x-nav-link :href="route('dashboard')" :active="$dashActive">
                {{ __('Dashboard') }}
              </x-nav-link>
            @endif

            {{-- Backoffice --}}
            @if($esBackoffice)
              <x-nav-link :href="route('admin.postulaciones.index')" :active="$postsActive">
                {{ __('Postulaciones') }}
              </x-nav-link>

              <x-nav-link :href="route('admin.grupos.index')" :active="$gruposActive">
                {{ __('Asignación de fechas') }}
              </x-nav-link>

              @can('manage-users')
                <x-nav-link :href="route('admin.users.index')" :active="$usersActive">
                  {{ __('Usuarios') }}
                </x-nav-link>
              @endcan

              @can('manage-settings')
                <x-nav-link :href="route('admin.settings.edit')" :active="$settingsActive">
                  {{ __('Configuraciones') }}
                </x-nav-link>

                <x-nav-link :href="route('admin.audits.index')" :active="$auditActive">
                  {{ __('Auditoría') }}
                </x-nav-link>
              @endcan
            @endif

            {{-- Aspirante --}}
            @if($esAspirante)
              <x-nav-link :href="route('postulaciones.index')" :active="$misPostActive">
                {{ __('Mis postulaciones') }}
              </x-nav-link>
            @endif
          @endauth
        </div>
      </div>

      <!-- Perfil -->
      <div class="hidden sm:flex sm:items-center sm:ms-6">
        @auth
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="inline-flex items-center px-3 py-2 text-sm rounded-md text-gray-500 bg-white hover:text-gray-700 transition">
                <div>{{ Auth::user()->name }}</div>
                <div class="ms-1">
                  <svg class="fill-current h-4 w-4" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 010-1.414z"
                          clip-rule="evenodd" />
                  </svg>
                </div>
              </button>
            </x-slot>

            <x-slot name="content">
              <x-dropdown-link :href="route('profile.edit')">
                {{ __('Perfil') }}
              </x-dropdown-link>

              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                  onclick="event.preventDefault(); this.closest('form').submit();">
                  {{ __('Cerrar sesión') }}
                </x-dropdown-link>
              </form>
            </x-slot>
          </x-dropdown>
        @endauth
      </div>

      <!-- Hamburger (mobile) -->
      <div class="-me-2 flex items-center sm:hidden">
        <button @click="open = ! open"
                class="inline-flex items-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition"
                aria-controls="mobile-nav" :aria-expanded="open.toString()">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                  stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden"
                  stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile nav -->
  <div id="mobile-nav" :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
    <div class="pt-2 pb-3 space-y-1">
      <x-responsive-nav-link :href="$urlConv" :active="$convActive">
        {{ __('Convocatorias') }}
      </x-responsive-nav-link>

      @auth
        @if (Route::has('dashboard'))
          <x-responsive-nav-link :href="route('dashboard')" :active="$dashActive">
            {{ __('Dashboard') }}
          </x-responsive-nav-link>
        @endif

        @if($esBackoffice)
          <x-responsive-nav-link :href="route('admin.postulaciones.index')" :active="$postsActive">
            {{ __('Postulaciones') }}
          </x-responsive-nav-link>

          <x-responsive-nav-link :href="route('admin.grupos.index')" :active="$gruposActive">
            {{ __('Asignación de fechas') }}
          </x-responsive-nav-link>

          @can('manage-users')
            <x-responsive-nav-link :href="route('admin.users.index')" :active="$usersActive">
              {{ __('Usuarios') }}
            </x-responsive-nav-link>
          @endcan

          @can('manage-settings')
            <x-responsive-nav-link :href="route('admin.settings.edit')" :active="$settingsActive">
              {{ __('Configuraciones') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.audits.index')" :active="$auditActive">
              {{ __('Auditoría') }}
            </x-responsive-nav-link>
          @endcan
        @endif

        @if($esAspirante)
          <x-responsive-nav-link :href="route('postulaciones.index')" :active="$misPostActive">
            {{ __('Mis postulaciones') }}
          </x-responsive-nav-link>
        @endif
      @endauth
    </div>
  </div>
</nav>