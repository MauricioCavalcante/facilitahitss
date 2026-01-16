<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Facilita @yield('title')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('img/favicon-48x48.png') }}">

    @yield('head')
</head>

<body class="bg-gray-100">
        @if (session('status'))
            <div class="d-flex justify-content-center align-middle alert alert-primary text-center m-3 p-2 pb-4 sm:m-10">
                <p>{{ session('status') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="d-flex justify-content-center align-middle alert alert-danger text-center m-3 p-2 pb-4 sm:m-10">
                <p>{{ session('error') }}</p>
            </div>
        @endif
        @if (session('success'))
            <div class="d-flex justify-content-center align-middle alert alert-success text-center m-3 p-2 pb-4 sm:m-10">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('warning'))
            <div class="d-flex justify-content-center align-middle alert alert-warning text-center m-3 p-2 pb-4 sm:m-10">
                <p>{{ session('warning') }}</p>
            </div>
        @endif
        @if (session('delete'))
            <div class="d-flex justify-content-center align-middle alert alert-info text-center m-3 p-2 pb-4 sm:m-10">
                <p>{{ session('delete') }}</p>
            </div>
        @endif
    @yield('content')
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
