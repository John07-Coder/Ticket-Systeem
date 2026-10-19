<x-layouts::app :title="__('Nieuw ticket')">
    <h1>Nieuw ticket</h1>
    <p class="lead">Beschrijf het probleem zo duidelijk mogelijk.</p>

    @include('tickets.partials.form')
</x-layouts::app>
