@extends('layouts.main')

@section('title', 'Editar Usuário')

@section('content')
        <section class="bg-white rounded-lg shadow-md m-5 p-2 pb-4 sm:m-10">
            <div class="d-flex m-3">
                <a href="{{ route('users.details', ['id' => $user->id]) }}" class="d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
                    </svg>
                    <p>Voltar</p>
                </a>
            </div>
            <h1 class=" text-4xl font-semibold text-gray-800 m-4">Editar Usuário</h1>
            <div class="container">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Para atualizar -->

                    <!-- Nome -->
                    <div class="mb-4">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" name="name" id="name"
                            class="form-control"
                            value="{{ old('name', $user->name) }}" required>
                    </div>

                    <!-- E-mail -->
                    <div class="mb-4">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email"
                            class="form-control"
                            value="{{ old('email', $user->email) }}" required>
                    </div>

                    <!-- Role -->
                    <div class="mb-4">
                        <label for="role" class="form-label">Perfil</label>
                        <select name="role" id="role" class="form-select">
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="editor" {{ $user->role == 'editor' ? 'selected' : '' }}>Editor</option>
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>Usuário</option>
                        </select>
                    </div>
                    <!-- Status -->
                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <div class="btn-group" role="group" aria-label="Status do usuário">

                            <input type="radio" class="btn-check" name="is_active" id="status_active" value="1" autocomplete="off"
                                {{ $user->is_active ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="status_active">
                                Ativo
                            </label>

                            <input type="radio" class="btn-check" name="is_active" id="status_inactive" value="0" autocomplete="off"
                                {{ !$user->is_active ? 'checked' : '' }}>
                            <label class="btn btn-outline-danger" for="status_inactive">
                                Inativo
                            </label>

                        </div>
                    </div>
                    <!-- Módulos -->
                    <div class="mb-4">
                        <label class="form-label">Módulos</label>
                        <div >
                            @foreach ($modules as $module)
                                <input type="checkbox" class="btn-check" id="module_{{ $module->id }}" name="modules[]"
                                    value="{{ $module->id }}" autocomplete="off"
                                    {{ in_array($module->id, old('modules', $user->modules->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="module_{{ $module->id }}">
                                    {{ $module->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-dark px-4 py-2">
                            Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </section>
@endsection
