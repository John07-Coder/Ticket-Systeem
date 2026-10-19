<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
