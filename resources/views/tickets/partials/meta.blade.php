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
@endphp

<p class="meta">
    <span class="badge badge-{{ $ticket->status }}">
        {{ $statusLabels[$ticket->status] ?? $ticket->status }}
    </span>
    <span class="badge badge-{{ $ticket->priority }}">
        {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
    </span>
    @if ($ticket->category)
        <span class="chip">{{ $ticket->category->name }}</span>
    @endif
    @if ($ticket->created_by === auth()->id())
        <span class="badge badge-mine">Van jou</span>
    @elseif ($ticket->creator)
        <span>{{ $ticket->creator->name }}</span>
    @endif
</p>
