<article class="rounded-xl border p-4 {{ $comment->is_internal ? 'border-amber-500/40 bg-amber-500/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">
    <div class="flex items-start justify-between gap-4">
        <p class="text-sm text-zinc-500">
            {{ $comment->user->name ?? 'Onbekend' }}
            · {{ $comment->created_at->format('d-m-Y H:i') }}
            @if ($comment->is_internal)
                · Intern
            @endif
        </p>

        @can('delete', $comment)
            <form method="POST" action="{{ route('tickets.comments.destroy', [$ticket, $comment]) }}" onsubmit="return confirm('Deze reactie verwijderen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 dark:text-red-300">Verwijderen</button>
            </form>
        @endcan
    </div>

    <p class="mt-2 whitespace-pre-line text-zinc-800 dark:text-zinc-100">{{ $comment->message }}</p>

    @can('update', $comment)
        <details class="mt-4">
            <summary class="cursor-pointer text-sm text-zinc-500">Wijzigen</summary>

            <form method="POST" action="{{ route('tickets.comments.update', [$ticket, $comment]) }}" class="mt-3 flex flex-col gap-3">
                @csrf
                @method('PUT')

                <textarea name="message" rows="3" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white" required>{{ $comment->message }}</textarea>

                @if (auth()->user()->isStaff())
                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="is_internal" value="1" @checked($comment->is_internal)>
                        Interne notitie
                    </label>
                @endif

                <button type="submit" class="self-start rounded-lg border border-zinc-300 px-3 py-1.5 text-sm dark:border-zinc-600 dark:text-zinc-100">Opslaan</button>
            </form>
        </details>
    @endcan
</article>
