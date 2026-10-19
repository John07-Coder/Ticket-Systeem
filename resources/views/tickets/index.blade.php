<x-layouts::app :title="__('Tickets')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Tickets</h1>
                <p class="mt-1 text-sm text-zinc-500">
                    @if (auth()->user()->isStaff())
                        Alle meldingen.
                    @else
                        Meldingen die jij hebt aangemaakt.
                    @endif
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Nieuw ticket</a>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'Alles', 'open' => 'Open', 'in_progress' => 'In behandeling', 'closed' => 'Gesloten'] as $value => $label)
                <a
                    href="{{ $value === '' ? route('tickets.index') : route('tickets.index', ['status' => $value]) }}"
                    class="rounded-full px-3 py-1 text-sm {{ ($status ?? null) === ($value === '' ? null : $value) ? 'bg-zinc-900 text-white' : 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-col gap-3">
            @forelse ($tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="rounded-xl border border-zinc-200 bg-white p-4 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500">
                    <h2 class="font-medium text-zinc-900 dark:text-white">{{ $ticket->title }}</h2>
                    @include('tickets.partials.meta')
                    @if (auth()->user()->isStaff())
                        <p class="mt-2 text-sm text-zinc-500">Toegewezen aan {{ $ticket->assignee->name ?? 'niemand' }}</p>
                    @endif
                </a>
            @empty
                <p class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-sm text-zinc-500 dark:border-zinc-600">Er zijn nog geen tickets.</p>
            @endforelse
        </div>
    </div>
</x-layouts::app>
