@extends('layouts.main')

@section('title', 'Usuários')

@section('content')
<section class="bg-white rounded-lg shadow-md m-5 p-2 pb-4 sm:m-10">
    <div class="d-flex m-3">
        <a href="/" class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                class="bi bi-arrow-left-circle" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z" />
            </svg>
            <p>Voltar</p>
        </a>
    </div>
    <div class="dropdown flex-column justify-content-end">
        <h1 class="text-center text-4xl font-semibold text-gray-800 m-4">
            Usuários
        </h1>
        <div class="d-flex">
            <!-- Botão para abrir o modal -->
            <button class="btn btn-dark m-3 ms-auto" data-bs-toggle="modal" data-bs-target="#addUserModal">Novo Usuário</button>
        </div>

        <div id="usuarios" class="d-flex table-responsive">
            <table class="table table-hover table-striped table-bordered table-sm ">
                <thead class="">
                    <tr class='text-center text-nowrap table-dark'>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Perfil</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="text-center text-nowrap align-middle ">
                            <td><a href="{{ route('users.details', ['id' => $user->id]) }}">{{ $user->name }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->is_active ? 'Ativo' : 'Desativado' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal para adicionar novo usuário -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Adicionar Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulário de criação de usuário -->
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Perfil</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="admin">Administrador</option>
                            <option value="editor">Editor</option>
                            <option value="user">Usuário</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Usuário</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
