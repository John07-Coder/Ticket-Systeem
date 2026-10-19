@php
    $statusLabels = [
        'open' => 'Open',
        'in_progress' => 'In behandeling',
        'closed' => 'Gesloten',
    ];
    $priorityLabels = [
        'low' => 'Laag',
        'normal' => 'Normaal',
        'high' => 'Hoog',
    ];
    $field = 'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white';
@endphp

<form method="POST" action="{{ isset($ticket) ? route('tickets.update', $ticket) : route('tickets.store') }}" class="mt-6 flex max-w-2xl flex-col gap-4">
    @csrf
    @if (isset($ticket))
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-700 dark:text-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
        Titel
        <input type="text" name="title" value="{{ old('title', $ticket->title ?? '') }}" class="{{ $field }}" required>
    </label>

    <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
        Beschrijving
        <textarea name="description" rows="6" class="{{ $field }}" required>{{ old('description', $ticket->description ?? '') }}</textarea>
    </label>

    <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
        Categorie
        <select name="category_id" class="{{ $field }}" required>
            <option value="">Kies een categorie</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $ticket->category_id ?? null) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </label>

    <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
        Prioriteit
        <select name="priority" class="{{ $field }}">
            @foreach ($priorityLabels as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $ticket->priority ?? 'normal') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </label>

    @if (auth()->user()->isStaff())
        <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
            Toegewezen aan
            <select name="assigned_to" class="{{ $field }}">
                <option value="">Niemand</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected(old('assigned_to', $ticket->assigned_to ?? null) == $agent->id)>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm text-zinc-700 dark:text-zinc-300">
            Status
            <select name="status" class="{{ $field }}">
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $ticket->status ?? 'open') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </label>
    @endif

    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">
            {{ isset($ticket) ? 'Opslaan' : 'Ticket aanmaken' }}
        </button>
        <a href="{{ isset($ticket) ? route('tickets.show', $ticket) : route('tickets.index') }}" class="text-sm text-zinc-500">
            Annuleren
        </a>
    </div>
</form>
