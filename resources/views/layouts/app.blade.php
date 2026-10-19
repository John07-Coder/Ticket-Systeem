<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
