<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex items-center justify-center mb-3 p-3">
        <img src="{{ asset('img/logo_hitssbr.jpg') }}" alt="GlobalHitss">
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email or Username -->
        <div>
            <x-input-label for="email_or_username" :value="__('Email ou Nome de usuário')" />
            <x-text-input id="email_or_username" class="block mt-1 w-full" type="text" name="email_or_username" :value="old('email_or_username')" required autofocus />
            <x-input-error :messages="$errors->get('email_or_username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Senha')" />
            <div class="relative w-full">
                <x-text-input
                    id="password"
                    class="block mt-1 w-full border rounded-md px-4 py-2 pr-12"
                    x-bind:type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />

            <!-- Botão de visualizar senha -->
            <div class="block mt-4">
                <label for="show_password" class="inline-flex items-center cursor-pointer">
                    <input id="show_password" type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        @click="showPassword = !showPassword">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Mostrar senha') }}</span>
                </label>
            </div>
        </div>

        <!-- Hidden Field for Identification -->
        <input type="hidden" name="login_field" value="email_or_username">

        <!-- Forgot Password Link -->
        @if (Route::has('password.request'))
            <div class="block mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('Esqueceu sua senha?') }}
                </a>
            </div>
        @endif

        <div class="d-flex items-center justify-end mt-4 gap-2">
            <a class="btn btn-ligth p-2 font-bold py-2 px-4" href="{{ route('register') }}">Cadastrar-se</a>
            <button type="submit" class="btn btn-dark p-2 font-bold py-2 px-4">
                Entrar
            </button>
        </div>
    </form>
</x-guest-layout>
