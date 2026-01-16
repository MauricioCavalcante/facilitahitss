<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom">
    <div class="container">
        <!-- Botão da logo -->
        <a class="navbar-brand d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none"
            href="{{ route('aneel::index') }}">
            <img src="{{ asset('img/logo_hitssbr.jpg') }}" alt="Logo" class="me-2" style="height: 50px;">
        </a>

        <!-- Botão de hambúrguer para telas menores -->
        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu de navegação principal -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="{{ route('aneel::index')}}" class="nav-link text-dark">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('aneel::reportsRTA.index')}}" class="nav-link text-dark">Relatórios</a>
                </li>

                <!-- Menu dropdown para o usuário autenticado -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-dark" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg p-2" aria-labelledby="navbarDropdown">
                        @if (Auth::user()->role == 'admin')
                            <li><a class="dropdown-item" href="{{ route('index') }}">Selecionar Organização</a></li>
                        @endif

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Sair
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
