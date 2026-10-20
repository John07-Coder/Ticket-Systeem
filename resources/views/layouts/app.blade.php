<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="errors">{{ session('error') }}</div>
        @endif

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
