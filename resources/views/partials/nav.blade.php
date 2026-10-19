<nav class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="text-sm text-zinc-600">Dashboard</a>
        <a href="{{ route('tickets.index') }}" class="font-semibold">Tickets</a>
    </div>

    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-zinc-600">Uitloggen</button>
        </form>
    @endauth
</nav>
