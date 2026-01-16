@extends('layouts.main')

@section('content')
    <section class="bg-white rounded-lg shadow-md m-5 p-2 pb-4 sm:m-10">
        <div class="dropdown d-flex justify-content-end">
            <button class="btn btn-ligth" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-gear"
                    viewBox="0 0 16 16">
                    <path
                        d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
                    <path
                        d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
                </svg>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Editar Perfil</a></li>
                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="{{ route('users.index') }}" class="dropdown-item">
                            Usuários
                        </a>
                    </li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                    this.closest('form').submit();"
                            style="text-decoration: none">
                            {{ __('Sair') }}
                        </x-dropdown-link>
                    </form>
                </li>
            </ul>
        </div>

        <div class="flex justify-center mb-6">
            <img class="max-w-xs" src="{{ asset('img/logo_hitssbr.jpg') }}" alt="GlobalHitss">
        </div>

        <h1 class="text-center text-4xl font-semibold text-gray-800">
            Bem-vindo ao Facilita
        </h1>

        <div class="flex flex-wrap justify-center gap-4 mt-6">
            @if (!empty($modules) && count($modules) > 0)
                @foreach ($modules as $module)
                    <div class="w-full sm:w-80 bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden">
                        <div class="p-6 text-center">
                            <h5 class="text-xl font-semibold text-gray-800">{{ $module->name }}</h5>
                            <p class="text-gray-600 mt-2">
                                {{ $module->description ?? 'Descrição não disponível' }}
                            </p>
                            <a href="{{ route(strtolower($module->name) . '::index') }}"
                                class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Acessar
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-600 mt-2">
                    Por enquanto você não possui acesso a nenhum módulo, aguarde liberação!
                </p>
            @endif
        </div>
    </section>
@endsection
