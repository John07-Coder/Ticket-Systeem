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
    <span>{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</span>
    @if ($ticket->category)
        <span>· {{ $ticket->category->name }}</span>
    @endif
    @if ($ticket->created_by === auth()->id())
        <span class="badge badge-mine">Van jou</span>
    @elseif ($ticket->creator)
        <span>Aangemaakt door {{ $ticket->creator->name }}</span>
    @endif
</p>
