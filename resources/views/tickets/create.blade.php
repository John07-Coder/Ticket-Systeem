<x-layouts::app :title="__('Nieuw ticket')">
    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Nieuw ticket</h1>
    <p class="mt-1 text-sm text-zinc-500">Beschrijf het probleem zo duidelijk mogelijk.</p>

    @include('tickets.partials.form')
</x-layouts::app>
