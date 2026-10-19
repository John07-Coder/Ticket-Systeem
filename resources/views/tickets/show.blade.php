<x-layouts::app :title="$ticket->title">
    <a href="{{ route('tickets.index') }}" class="text-sm text-zinc-500 hover:text-zinc-900 dark:hover:text-white">Terug naar tickets</a>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $ticket->title }}</h1>
            @include('tickets.partials.meta')
        </div>

        <div class="flex items-center gap-3">
            @can('update', $ticket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm text-zinc-800 dark:border-zinc-600 dark:text-zinc-100">Bewerken</a>
            @endcan

            @can('delete', $ticket)
                <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Dit ticket verwijderen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 dark:text-red-300">Verwijderen</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <p class="whitespace-pre-line text-zinc-800 dark:text-zinc-100">{{ $ticket->description }}</p>
        <dl class="mt-6 grid gap-2 text-sm text-zinc-500 sm:grid-cols-2">
            <div>Aangemaakt op {{ $ticket->created_at->format('d-m-Y H:i') }}</div>
            <div>Toegewezen aan {{ $ticket->assignee->name ?? 'niemand' }}</div>
        </dl>
    </div>

    <section class="mt-10">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Reacties</h2>

        <div class="mt-4 flex flex-col gap-3">
            @forelse ($ticket->comments as $comment)
                @include('tickets.partials.comment')
            @empty
                <p class="text-sm text-zinc-500">Er zijn nog geen reacties.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" class="mt-6 flex max-w-2xl flex-col gap-3">
            @csrf

            <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
                Nieuwe reactie
                <textarea name="message" rows="4" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white" required>{{ old('message') }}</textarea>
            </label>

            @if (auth()->user()->isStaff())
                <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="is_internal" value="1" @checked(old('is_internal'))>
                    Interne notitie, niet zichtbaar voor de melder
                </label>
            @endif

            <button type="submit" class="self-start rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Plaatsen</button>
        </form>
    </section>
</x-layouts::app>
