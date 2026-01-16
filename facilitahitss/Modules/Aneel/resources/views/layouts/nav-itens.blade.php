@section('navbar')
    <ul class="navbar-nav ms-auto me-4">
        <li class="nav-item">
            <a href="{{ route('aneel::index') }}" class="nav-link text-dark">Dashboard</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('aneel::reportsRTA.index') }}" class="nav-link text-dark">Relatórios</a>
        </li>
    </ul>
@endsection
