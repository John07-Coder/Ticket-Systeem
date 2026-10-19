<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    @if (auth()->user()->isStaff())
                        Alle tickets van de helpdesk.
                    @else
                        Jouw tickets.
                    @endif
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Nieuw ticket</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @include('partials.stat', ['label' => 'Open', 'value' => $counts['open'], 'href' => route('tickets.index', ['status' => 'open'])])
            @include('partials.stat', ['label' => 'In behandeling', 'value' => $counts['in_progress'], 'href' => route('tickets.index', ['status' => 'in_progress'])])
            @include('partials.stat', ['label' => 'Gesloten', 'value' => $counts['closed'], 'href' => route('tickets.index', ['status' => 'closed'])])

            @if (auth()->user()->isStaff())
                @include('partials.stat', ['label' => 'Niet toegewezen', 'value' => $unassigned, 'href' => route('tickets.index', ['assigned' => 'none'])])
            @endif
        </div>

        <section class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <h2 class="font-medium text-zinc-900 dark:text-white">Recente tickets</h2>
                <a href="{{ route('tickets.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Alles bekijken</a>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($recent as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">{{ $ticket->title }}</p>
                            @include('tickets.partials.meta')
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-8 text-sm text-zinc-500">Er zijn nog geen tickets.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts::app>
