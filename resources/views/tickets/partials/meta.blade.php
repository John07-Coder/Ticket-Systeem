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
    $statusClasses = [
        'open' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
        'in_progress' => 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
        'closed' => 'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300',
    ];
@endphp

<p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClasses[$ticket->status] ?? '' }}">
        {{ $statusLabels[$ticket->status] ?? $ticket->status }}
    </span>
    <span>{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
    @if ($ticket->category)
        <span>· {{ $ticket->category->name }}</span>
    @endif
    @if ($ticket->creator)
        <span>· {{ $ticket->creator->name }}</span>
    @endif
</p>
