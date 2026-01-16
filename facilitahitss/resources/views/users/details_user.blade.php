@extends('layouts.main')

@section('title', 'Usuário')

@section('content')
<section class="bg-white rounded-lg shadow-md m-5 p-2 pb-4 sm:m-10">
    <div class="d-flex m-3">
        <a href="{{ route('users.index') }}" class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
            </svg>
            <p>Voltar</p>
        </a>
    </div>
    <div class="dropdown flex-column justify-content-end">
        <h1 class=" text-4xl font-semibold text-gray-800 m-4">
            {{ $user->name }}
        </h1>
        <div class="ms-4">
            <p>E-mail: {{ $user->email }}</p>
            <p>Nome de usuário: {{ $user->username }}</p>
            <p>Perfil: {{ $user->role }}</p>
            <p><strong>Módulos autorizados:</strong></p>
            <ul>
                @foreach ($user->modules as $module)
                    <li>{{ $module->name }}</li>
                @endforeach
            </ul>
            <div class="d-flex gap-3">
                <a class="btn btn-success mt-3" href="{{ route('users.edit', ['id' => $user->id]) }}">Editar</a>
                <form action="{{ route('users.delete', ['id' => $user->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection