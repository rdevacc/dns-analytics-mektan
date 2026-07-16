<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'DNS Analytics')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body>

    @include('layouts.partials.navbar')

    <main class="container-fluid py-4">
        @yield('content')
    </main>

    @stack('scripts')

</body>

</html>