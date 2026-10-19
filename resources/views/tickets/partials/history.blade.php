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
    $fieldLabels = [
        'title' => 'de titel',
        'description' => 'de beschrijving',
        'category' => 'de categorie',
        'priority' => 'de prioriteit',
    ];
@endphp

<div class="panel history">
    @if (! $ticket->activities->contains('type', 'created'))
        <p>
            {{ $ticket->creator->name ?? 'Onbekend' }} heeft het ticket aangemaakt
            <span>{{ $ticket->created_at->format('d-m-Y H:i') }}</span>
        </p>
    @endif

    @forelse ($ticket->activities as $activity)
        <p>
            @if ($activity->type === 'created')
                {{ $activity->user->name ?? 'Onbekend' }} heeft het ticket aangemaakt
            @elseif ($activity->type === 'claimed')
                {{ $activity->user->name ?? 'Onbekend' }} heeft het ticket overgenomen
            @elseif ($activity->type === 'status')
                {{ $activity->user->name ?? 'Onbekend' }} heeft de status gewijzigd van {{ $statusLabels[$activity->from_value] ?? $activity->from_value }} naar {{ $statusLabels[$activity->to_value] ?? $activity->to_value }}
            @elseif ($activity->type === 'assigned')
                {{ $activity->user->name ?? 'Onbekend' }} heeft het ticket toegewezen aan {{ $activity->to_value ?? 'niemand' }}
            @elseif ($activity->type === 'updated' && $activity->field === 'priority')
                {{ $activity->user->name ?? 'Onbekend' }} heeft de prioriteit gewijzigd van {{ $priorityLabels[$activity->from_value] ?? $activity->from_value }} naar {{ $priorityLabels[$activity->to_value] ?? $activity->to_value }}
            @elseif ($activity->type === 'updated' && $activity->field === 'category')
                {{ $activity->user->name ?? 'Onbekend' }} heeft de categorie gewijzigd van {{ $activity->from_value ?? 'geen' }} naar {{ $activity->to_value ?? 'geen' }}
            @else
                {{ $activity->user->name ?? 'Onbekend' }} heeft {{ $fieldLabels[$activity->field] ?? 'het ticket' }} gewijzigd
            @endif
            <span>{{ $activity->created_at->format('d-m-Y H:i') }}</span>
        </p>
    @empty
        @if ($ticket->activities->contains('type', 'created'))
            <p class="empty">Er is nog geen geschiedenis.</p>
        @endif
    @endforelse
</div>
