<!DOCTYPE html>
<html lang="nl">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900">
        @include('partials.nav')

        <main class="mx-auto max-w-5xl p-6">
            @yield('content')
        </main>
    </body>
</html>
